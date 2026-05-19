<template>
	<form class="flex flex-wrap gap-base-2 mb-base-2" @submit.prevent="filterSubscriptionsList">
		<div class="flex-1">
			<BaseSelect v-model="type" :options="typesList" optionLabel="name" optionValue="value" :placeholder="$t('Type')"/>
		</div>
		<div class="flex-1">
			<BaseSelect v-model="selectedStatus" :options="statusList" optionLabel="name" optionValue="value" :placeholder="$t('Status')"/>
		</div>
		<BaseButton :loading="loadingSearch" class="self-end w-full md:w-auto">{{$t('Search')}}</BaseButton>
	</form>
	<div class="relative overflow-x-auto border border-b-0 border-table-border-color md:rounded-md dark:border-white/10">
		<table class="s-table w-full text-sm whitespace-nowrap text-start">
			<thead class="s-table-header text-xs uppercase bg-table-header-color dark:bg-dark-web-wash">
				<tr>
					<th scope="col" class="p-3">{{$t('Subscription')}}</th>
					<th scope="col" class="p-3">{{$t('Status')}}</th>
					<th scope="col" class="p-3">{{$t('Next payment')}}</th>
					<th scope="col" class="p-3">{{$t('Action')}}</th>
				</tr>
			</thead>
			<tbody class="s-table-body">
				<tr v-if="loadingSubscription">
					<td colspan="5"><Loading class="mt-base-2"/></td>
				</tr> 
				<template v-else>
					<template v-if="subscriptionsList.length">                 
						<tr v-for="subscription in subscriptionsList" :key="subscription.id" class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b border-table-border-color dark:border-gray-700">
							<td class="p-3">{{ subscription.name }}</td>
							<td class="p-3">{{ subscription.status_text }}</td>
							<td class="p-3">{{ subscription.next_payment }}</td>
							<td class="p-3">
								<router-link :to="{name: 'setting_subscription_detail', params: {id: subscription.id}}">{{ $t('View') }}</router-link>
							</td>
						</tr>
					</template>
					<tr v-else class="border-b border-table-border-color dark:border-gray-700">
						<td colspan="5" class="text-center p-3">{{$t('No Subscriptions')}}</td>
					</tr>
				</template>
			</tbody>
		</table>
	</div>
	<div v-if="loadmoreSubscription" class="text-center my-base-2">
		<BaseButton @click="getSubscriptionsList(type, selectedStatus, currentPage)">{{$t('View more')}}</BaseButton>
	</div>
</template>

<script>
import { getUserSubscription, getSubscriptionConfig } from '@/api/subscription'
import Loading from '@/components/utilities/Loading.vue';
import BaseButton from '@/components/inputs/BaseButton.vue';
import BaseSelect from '@/components/inputs/BaseSelect.vue';

export default {
	components: { Loading, BaseButton, BaseSelect },
	data(){
		return{
			loadingSubscription: true,
			loadmoreSubscription: false,
			subscriptionsList: [],
			type: 'all',
			currentPage: 1,
			selectedStatus: 'all',
            statusList: [
				{ name: this.$t('All'), value: 'all'},
				{ name: this.$t('Active'), value: 'active'},
				{ name: this.$t('Cancel'), value: 'cancel'},
				{ name: this.$t('Stop'), value: 'stop'}
			],
			typesList: [],
			loadingSearch: false
		}
	},
	mounted(){
		this.getConfig()
		this.getSubscriptionsList(this.type, this.selectedStatus, this.currentPage)
	},
	methods:{
		async getConfig(){
            try {
                const response = await getSubscriptionConfig()
                this.typesList = window._.map(response.types, function(value, key) {
                    return { value: key, name: value }
                });
            } catch (error) {
                this.showError(error)
            }
        },
		async getSubscriptionsList(type, status, page){
			try {
				const response = await getUserSubscription(type, status, page)
				if(page === 1){
                    this.subscriptionsList = response.items
                }else{
                    this.subscriptionsList = window._.concat(this.subscriptionsList, response.items);
                }
                if(response.has_next_page){
                    this.loadmoreSubscription = true
                    this.currentPage++;
                }else{
                    this.loadmoreSubscription = false
                }
			} catch (error) {
				this.showError(error.error)
			} finally {
				this.loadingSubscription = false
                this.loadingSearch = false
			}
		},
		filterSubscriptionsList(){
			this.loadingSubscription = true
			this.loadmoreSubscription = false
            this.currentPage = 1;
			this.loadingSearch = true
            this.getSubscriptionsList(this.type, this.selectedStatus, this.currentPage)
        }
	}
}
</script>