import './bootstrap';

import { createApp, watch } from 'vue'
import App from './App.vue'
import router from './router'
import { i18n } from './i18n'
import { setTitlePage } from '@/utility/index'
import { getAsset } from './utility/index'
import vClickOutside from "click-outside-vue3"
import PrimeVue from 'primevue/config';
import ConfirmationService from 'primevue/confirmationservice';
import DialogService from 'primevue/dialogservice';
import ToastService from 'primevue/toastservice';
import Tooltip from 'primevue/tooltip';
import _ from 'lodash';
import 'primeicons/primeicons.css'
import { VueReCaptcha } from 'vue-recaptcha-v3'
import { checkPopupBodyClass, changeUrl } from '@/utility'
import RequireLoginModal from '@/components/modals/RequireLoginModal.vue'
import PermissionModal from '@/components/modals/PermissionModal.vue';
import { createPinia } from 'pinia'
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate'
import { useAuthStore } from './store/auth';
import { useAppStore} from './store/app';
import { useLangStore} from './store/lang';
import { initializeApp } from 'firebase/app';
import { getMessaging, getToken, onMessage } from "firebase/messaging";
import { storeFcmToken } from '@/api/utility';
import localData from './utility/localData'
import 'vueperslides/dist/vueperslides.css'
import BasicModal from '@/components/modals/BasicModal.vue'
import VibbDetailModal from '@/components/modals/VibbDetailModal.vue';
import PostStatusBox from '@/components/posts/PostStatusBox.vue';
import Constant from '@/utility/constant'
import CreatorPermissionModal from '@/components/modals/CreatorPermissionModal.vue'
import LikersModal from '@/components/modals/LikersModal.vue'
import ProfileModal from '@/components/modals/ProfileModal.vue';
import { useUserStore } from '@/store/user'

const pinia = createPinia()
pinia.use(piniaPluginPersistedstate)

const app = createApp({
    extends: App,
    async beforeCreate() {   
        const authStore = useAuthStore()
        if ((window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true) && !localData.get('pwa_forced_logout')) {
            localData.set('pwa_forced_logout', 1)
            await authStore.logout()
        }

        await useAppStore().getInit()
        await authStore.me()			        
        var locale = localData.get('locale', window.siteConfig.languageDefault);        
        if (authStore.authenticated) {
            locale = authStore.user.language;
            localData.set('locale', locale)
        }
        await useLangStore().init({ i18n, locale })
    }
});

app.use(router)
app.use(i18n)
app.use(pinia)
app.use(vClickOutside)
app.use(PrimeVue)
app.use(ConfirmationService)
app.use(DialogService)
app.use(ToastService)
app.directive('tooltip', Tooltip)
if(window.siteConfig.recapchaPublicKey){
    app.use(VueReCaptcha, { siteKey: window.siteConfig.recapchaPublicKey, loaderOptions:{autoHideBadge: true }})
}
app.mount('#app')

watch(
    () => i18n.global.locale.value,
    () => {
        const to = router.currentRoute.value
        if (to.meta.title != null) {
            setTitlePage(to.meta.title.value)
        }
    }
)

app.config.globalProperties.$filters = {
    numberShortener(number, singularText, pluralText, digits = 1){
        // format number
        let shortNumber
        if(number >= 1e12){
            shortNumber = +(number / 1e12).toFixed(digits) + "T"
        }else if(number >= 1e9){
            shortNumber = +(number / 1e9).toFixed(digits) + "B"
        }else if(number >= 1e6){
            shortNumber = +(number / 1e6).toFixed(digits) + "M"
        }else if(number >= 1e3){
            shortNumber = +(number / 1e3).toFixed(digits) + "K"
        }else{
            shortNumber = number
        }        
        // render content        
        if(number == 1){
            return _.replace(singularText, '[number]', shortNumber)
        }else{
            return _.replace(pluralText, '[number]', shortNumber)
        }
    },
    textTranslate(content, variable){
        return Object.keys(variable).reduce((acc, key) => {
            return _.replace(acc, `[${key}]`, variable[key]);
        }, content);
    }
}

app.config.globalProperties.shortenNumber = function(number, digits) {
    let shortNumber;
    if(number >= 1e12){
        shortNumber = +(number / 1e12).toFixed(digits) + "T";
    }else if(number >= 1e9){
        shortNumber = +(number / 1e9).toFixed(digits) + "B";
    }else if(number >= 1e6){
        shortNumber = +(number / 1e6).toFixed(digits) + "M";
    }else if(number >= 1e3){
        shortNumber = +(number / 1e3).toFixed(digits) + "K";
    }else{
        shortNumber = number;
    }
    return shortNumber;
}

app.config.globalProperties.showSuccess = function(content, life_time = 5000) {
    this.$toast.add({severity:'success', summary: null, detail: content, life: life_time})
}

app.config.globalProperties.showError = function(content, life_time = 5000) {
    var message = content
    if (typeof content == 'object') {
        message = content.message
        if (content.code == 'error_validate') {
            var key = _.findKey(content.detail, function() { return  true })
            message = content.detail[key]
        }

        if (['authenticated', 'permission'].includes(content.code)) {
            return
        }
    }

    if (message) {
        this.$toast.add({severity:'error', summary: null, detail: message, life: life_time})
    }
}

app.config.globalProperties.resetErrors = function(errorObject) {
    Object.keys(errorObject).forEach((key) => {
        errorObject[key] = null;
    });
}

app.config.globalProperties.handleApiErrors = function(errorObject, error) {
    this.resetErrors(errorObject);
    if (error.error.code === 'error_validate') {
        Object.keys(errorObject).forEach((key) => {
            errorObject[key] = error.error.detail[key] || null;
        });
        setTimeout(() => {
            const appStore = useAppStore()
            if(appStore.screen.md){
                const errorElement = document.getElementsByClassName("p-invalid")[0];
                let position = errorElement.getBoundingClientRect();
                window.scrollTo(position.left, position.top + window.scrollY - 100);
            }
        }, 100);
    } else {
        this.showError(error.error);
    }
}

app.config.globalProperties.asset = function(path) {
    return getAsset(path)
}

app.config.globalProperties.showRequireLogin = function(){
    useAppStore().setOpenedModalCount();
    this.$dialog.open(RequireLoginModal, {
        props:{
            showHeader: false,
            class: 'p-dialog-no-header',
            modal: true,
            dismissableMask: true,
            draggable: false
        },
        onClose: () => {
            checkPopupBodyClass();
            useAppStore().setOpenedModalCount(false);
        }
    });
}

app.config.globalProperties.checkUploadedData = function(data, type){
    const appStore = useAppStore()
    if (typeof type === 'undefined') {
        type = 'photo'
    }

    var extensions = appStore.config.photoExtensionSupport
    switch (type) {
        case 'video':
            extensions = appStore.config.videoExtensionSupport
            break;
        case 'csv':
            extensions = appStore.config.csvExtensionSupport
            break;
        case 'user_verify':
            extensions = appStore.config.userVerifyExtensionSupport
            break;
        case 'chat':
            extensions = appStore.config.chatExtensionSupport
            break;
        case 'post':
            extensions = appStore.config.post.file_extensions
            break;
    }
    const fileExtension = data.name.split('.').pop().toLowerCase()

    if (!extensions.split(',').includes(fileExtension)){
        this.$toast.add({severity:'error', summary: null, detail: this.$t('The {name} has an invalid extension. Valid extension(s): {extension}.', {name:data.name,extension: extensions}), life: 5000})
        return false;
    }
    if(data.size > appStore.config.maxUploadSize * 1024){
        this.$toast.add({severity:'error', summary: null, detail: this.$t('The {name} is too large, maximum file size is {size}Kb.', {name:data.name,size: appStore.config.maxUploadSize}), life: 5000})
        return false;
    }

    return true
}
app.config.globalProperties.formatDateTime = function(str) {
    if (! str) {
        return str
    }
    
    var date = new Date(str),
        mnth = ("0" + (date.getMonth() + 1)).slice(-2),
        day = ("0" + date.getDate()).slice(-2);
    return [date.getFullYear(), mnth, day].join("-");
}
app.config.globalProperties.formatTimeOnly = function(str) {
    if (! str) {
        return str
    }
    
    var date = new Date(str)
    return [("0" + date.getHours()).slice(-2), ("0" + date.getMinutes()).slice(-2)].join(":");
}
app.config.globalProperties.formatCurrency = function(number, thousandSeparator = ',', decimalSeparator = '.') {
    const appStore = useAppStore()
    const hasDecimals = number % 1 !== 0;
    const parts = hasDecimals ? number.toFixed(2).split('.') : [number.toString()];
    parts[0] = parts[0].replace(/(\d)(?=(\d{3})+(?!\d))/g, `$1${thousandSeparator}`);
    return appStore.config.currency.symbol + (hasDecimals ? parts.join(decimalSeparator) : parts[0]);
}
app.config.globalProperties.formattedAmount = function(amount) {
    return amount ? parseFloat(String(amount).replace(/,/g, '')).toFixed(2) : 0
}
app.config.globalProperties.exchangeCurrency = function(amount) {
    const appStore = useAppStore()
    return Math.round((this.formattedAmount(amount) / appStore.config.wallet.exchangeRate) * 100) / 100 + ' ' + appStore.config.currency.code
}
app.config.globalProperties.exchangeTokenCurrency = function(amount) {
    const appStore = useAppStore()
    return this.formattedAmount(amount) + ' ' + appStore.config.wallet.tokenName
}
app.config.globalProperties.aspectRatioImage = function(image){
    const imageFrameHeight = image.height,
    imageFrameWidth = image.width,
    imageFrameAspectRatio = imageFrameHeight / imageFrameWidth
    if(imageFrameHeight === 0 || imageFrameWidth === 0){
        return 1
    } else {
        if(imageFrameAspectRatio > 1.5){
            return 1.5
        } else if(imageFrameAspectRatio < 0.35){
            return 0.35
        } else{
            return imageFrameAspectRatio
        }
    }
}
app.config.globalProperties.aspectRatioVideo = function(video){
    if (video.width > video.height) {
        return 'horizontal';
    } else {
        return 'vertical';
    }
}
app.config.globalProperties.checkPermission = function(permission){
    const authStore = useAuthStore()
    if (!window._.has(authStore.user.permissions, permission) || !authStore.user.permissions[permission]) {
        this.showPermissionPopup(permission)
        return false
    }
    return true
}
app.config.globalProperties.showPermissionPopup = function(permission, message = ''){
    useAppStore().setOpenedModalCount();
    this.$dialog.open(PermissionModal, {
        props:{
            showHeader: false,
            class: 'p-dialog-no-header',
            modal: true,
            draggable: false
        },
        data: {
            permission: permission,
            message: message
        },
        onClose: () => {
            checkPopupBodyClass();
            useAppStore().setOpenedModalCount(false);
        }
    })
}
app.config.globalProperties.parseDate = function(dateStr){
    if(dateStr){
        var parts = dateStr.split('-');
        var year = 2000 + parseInt(parts[0], 10);
        var month = parseInt(parts[1], 10) - 1;
        var day = parseInt(parts[2], 10);
        return new Date(year, month, day);
    } else {
        return 0
    }
}
app.config.globalProperties.runningDate = function(startDate, endDate){
    return startDate && endDate && Math.floor((this.parseDate(this.formatDateTime(endDate)) - this.parseDate(this.formatDateTime(startDate)) ) / (1000 * 60 * 60 * 24)) + 1
}
app.config.globalProperties.getOthersMemberInRoom = function(members){
    const authStore = useAuthStore()
    return members.filter(member => member.id !== authStore.user.id)
}
app.config.globalProperties.showMiniChatBox = function(){
    const appStore = useAppStore()
    return !appStore.screen.lg && appStore.config.chat.enable_bubble_chat && !['chat', 'chat_requests', 'email_confirm', 'phone_confirm', 'chatbot', 'photos_confirm', 'identity_confirm'].includes(this.$route.name)
}
app.config.globalProperties.showNotificationModal = function(content) {
    useAppStore().setOpenedModalCount();
    this.$dialog.open(BasicModal, {
        data:{
            content: content
        },
        props:{
            showHeader: false,
            class: 'p-dialog-no-header',
            modal: true,
            dismissableMask: true,
            draggable: false
        },
        onClose: () => {
            checkPopupBodyClass();
            useAppStore().setOpenedModalCount(false);
        }
    });
}
app.config.globalProperties.showPostStatusBox = function(type = '', postFrom = '', postSubject = null) {
    useAppStore().setOpenedModalCount();
    this.$dialog.open(PostStatusBox, {
        data: {
            chosenType: type,
            postFrom: postFrom,
            postSubject: postSubject
        },
        props:{
            class: 'post-status-modal p-dialog-lg',
            modal: true,
            showHeader: false,
            dismissableMask: false,
            closeOnEscape: false
        },
        onClose: () => {
            checkPopupBodyClass();
            useAppStore().setOpenedModalCount(false);
        }
    });
}

app.config.globalProperties.addImageProcess = async function (src) {
    return new Promise((resolve, reject) => {
        let img = new Image()
        img.onload = () => resolve(img)
        img.onerror = reject
        img.src = URL.createObjectURL(src)
    })
}

app.config.globalProperties.convertImage = async function (img) {
    var MAX_WIDTH = Constant.IMAGE_MAX_WIDTH;
    var MAX_HEIGHT = Constant.IMAGE_MAX_HEIGHT;
    const fileExtension = img.name.split('.').pop().toLowerCase()
    if (fileExtension == 'gif' || fileExtension == 'heic') {
        return img
    }

    var imageTag = await this.addImageProcess(img);

    var width = imageTag.width;
    var height = imageTag.height;

    // Change the resizing logic
    if (width > height) {
        if (width > MAX_WIDTH) {
            height = height * (MAX_WIDTH / width);
            width = MAX_WIDTH;
        }
    } else {
        if (height > MAX_HEIGHT) {
            width = width * (MAX_HEIGHT / height);
            height = MAX_HEIGHT;
        }
    }

    var canvas = document.createElement("canvas");
    canvas.width = width;
    canvas.height = height;
    var ctx = canvas.getContext("2d");
    ctx.drawImage(imageTag, 0, 0, width, height);

    var blob = await new Promise(resolve => {
        canvas.toBlob(resolve, '');
    });

    return blob;
}
app.config.globalProperties.enableRecapcha = function(){
    return window.siteConfig.recaptchaType === 'recaptcha' && window.siteConfig.recapchaPublicKey
}
app.config.globalProperties.enableTurnstile = function(){
    return window.siteConfig.recaptchaType === 'turnstile' && window.siteConfig.turnstileSiteKey
}
app.config.globalProperties.openVibb = function(data) {
    useAppStore().setOpenedModalCount();
    this.$dialog.open(VibbDetailModal, {
        data: data,
        props:{
            class: 'p-dialog-vibb p-dialog-no-header-title',
            modal: true,
            showHeader: false,
            draggable: false
        },
        onClose: () => {
            changeUrl(this.$router.currentRoute.value.fullPath)
            checkPopupBodyClass();
            useAppStore().setOpenedModalCount(false);
        }
    })
    this.$nextTick(() => {
        document.getElementsByClassName('p-dialog-mask')[0].classList.add('p-0')
    })
}
app.config.globalProperties.formatDuration = function(duration) {
    const hours = Math.floor(duration / 3600);
    const minutes = Math.floor((duration % 3600) / 60);
    const seconds = duration % 60;

    const formattedMinutes = minutes.toString().padStart(2, '0');
    const formattedSeconds = seconds.toString().padStart(2, '0');

    if (hours > 0) {
        const formattedHours = hours.toString().padStart(2, '0');
        return `${formattedHours}:${formattedMinutes}:${formattedSeconds}`;
    } else {
        return `${formattedMinutes}:${formattedSeconds}`;
    }
};
app.config.globalProperties.showCreatorPermissionModal = function(userName, content = this.$t('This action cannot be performed at this time, please contact this user for more details.')) {
    useAppStore().setOpenedModalCount();
    this.$dialog.open(CreatorPermissionModal, {
        data:{
            content: content,
            userName: userName
        },
        props:{
            showHeader: false,
            class: 'p-dialog-no-header',
            modal: true,
            dismissableMask: true,
            draggable: false
        },
        onClose: () => {
            checkPopupBodyClass();
            useAppStore().setOpenedModalCount(false);
        }
    });
}
app.config.globalProperties.showHasDraftPopup = function (content) {
    return new Promise((resolve) => {
        this.$confirm.require({
            message: content,
            header: this.$t('Leave Page?'),
            acceptLabel: this.$t('Leave Page'),
            rejectLabel: this.$t('Stay on Page'),
            accept: () => resolve(true),
            reject: () => {
                resolve(false)
                checkPopupBodyClass();
            }
        });
    });
};
app.config.globalProperties.openLikersModal = function(subjectType, subjectId){
    useAppStore().setOpenedModalCount();
    this.$dialog.open(LikersModal, {
        data: {
            subjectType, subjectId
        },
        props:{
            header: this.$t('Likes'),
            class: 'likers-modal',
            modal: true,
            dismissableMask: true,
            draggable: false
        },
        onClose: () => {
            checkPopupBodyClass();
            useAppStore().setOpenedModalCount(false);
        }
    });
}
app.config.globalProperties.saveFcmToken = function () {
    const appStore = useAppStore()
    const firebaseConfig = {
        apiKey: appStore.config.pwa.apiKey,
        authDomain: appStore.config.pwa.authDomain,
        projectId: appStore.config.pwa.projectId,
        storageBucket: appStore.config.pwa.storageBucket,
        messagingSenderId: appStore.config.pwa.messagingSenderId,
        appId: appStore.config.pwa.appId,
        measurementId: appStore.config.pwa.measurementId
    };
    const firebase = initializeApp(firebaseConfig);
    const messaging = getMessaging(firebase);

    if ('serviceWorker' in navigator) {
        // Register the service worker with the appropriate scope
        // The scope should match the directory where the service worker file is located
        navigator.serviceWorker.register(window.siteConfig.siteUrl + '/firebase-messaging-sw.js') // Use the root path
            .then((registration) => {
                console.log('Registration successful, scope is:', registration.scope);
                // Make sure you call useServiceWorker before calling getToken
                //messaging.useServiceWorker(registration);

                getToken(messaging, { vapidKey: appStore.config.pwa.key_pair, serviceWorkerRegistration: registration }).then((currentToken) => {
                    if (currentToken) {
                        if (localData.get('enable_pwa', 1)) {
                            localData.set('fcm_token', currentToken)
                            storeFcmToken({
                                'type': 'web',
                                'token': currentToken
                            })
                        }
                    } else {
                        console.log('No registration token available. Request permission to generate one.');
                    }
                }).catch((err) => {
                    console.log('An error occurred while retrieving token. ', err);
                });
                // Now you can request the token
                // getToken(messaging, {vapidKey: "YOUR_VAPID_KEY"})
            }).catch((err) => {
                console.log('Service worker registration failed, error:', err);
            });
    }

    onMessage(messaging, (payload) => {
        if (payload) {
            console.log(document.hidden)
            if (! document.hidden) {
                navigator.serviceWorker.ready.then(function(registration) {
                    console.log(payload)
                    const notificationTitle = payload.notification.title;
                    const notificationOptions = {
                        body: payload.notification.body,
                        data: payload.data,
                        image: payload.notification.image,
                        icon: payload.notification.icon,
                        tag: payload.messageId
                    };

                    registration.showNotification(notificationTitle,
                        notificationOptions);
                });
                /*var options = {
                    body: payload.notification.body,
                    image: payload.notification.image,
                    icon: payload.notification.icon
                }
                var n = new Notification(window.siteConfig.siteName, options);
                n.onclick = function () {
                    n.close();
                    window.focus();
                    window.location.href = payload.data.url;
                }*/
            }
        }
    });
}
app.config.globalProperties.createVibb = function () {
    const authStore = useAuthStore()
    if(!authStore.authenticated){
        router.push({ 'name': 'login' })
        return
    }
    if (authStore.user) {
        let permission = 'vibb.allow_create'
        if(app.config.globalProperties.checkPermission.call(this, permission)){
            router.push({ 'name': 'vibb_create', force: true })
        }
    }
}
app.config.globalProperties.openProfile = function(data) {
    useAppStore().setOpenedModalCount();
    this.$dialog.open(ProfileModal, {
        data: data,
        props:{
            class: 'p-dialog-profile p-dialog-no-header-title',
            modal: true,
            showHeader: false,
            draggable: false
        },
        onClose: () => {
            document.body.style.overflow = '';
            document.body.classList.remove('p-overflow-hidden');
            useUserStore().sortUser()
            changeUrl(this.$router.currentRoute.value.fullPath)
            checkPopupBodyClass();
            useAppStore().setOpenedModalCount(false);
        }
    })
    this.$nextTick(() => {
        document.getElementsByClassName('p-dialog-mask')[0].classList.add('p-0')
    })
}