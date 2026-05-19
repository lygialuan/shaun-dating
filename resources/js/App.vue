<template>
	<template v-if="config != null && user != null && locale_loaded">
		<AuthenticatedLayout v-if="authenticated" />
		<NonAuthenticatedLayout v-else />
	</template>
	<LoadingPage v-else></LoadingPage>
	<ConfirmDialog class="confirm-dialog-social" :draggable="false"/>
	<DynamicDialog />
	<Toast :position="user?.rtl ? 'top-left' : 'top-right'">
		<template #message="toast">
			<div class="p-toast-message-icon">
				<BaseIcon v-if="toast.message.severity === 'success'" name="success" class="text-base-green" />
				<BaseIcon v-else-if="toast.message.severity === 'error'" name="error" />
			</div>
			<div class="p-toast-message-text">
				<span class="p-toast-summary">{{ toast.message.summary }}</span>
				<div class="p-toast-detail" v-html="toast.message.detail" />
			</div>
		</template>
	</Toast>
	<CookiesWarning />
	<AppSuggest />
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { markSeenNotification } from '@/api/notification';
import { getCsrf } from '@/api/utility';
import { checkAdvancedUpload } from '@/utility/index'
import { useAppStore } from '@/store/app'
import { useAuthStore } from '@/store/auth'
import { useLangStore } from '@/store/lang'
import { useChatStore } from '@/store/chat'
import { useUtilitiesStore } from '@/store/utilities'
import BaseIcon from '@/components/icons/BaseIcon.vue';
import LoadingPage from '@/pages/LoadingPage.vue';
import Constant from '@/utility/constant'
import localData from '@/utility/localData';
import Echo from 'laravel-echo';
import Toast from 'primevue/toast';
import ConfirmDialog from 'primevue/confirmdialog';
import DynamicDialog from 'primevue/dynamicdialog';
import CookiesWarning from '@/components/layout/CookiesWarning.vue';
import AppSuggest from '@/components/layout/AppSuggest.vue';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import NonAuthenticatedLayout from '@/layouts/NonAuthenticatedLayout.vue';
import TurnOnNotificationsModal from '@/components/modals/TurnOnNotificationsModal.vue';
var dragTimer = null

export default {
	components: {
		BaseIcon,
		LoadingPage,
		Toast,
		ConfirmDialog,
		DynamicDialog,
		CookiesWarning,
		AppSuggest,
		AuthenticatedLayout,
		NonAuthenticatedLayout
	},
	data() {
		return {
			first: true,
			firstPWA: true,
			lastScrollTop: 0,
			hasClass: false
		};
	},
	mounted() {
        this.initializeApp();
    },
	beforeUnmount() {
		this.cleanupEventListeners();
	},
	watch: {
		'$route': 'checkPermission',
		async user(user) {
            await this.handleUserChange(user);
        },
		darkmode: 'applyDarkMode',
        systemMode: 'applyDarkMode',
		locale_loaded: 'handleLocaleLoaded'
	},
	computed: {
		...mapState(useAuthStore, ['user', 'authenticated']),
		...mapState(useAppStore, ['config', 'darkmode', 'systemMode']),
		...mapState(useLangStore, ['locale_loaded'])
	},
	methods: {
		...mapActions(useUtilitiesStore, ['pingNotification', 'setChatCount', 'setEventDragDrop']),
		...mapActions(useChatStore, ['setChatMessageSentEvent', 'setChatRoomSeenSelfEvent', 'setRoomSeenEvent', 'setRoomUnreadEvent', 'setRoomAcceptEvent', 'setChatMessageUnsentEvent']),
		...mapActions(useAppStore, ['detectMobile', 'setDarkmode', 'updateSystemMode', 'updateScreenSize']),
		...mapActions(useAuthStore, ['me']),
		checkPermission(){
			if (this.user && this.$router.currentRoute.value.meta.permission) {
				let permission = this.$router.currentRoute.value.meta.permission;
				if (! window._.has(this.user.permissions, permission) || ! this.user.permissions[permission]) {
					this.$router.push({ 'name': 'permission' })
				}
			}
		},
		initializeApp() {
            this.detectMobile();
            this.updateScreenSize();
			this.updateSystemMode();
            if (checkAdvancedUpload) {
                this.setupDragAndDrop();
            }
            document.addEventListener('scroll', this.handleScroll);
            document.addEventListener('visibilitychange', this.handleVisibilityChange);
            window.addEventListener('resize', this.updateScreenSize);
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', this.updateSystemMode);
        },
		cleanupEventListeners() {
            if (this.authenticated) {
                clearInterval(this.interval);
            }
			document.removeEventListener('scroll', this.handleScroll);
            document.removeEventListener('visibilitychange', this.handleVisibilityChange);
            window.removeEventListener('resize', this.updateScreenSize);
            window.matchMedia('(prefers-color-scheme: dark)').removeEventListener('change', this.updateSystemMode);
        },
		handleScroll() {
            const viewportWidth = window.innerWidth || document.documentElement.clientWidth;
			if (viewportWidth >= 1025) return;

			if (!this.lastScrollTop && this.lastScrollTop !== 0) this.lastScrollTop = 0;
			if (this.hasClass === undefined) this.hasClass = false;

			const currentScroll = window.scrollY;
			const isBottom = currentScroll + window.innerHeight >= document.body.scrollHeight;
			if (currentScroll > 45 && currentScroll > this.lastScrollTop && !this.hasClass) {
				document.body.classList.add('documentScrollingDown');
				this.hasClass = true;
			}

			if ((currentScroll <= this.lastScrollTop || isBottom) && this.hasClass) {
				document.body.classList.remove('documentScrollingDown');
				this.hasClass = false;
			}

			this.lastScrollTop = currentScroll;
        },
        async handleVisibilityChange() {
            if (!document.hidden) {
                this.me();
				const response = await getCsrf()
				window.axios.defaults.headers.common['X-CSRF-TOKEN'] = response.data.data.csrt;
            }
        },
        setupDragAndDrop() {
			var self = this
            const dragEvents = ['dragover', 'dragenter', 'dragleave', 'drop'];
            dragEvents.forEach((event) => {
                window.addEventListener(event, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                });
            });

            ['dragover', 'drop'].forEach(function (event) {
				window.addEventListener(event, function (e) {
					self.setEventDragDrop(e);
					window.clearTimeout(dragTimer);
				});
			});

			window.addEventListener('dragleave', function (e) {		
				dragTimer = window.setTimeout(function() {
					self.setEventDragDrop(e)
				}, 25);
			});
        },
		async handleUserChange(user) {
            if (user.id > 0 && this.authenticated) {
                this.handleAuthenticatedUser(user);
            } else if (user.id === 0 && this.authenticated) {
                await useAuthStore().logout();
                window.location.href = window.siteConfig.siteUrl;
            }

            document.body.dir = user.rtl ? 'rtl' : 'ltr';
            this.checkPermission();
        },
		showNotification() {
			if (! this.config.pwa.enable) {
				return;
			}

			if (! this.firstPWA) {
				return;
			}
			this.firstPWA = false;

			if (!("Notification" in window)) {
				//alert("This browser does not support desktop notification");
			} else if (Notification.permission === "granted") {
				if(!localData.get('pwa_disable_notifications')){
					this.saveFcmToken()
				}
			} else {
				if (!localData.get('pwa_popup')) {
					this.$dialog.open(TurnOnNotificationsModal, {
						props:{
							showHeader: false,
							dismissableMask: true,
							draggable: false,
							position: 'top'
						}
					});
					localData.set('pwa_popup', true)
				}
			}
		},
		handleAuthenticatedUser(user) {
			let check = true
            if (this.config.emailVerify && !user.email_verified) {
                if (this.$router.currentRoute.value.name !== 'email_confirm') {
                    this.$router.push({ name: 'email_confirm' });
					check = false;
                }
            } else if (this.config.phoneVerify && !user.phone_verified){
				if (this.$router.currentRoute.value.name != 'phone_confirm') {
					this.$router.push({ 'name': 'phone_confirm' })
					check = false;
				}	
			} else if (this.config.photosVerify && !user.photos_verified){
				if (this.$router.currentRoute.value.name != 'photos_confirm') {
					this.$router.push({ 'name': 'photos_confirm' })
				}	
			} else if (this.config.identityVerify && !user.identity_verified){
				if (this.$router.currentRoute.value.name != 'identity_confirm') {
					this.$router.push({ 'name': 'identity_confirm' })
				}	
			} else if (! user.already_setup_login) {
                this.$router.push({ name: 'first_login' });
				check = false;
            }

			if (check) {
				this.showNotification()
			}

            this.setDarkmode(user.darkmode);

            if (this.first) {
                this.setupBroadcast(user);
                this.startNotificationPing();
                this.markNotificationAsRead();
                this.first = false;
            }
        },
		setupBroadcast(user) {
            if (this.config.broadcastEnable) {
				window.Echo = new Echo({
					broadcaster: 'pusher',
					key: this.config.broadcastKey,
					cluster: this.config.broadcastCluster,
					wsHost: this.config.broadcastHost,
					wsPort: this.config.broadcastPort,
					wssPort: this.config.broadcastPort,
					forceTLS: this.config.broadcastForceTLS,
					enabledTransports: ['ws', 'wss'],
					authEndpoint: window.siteConfig.siteUrl + '/broadcasting/auth'
				});
				window.Echo.private(Constant.CHANNEL_USER + user.id).
					listen('.Chat.MessageSentEvent', (data) => {
						this.setChatMessageSentEvent(data)
						this.setChatCount(data.chat_count)
					}).listen('.Chat.RoomSeenSelfEvent', (data) => {
						this.setChatCount(data.chat_count)
						this.setChatRoomSeenSelfEvent(data)
					}).listen('.Chat.RoomSeenEvent', (data) => {
						this.setRoomSeenEvent(data)
					}).listen('.Chat.RoomUnreadEvent', (data) => {
						this.setChatCount(data.chat_count)
						this.setRoomUnreadEvent(data)
					}).listen('.Chat.RoomAcceptEvent', (data) => {
						this.setRoomAcceptEvent(data)
					}).listen('.Chat.MessageUnsentEvent', (data) => {
						this.setChatMessageUnsentEvent(data)
					}
				);
			}
        },
		startNotificationPing() {
            this.pingNotification();
            this.interval = setInterval(this.pingNotification, this.config.notificationInterval);
        },
        async markNotificationAsRead() {
            const urlParams = new URLSearchParams(window.location.search);
            const notifyId = urlParams.get('notify_id');
            if (notifyId) {
                await markSeenNotification({ id: notifyId });
				this.pingNotification();
            }
        },
		applyDarkMode() {
            const isDarkMode = this.darkmode === 'on' || (this.darkmode === 'auto' && this.systemMode === 'dark');
            document.documentElement.classList.toggle('dark', isDarkMode);
        },
		handleLocaleLoaded() {
            if (this.locale_loaded && localData.get('inactive')) {
                localData.remove('inactive');
                this.showError(this.$t('Your account is pending approval.'));
            }
        }
	}
};
</script>
