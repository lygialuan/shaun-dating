import _ from 'lodash';
window._ = _;

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';
import localData from './utility/localData';
import { setAccessCode } from './utility/index'
import router from './router/index'
import { useAuthStore } from './store/auth';

window.axios = axios.create({
    baseURL: window.siteConfig.siteUrl + '/api'
});

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['Accept'] = 'application/json';
window.axios.defaults.headers.common['SupportCookie'] = 'yes';
window.axios.defaults.headers.common['Accept-Language'] = localData.get('locale', window.siteConfig.languageDefault)
window.axios.defaults.headers.common['X-CSRF-TOKEN'] = document.head.querySelector('meta[name="csrf-token"]').content;
window.axios.defaults.withcredentials = true;

if (window.siteConfig.mustLogin) {
    localData.set('authenticated', true);
    window.location.href = window.siteConfig.siteUrl;
}

if (localData.get('access_code', null)) {
    if (window.siteConfig.offline) {
        setAccessCode(localData.get('access_code'));
    } else {
        localData.remove('access_code');
    }
}

window.axios.interceptors.response.use(
    response => response,
    async error => {
        const authStore = useAuthStore()
        switch (error.response.status) {
            case 400:
                switch (error.response.data.error.code){
                    case 'permission':
                        router.push({ 'name': 'permission' })
                        break
                }
                break
            case 401:
                authStore.remove()
                window.location.href = window.siteConfig.siteUrl + '/login?redirect=' + btoa(window.location.href);
                break
            case 503:
                localData.remove('access_code')
                window.location.href = window.siteConfig.siteUrl
                break
            case 403:
                switch (error.response.data.error.code){
                    case 'email_validate':
                        router.push({'name' : 'email_confirm'})
                        break
                    case 'phone_validate':
                        router.push({'name' : 'phone_confirm'})
                        break
                    case 'photos_validate':
                        router.push({'name' : 'photos_confirm'})
                        break
                    case 'identity_validate':
                        router.push({'name' : 'identity_confirm'})
                        break
                    default:
                        await authStore.logout()
                        localData.set('inactive', true)
                        window.location.href = window.siteConfig.siteUrl;
                        break
                }
                break
        }
        return Promise.reject(error.response.data);
    }
);

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Pusher from 'pusher-js';
window.Pusher = Pusher;
