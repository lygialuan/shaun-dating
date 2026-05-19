<template>
	<CurrentPlan v-if="subscriptionDetail" :subscription="subscriptionDetail" @cancel="handleCancelSubscription" @resume="handleResumeSubscription" @back="handleBack" class="mb-base-2"/>
	<div class="mb-base-2 font-bold">{{ $t('Payment history') }}</div>
	<div class="relative overflow-x-auto border border-b-0 border-table-border-color md:rounded-md dark:border-white/10">
		<table class="s-table w-full text-sm whitespace-nowrap text-center">
			<thead class="s-table-header text-xs uppercase bg-table-header-color dark:bg-dark-web-wash">
				<tr>
					<th scope="col" class="p-3 text-start">{{$t('Date')}}</th>
					<th scope="col" class="p-3">{{$t('Status')}}</th>
					<th scope="col" class="p-3">{{$t('Transaction ID')}}</th>
					<th scope="col" class="p-3">{{$t('Amount')}}</th>
				</tr>
			</thead>
			<tbody class="s-table-body">
				<tr v-if="loadingTransaction">
					<td colspan="4"><Loading class="mt-base-2"/></td>
				</tr>
				<template v-else>
					<template v-if="transactionsList.length">                 
						<tr v-for="transaction in transactionsList" :key="transaction.id" class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b border-table-border-color dark:border-gray-700">
							<td class="p-3 text-start">{{ transaction.date }}</td>
							<td class="p-3">{{ transaction.status_text }}</td>
							<td class="p-3">{{ transaction.transaction_id }}</td>
							<td class="p-3">{{ transaction.price }}</td>
						</tr>
					</template>
					<tr v-else class="border-b border-table-border-color dark:border-gray-700">
						<td colspan="4" class="p-3">{{$t('No Transactions')}}</td>
					</tr>
				</template>
			</tbody>
		</table>
	</div>
	<div v-if="loadmoreTransaction" class="text-center my-base-2">
		<BaseButton @click="getSubscriptionTransactions(id, currentPage)">{{$t('View more')}}</BaseButton>
	</div>
</template>

<script>
import { getSubscriptionDetail, getTransactions, cancelSubscription, resumeSubscription } from '@/api/subscription'
import Loading from '@/components/utilities/Loading.vue';
import BaseButton from '@/components/inputs/BaseButton.vue';
import CurrentPlan from '@/components/membership/CurrentPlan.vue'

export default {
	props: ['id'],
	components: { Loading, BaseButton, CurrentPlan },
	data(){
		return{
			subscriptionDetail: null,
			loadingTransaction: true,
			loadmoreTransaction: false,
			transactionsList: [],
			currentPage: 1
		}
	},
	mounted(){
		this.getSubscriptionDetail(this.id)
		this.getSubscriptionTransactions(this.id, this.currentPage)
	},
	methods:{
		async getSubscriptionDetail(id){
			try {
				const response = await getSubscriptionDetail(id)
				this.subscriptionDetail = response
			} catch (error) {
				this.showError(error.error)
			}
		},
		async getSubscriptionTransactions(id, page){
			try {
				const response = await getTransactions(id, page)
				if(page === 1){
                    this.transactionsList = response.items
                }else{
                    this.transactionsList = window._.concat(this.transactionsList, response.items);
                }
                if(response.has_next_page){
                    this.loadmoreTransaction = true
                    this.currentPage++;
                }else{
                    this.loadmoreTransaction = false
                }
                this.loadingTransaction = false
			} catch (error) {
				this.showError(error.error)
			}
		},
		async handleCancelSubscription(subscriptionId){
            try {
                await cancelSubscription(subscriptionId)
                this.getSubscriptionDetail(subscriptionId)
            } catch (error) {
                this.showError(error.error)
            }
        },
        async handleResumeSubscription(subscriptionId){
            try {
                await resumeSubscription(subscriptionId)
                this.getSubscriptionDetail(subscriptionId)
            } catch (error) {
                this.showError(error.error)
            }
        },
		handleBack(){
			this.$router.push({ name: 'setting_subscriptions' })
		}
	}
}
</script>