import middleware from './middleware';
import { i18n } from '@/i18n'
import { computed } from 'vue';
import { loadPageView } from '@/utility/index';

export default [
    {
        path: '/clips/:tab?',
        name: 'vibb',
        props: true,
        component: loadPageView('vibb'),
        meta: { title: computed(() => i18n.global.t('Vibb')) }
    },
    {
        path: '/clips/create',
        name: 'vibb_create',
        component: () => import('@/pages/vibb/VibbCreate.vue'),
        beforeEnter: middleware.user,
        meta: { title: computed(() => i18n.global.t('Create Vibb')) }
    }
]