import middleware from './middleware';
import { i18n } from '@/i18n'
import { computed } from 'vue';
import { loadPageView } from '@/utility/index';

export default [
    {
        path: '/advertisings',
        name: 'advertisings',
        component: loadPageView('ads'),
        beforeEnter: middleware.user,
        meta: { title: computed(() => i18n.global.t('Advertisings Browse')) }
    },
    {
        path: '/advertisings/create/:id?',
        name: 'advertising_create',
        props: true,
        component: () => import('@/pages/ads/AdvertisingCreate.vue'),
        beforeEnter: middleware.user,
        meta: { title: computed(() => i18n.global.t('Advertising Create')) }
    },
    {
        path: '/advertisings/detail/:id?',
        name: 'advertising_detail',
        props: true,
        component: () => import('@/pages/ads/AdvertisingDetail.vue'),
        beforeEnter: middleware.user,
        meta: { title: computed(() => i18n.global.t('Advertising Detail')) }
    },
    {
        path: '/advertisings/boost_post/:id?',
        name: 'advertising_boost_post',
        props: true,
        component: () => import('@/pages/ads/AdvertisingBoostPost.vue'),
        beforeEnter: middleware.user,
        meta: { title: computed(() => i18n.global.t('Advertising Boost Post')) }
    },
]