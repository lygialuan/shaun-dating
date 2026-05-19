<template>
    <div class="main-content-section">
        <div class="main-content-section-header">
            <h3 class="main-content-section-header-title">{{ $t('Wallet') }}</h3>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-base-2">
            <div>
                <p class="text-base-xs mb-1">{{ $t('Your balance') }}</p>
                <div class="font-bold mb-1">{{ exchangeTokenCurrency(user.wallet_balance) }} ~ {{ exchangeCurrency(user.wallet_balance) }}</div>
                <p class="text-base-xs"> {{ exchangeTokenCurrency(1) }} = {{ exchangeCurrency(1) }}</p>
            </div>
            <div class="flex flex-wrap gap-base-2">
                <BaseButton v-if="user.can_show_withdraw_wallet" @click="handleClickTransfer()">{{$t('Transfer fund')}}</BaseButton>
                <BaseButton v-if="user.can_show_send_wallet" @click="handleClickSendFund()">{{$t('Send fund')}}</BaseButton>
                <BaseButton @click="handleClickAddFund()">{{$t('Add fund')}}</BaseButton>
            </div>
        </div>
    </div>
    <div class="main-content-section">
        <div class="main-content-section-header">
            <h3 class="main-content-section-header-title">{{ $t('Billing activities') }}</h3>
        </div>
        <div class="flex flex-wrap gap-base-2 mb-base-2">
            <form class="flex-1 flex flex-col md:flex-row flex-wrap gap-base-2 min-w-0" @submit.prevent="handleSearchTransactions">
                <div class="flex flex-col gap-base-2 flex-1 w-full">
                    <div class="flex flex-col sm:flex-row flex-wrap gap-base-2">
                        <BaseSelect v-model="selectedWalletType" :options="walletTypesList" optionLabel="key" optionValue="value" :placeholder="$t('Type')" class="flex-1"/>
                        <BaseSelect v-model="selectedWalletDate" :options="walletDatesList" optionLabel="key" optionValue="value" :placeholder="$t('Date')" class="flex-1"/>
                    </div>
                    <div v-if="selectedWalletDate === 'custom'" class="flex flex-col sm:flex-row flex-wrap gap-base-2">
                        <BaseCalendar v-model="customFromDate" class="flex-1" :placeholder="$t('From')"/>
                        <BaseCalendar v-model="customToDate" class="flex-1" :placeholder="$t('To')"/>
                    </div>
                </div>
                <BaseButton :loading="loadingApply" class="self-start w-full md:w-auto">{{$t('Apply')}}</BaseButton>
            </form>
        </div>
        <div class="relative overflow-x-auto border border-b-0 border-table-border-color md:rounded-md dark:border-white/10">
            <table class="s-table w-full text-sm whitespace-nowrap text-center">
                <thead class="s-table-header text-xs uppercase bg-table-header-color dark:bg-dark-web-wash">
                    <tr>
                        <th scope="col" class="p-3 text-start">{{$t('Type')}}</th>
                        <th scope="col" class="p-3">{{$t('Date')}}</th>
                        <th scope="col" class="p-3">{{$t('Gross')}}</th>
                        <th scope="col" class="p-3">{{$t('Fee')}}</th>
                        <th scope="col" class="p-3">{{$t('Net')}}</th>
                    </tr>
                </thead>
                <tbody class="s-table-body">
                    <tr v-if="loadingTransaction">
                        <td colspan="5"><Loading class="mt-base-2"/></td>
                    </tr>
                    <template v-else>
                        <template v-if="transactionsList.length">                 
                            <tr v-for="transactionItem in transactionsList" :key="transactionItem.id" class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b border-table-border-color dark:border-gray-700">
                                <td class="p-3 text-start">{{ transactionItem.description }}</td>
                                <td class="p-3">{{ transactionItem.created_at }}</td>
                                <td class="p-3">{{ transactionItem.gross }}</td>
                                <td class="p-3">{{ transactionItem.fee }}</td>
                                <td class="p-3">{{ transactionItem.net }}</td>
                            </tr>
                        </template>
                        <tr v-else class="border-b border-table-border-color dark:border-gray-700">
                            <td colspan="5" class="p-3">{{$t('No Transactions')}}</td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <div v-if="loadmoreTransaction" class="text-center my-base-2">
            <BaseButton @click="getWalletTransactions(transactionPage, selectedWalletType, selectedWalletDate)" :loading="loadingViewMore">{{$t('View more')}}</BaseButton>
        </div>
    </div>
</template>
<script>
import { mapState } from 'pinia'
import { useAuthStore } from '../../store/auth'
import { useAppStore } from '../../store/app'
import { getWalletConfig, getWalletTransactions } from '@/api/wallet'
import ProfileVerification from '@/components/modals/ProfileVerification.vue'
import AddFundModal from '@/components/modals/AddFundModal.vue'
import SendFundModal from '@/components/modals/SendFundModal.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseSelect from '@/components/inputs/BaseSelect.vue'
import BaseCalendar from '@/components/inputs/BaseCalendar.vue'
import Loading from '@/components/utilities/Loading.vue';

export default {
    components: { BaseButton, BaseSelect, BaseCalendar, Loading },
    data(){
        return{
            selectedWalletType: this.$route.query.type || 'all',
            walletTypesList: [],
            selectedWalletDate: '30_day',
            walletDatesList: [
                { key: this.$t("Past 30 days"), value: "30_day" },
                { key: this.$t("Past 60 days"), value: "60_day" },
                { key: this.$t("Past 90 days"), value: "90_day" },
                { key: this.$t("Custom"), value: "custom" },
            ],
            customFromDate: null,
            customToDate: null,
            transactionsList: [],
            transactionPage: 1,
            loadmoreTransaction: false,
            loadingTransaction: true,
            loadingApply: false,
            loadingViewMore: false
        }
    },
    computed:{
        ...mapState(useAuthStore, ['user']),
        ...mapState(useAppStore, ['config'])
    },
    mounted(){
        this.getWalletTypes()
        this.getWalletTransactions(this.transactionPage, this.selectedWalletType, this.selectedWalletDate)
        if (! this.config.wallet.enable) {
            return this.$router.push({ 'name': 'permission' })
        }
    },
    methods: {
        async getWalletTypes(){
            try {
                const response = await getWalletConfig()
                this.walletTypesList = window._.map(response.types, function(key, value) {
                    return { key, value }
                });
            } catch (error) {
                console.log(error)
            }
        },
        async getWalletTransactions(page, type, date_type){
            this.loadingViewMore = true
            try {
                var from_date = '', to_date = '';
                if(date_type === 'custom'){
                    if (this.customFromDate) {
                        from_date = this.formatDateTime(this.customFromDate)
                    }
                    if (this.customToDate) {
                        to_date = this.formatDateTime(this.customToDate)
                    }
                }
                const response = await getWalletTransactions(page, type, date_type, from_date, to_date)
                if(page === 1){
                    this.transactionsList = response.items
                }else{
                    this.transactionsList = window._.concat(this.transactionsList, response.items);
                }
                if(response.has_next_page){
                    this.loadmoreTransaction = true
                    this.transactionPage++;
                }else{
                    this.loadmoreTransaction = false
                }
                this.loadingTransaction = false
                this.loadingApply = false
                this.loadingViewMore = false
            } catch (error) {
                this.showError(error.error)
                console.log(error)
                this.loadingApply = false
                this.loadingViewMore = false
            }
        },
        handleSearchTransactions(){
            this.loadingApply = true
            this.transactionPage = 1
            this.getWalletTransactions(this.transactionPage, this.selectedWalletType, this.selectedWalletDate)
        },
        handleClickAddFund(){
            this.$dialog.open(AddFundModal, {
                props: {
                    header: this.$t('Add fund to wallet'),
                    class: 'add-fund-modal',
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                }
            })
        },
        handleClickTransfer(){
            if (this.user) {
                let permission = 'wallet.transfer_fund'
				if(this.checkPermission(permission)){
                    if(this.config.wallet.fundTransferVerifyEnable && !this.user.is_verify){
                        this.$dialog.open(ProfileVerification, {
                            props: {
                                header: this.$t('Profile Verification'),
                                class: 'verification-modal',
                                modal: true,
                                dismissableMask: true,
                                draggable: false
                            }
                        })
                    } else {
                        this.$router.push({ name: 'wallet_transfer' })
                    }
                }
            }
        },
        handleClickSendFund(){
            if (this.user) {
                let permission = 'wallet.send_fund'
                if(this.checkPermission(permission)){
                    this.$dialog.open(SendFundModal, {
                        props: {
                            header: this.$t('Send funds to user'),
                            class: 'send-fund-modal',
                            modal: true,
                            dismissableMask: true,
                            draggable: false
                        }
                    })
                }
            }
        }
    }
}
</script>