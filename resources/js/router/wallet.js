import middleware from './middleware';
import { i18n } from '@/i18n'
import { computed } from 'vue';
import { loadPageView } from '../utility/index';

export default [
    {
        path: '/wallet',
        name: 'wallet',
        component: loadPageView('wallets'),
        beforeEnter: middleware.user,
        meta: { title: computed(() => i18n.global.t('Wallet')) }
    },
    {
        path: '/wallet/transfer',
        name: 'wallet_transfer',
        component: () => import('@/pages/wallets/WalletTransferFund.vue'),
        beforeEnter: middleware.user,
        meta: { title: computed(() => i18n.global.t('Transfer')) }
    }
]