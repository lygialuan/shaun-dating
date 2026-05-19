<template>
    <h3 class="text-main-color text-base-lg font-extrabold dark:text-white mb-base-2">{{ $t('Earning report') }}</h3>
    <p class="mb-4">{{ $t('You can see your total earnings from paid content sales here. To see earnings for a specific time period, please click "View all" for more details.') }}</p>
    <div v-if="earningReport" class="grid grid-cols-3 gap-base-2 bg-badge-color p-5 text-center rounded-base-lg mb-5 dark:bg-dark-web-wash">
        <div>
            <div class="mb-base-1">{{ $t('Total') }}</div>
            <div class="text-2xl">{{ earningReport.total }}</div>
        </div>
        <div>
            <div class="mb-base-1">{{ $t('Platform fee') }}</div>
            <div class="text-2xl">{{ earningReport.fee }}</div>
        </div>
        <div>
            <div class="mb-base-1">{{ $t('Earning') }}</div>
            <div class="text-2xl">
                <router-link :to="{name: 'wallet', query: {type: 'paid_content'}}" class="text-inherit">{{ earningReport.earning }}</router-link>
            </div>
        </div>
    </div>
    <h3 class="text-main-color text-base-lg font-extrabold dark:text-white mb-base-2">{{ $t('How much do creators earn?') }}</h3>
    <div class="mb-4">
        <p>{{ $filters.textTranslate(this.$t('Creators receive [fee]% of the revenue on their earnings'), { fee: 100 - this.config.paid_content.commission_fee}) }}</p>
        <p>{{ $filters.textTranslate(this.$t('The remaining [remaining_fee]% covers referral payments, payment processing, hosting, support, and all other services.'), { remaining_fee: this.config.paid_content.commission_fee}) }}</p>
    </div>
    <div class="relative overflow-x-auto border border-b-0 border-table-border-color md:rounded-md dark:border-white/10">
        <table class="s-table w-full text-sm whitespace-nowrap text-center">
            <thead class="s-table-header text-xs uppercase bg-table-header-color dark:bg-dark-web-wash">
                <tr>
                    <th scope="col" class="p-3 text-start">{{$t('Description')}}</th>
                    <th scope="col" class="p-3">{{$t('Gross')}}</th>
                    <th scope="col" class="p-3">{{$t('Platform fee')}}</th>
                    <th scope="col" class="p-3">{{$t('Net')}}</th>
                    <th scope="col" class="p-3">{{$t('Date')}}</th>
                </tr>
            </thead>
            <tbody class="s-table-body">
                <tr v-if="loadingEarningTransactions">
                    <td colspan="5"><Loading class="mt-base-2"/></td>
                </tr>
                <template v-else>
                    <template v-if="earningTransactionsList.length">                 
                        <tr v-for="transaction in earningTransactionsList" :key="transaction.id" class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b border-table-border-color dark:border-gray-700">
                            <td class="p-3 text-start">{{ transaction.description }}</td>
                            <td class="p-3">{{ transaction.gross }}</td>
                            <td class="p-3">{{ transaction.fee }}</td>
                            <td class="p-3">{{ transaction.net }}</td>
                            <td class="p-3">{{ transaction.created_at }}</td>
                        </tr>
                    </template>
                    <tr v-else class="border-b border-table-border-color dark:border-gray-700">
                        <td colspan="5" class="p-3">{{$t('No Transaction')}}</td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
    <div v-if="earningTransactionsList.length" class="text-end mt-base-2">
        <router-link :to="{name: 'wallet', query: {type: 'paid_content'}}">{{ $t('View all') }}</router-link>
    </div>
</template>

<script>
import { mapState } from 'pinia';
import { useAppStore } from '@/store/app';
import { getEarningReport, getEarningTransaction } from '@/api/paid_content'
import Loading from '@/components/utilities/Loading.vue';

export default {
    components: {
        Loading
    },
    data(){
        return{
            earningReport: null,
            loadingEarningTransactions: true,
            earningTransactionsList: []
        }
    },
    computed:{
        ...mapState(useAppStore, ['config']),
    },
    mounted(){
        this.getEarningReport()
        this.getEarningTransactions()
    },
    methods:{
        async getEarningReport(){
            try {
                this.earningReport = await getEarningReport()
            } catch (error) {
                this.showError(error.error)
            }
        },
        async getEarningTransactions(){
            try {
                this.earningTransactionsList = await getEarningTransaction()
            } catch (error) {
                this.showError(error.error)
            } finally {
                this.loadingEarningTransactions = false
            }
        }
    }
}
</script>