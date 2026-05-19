import middleware from './middleware';
import { i18n } from '@/i18n'
import { computed } from 'vue';
import { loadPageView } from '../utility/index';

export default [
    {
        path: '/settings',
        name: 'settings',
        component: loadPageView('settings'),
        beforeEnter: middleware.user,
        meta: { title: computed(() => i18n.global.t('Settings')) },
        children: [
            {
                path: '',
                name: 'setting_index',
                component: () => import('@/pages/settings/Account.vue'),
                beforeEnter: middleware.user,
                meta: { title: computed(() => i18n.global.t('Settings Account')) },
            },
            {
                path: 'profile',
                name: 'setting_profile',
                component: () => import('@/pages/settings/Profile.vue'),
                beforeEnter: middleware.user,
                meta: { title: computed(() => i18n.global.t('Settings Profile')) },
            },
            {
                path: 'two_factor',
                name: 'setting_two_factor',
                component: () => import('@/pages/settings/TwoFactor.vue'),
                beforeEnter: middleware.user,
                meta: { title: computed(() => i18n.global.t('Two-factor authentication')) },
            },
            {
                path: 'account',
                name: 'setting_account',
                component: () => import('@/pages/settings/Account.vue'),
                beforeEnter: middleware.user,
                meta: { title: computed(() => i18n.global.t('Settings Account')) },
            },
            {
                path: 'password',
                name: 'setting_password',
                component: () => import('@/pages/settings/Password.vue'),
                beforeEnter: middleware.user,
                meta: { title: computed(() => i18n.global.t('Change password')) },
            },
            {
                path: 'add_email_password',
                name: 'setting_add_email_password',
                component: () => import('@/pages/settings/AddEmailPassword.vue'),
                beforeEnter: middleware.user,
                meta: { title: computed(() => i18n.global.t('Add email and password')) },
            },
            {
                path: 'privacy',
                name: 'setting_privacy',
                component: () => import('@/pages/settings/Privacy.vue'),
                beforeEnter: middleware.user,
                meta: { title: computed(() => i18n.global.t('Settings Privacy')) },
            },
            {
                path: 'email',
                name: 'setting_email',
                component: () => import('@/pages/settings/Email.vue'),
                beforeEnter: middleware.user,
                meta: { title: computed(() => i18n.global.t('Settings Email')) },
            },
            {
                path: 'notifications',
                name: 'setting_notifications',
                component: () => import('@/pages/settings/Notifications.vue'),
                beforeEnter: middleware.user,
                meta: { title: computed(() => i18n.global.t('Settings Notifications')) },
            },
            {
                path: 'display',
                name: 'setting_display',
                component: () => import('@/pages/settings/Display.vue'),
                beforeEnter: middleware.user,
                meta: { title: computed(() => i18n.global.t('Settings Display')) },
            },
            {
                path: 'download',
                name: 'setting_download',
                component: () => import('@/pages/settings/Download.vue'),
                beforeEnter: middleware.user,
                meta: { title: computed(() => i18n.global.t('Settings Download GDPR')) },
            },
            {
                path: 'pwa',
                name: 'setting_pwa',
                component: () => import('@/pages/settings/PWA.vue'),
                beforeEnter: middleware.user,
                meta: { title: computed(() => i18n.global.t('PWA Settings')) },
            },
            {
                path: 'subscriptions',
                name: 'setting_subscriptions',
                component: () => import('@/pages/settings/Subscriptions.vue'),
                beforeEnter: middleware.user,
                meta: { title: computed(() => i18n.global.t('Subscriptions')) }
            },
            {
                path: 'subscriptions/detail/:id?',
                name: 'setting_subscription_detail',
                props: true,
                component: () => import('@/pages/settings/SubscriptionsDetail.vue'),
                beforeEnter: middleware.user,
                meta: { title: computed(() => i18n.global.t('Subscription Detail')) }
            },
            {
                path: 'creator_dashboard/:tab?',
                name: 'setting_creator_dashboard',
                props: true,
                component: () => import('@/pages/settings/creator_dashboard/index.vue'),
                beforeEnter: middleware.user,
                meta: { title: computed(() => i18n.global.t('Creator Dashboard')) }
            },
            {
                path: 'creator_dashboard/subscribers/detail/:id?',
                name: 'setting_subscriber_detail',
                props: true,
                component: () => import('@/pages/settings/creator_dashboard/SubscriberDetail.vue'),
                beforeEnter: middleware.user,
                meta: { title: computed(() => i18n.global.t('Creator Dashboard')) }
            }
        ]
    },
    {
        path: '/creator_setting_steps/:step?',
        name: 'creator_setting_steps',
        props: true,
        component: () => import('@/pages/creator_setting_steps/index.vue'),
        beforeEnter: middleware.user,
        meta: { title: computed(() => i18n.global.t('Creator Setting Steps')) }
    },
]