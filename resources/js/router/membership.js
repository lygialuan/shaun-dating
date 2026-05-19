import middleware from './middleware';
import { i18n } from '@/i18n'
import { computed } from 'vue';
import { loadPageView } from '../utility/index';

export default [
    {
        path: '/membership',
        name: 'membership',
        component: loadPageView('membership'),
        beforeEnter: middleware.user,
        meta: { title: computed(() => i18n.global.t('Compare Plans')) },
    }
]