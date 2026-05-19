import middleware from './middleware';
import { i18n } from '@/i18n'
import { computed } from 'vue';

export default [
    {
        path: '/pages/create',
        name: 'user_pages_create',
        component: () => import('@/pages/user_pages/UserPagesCreate.vue'),
        beforeEnter: middleware.user,
        meta: { title: computed(() => i18n.global.t('Create New Page')) }
    },
]