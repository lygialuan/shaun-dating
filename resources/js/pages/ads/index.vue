<template>
    <div class="main-content-section">
        <div class="main-content-section-header">
            <h3 class="main-content-section-header-title">{{ $t('Ads') }}</h3>
            <BaseButton :to="{name: 'advertising_create'}">{{ $t('Create new advertising') }}</BaseButton>
        </div>
        <BaseSelect class="max-w-[240px] mb-base-2" @change="filterAdvertisingsList()" v-model="selectedStatus" :options="statusList" optionLabel="name" optionValue="value" :placeholder="$t('Status')"/>
        <div class="relative overflow-x-auto border border-b-0 border-table-border-color md:rounded-md dark:border-white/10">
            <table class="s-table w-full text-sm whitespace-nowrap text-center">
                <thead class="s-table-header text-xs uppercase bg-table-header-color dark:bg-dark-web-wash">
                    <tr>
                        <th scope="col" class="p-3 w-10">{{$t('Action')}}</th>
                        <th scope="col" class="p-3 text-start">{{$t('Ad')}}</th>
                        <th scope="col" class="p-3 w-10">{{$t('Views')}}</th>
                        <th scope="col" class="p-3 w-10">{{$t('Clicks')}}</th>
                        <th scope="col" class="p-3 w-10">{{$t('Total spent')}}</th>
                    </tr>
                </thead>
                <tbody class="s-table-body">
                    <tr v-if="loadingAdvertising">
                        <td colspan="5"><Loading class="mt-base-2"/></td>
                    </tr>
                    <template v-else>
                        <template v-if="advertisementsList.length">                 
                            <tr v-for="advertisementItem in advertisementsList" :key="advertisementItem.id" class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b border-table-border-color dark:border-gray-700">
                                <td class="p-3"><BaseSelectStatus v-model="advertisementItem.status" :item="advertisementItem" /></td>
                                <td class="p-3">
                                    <div class="flex gap-4 justify-between items-center text-start">
                                        <div>
                                            <div class="font-medium">
                                                <router-link :to="{name: 'advertising_detail', params: {id: advertisementItem.id}}" class="text-main-color dark:text-white whitespace-normal">{{ advertisementItem.name }}</router-link>
                                            </div>
                                            <div class="text-base-xs text-sub-color dark:text-slate-400">{{ advertisementItem.created_at }}</div>
                                        </div>
                                        <BaseButton v-if="advertisementItem.canEdit" :to="{name: 'advertising_create', params: {id: advertisementItem.id}}" icon="pencil" type="secondary-outlined" size="sm"/>
                                    </div>
                                </td>
                                <td class="p-3">{{ advertisementItem.view_count }}</td>
                                <td class="p-3">{{ advertisementItem.click_count }}</td>
                                <td class="p-3">{{ advertisementItem.total_delivery_amount }}</td>
                            </tr>
                        </template>
                        <tr v-else class="border-b border-table-border-color dark:border-gray-700">
                            <td colspan="5" class="p-3">{{$t('No Advertisings')}}</td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <div v-if="loadmoreAdvertising" class="text-center my-base-2">
            <BaseButton @click="getAdvertisingsList(selectedStatus, advertisementPage)" :loading="loadingViewMore">{{$t('View more')}}</BaseButton>
        </div>
    </div>
</template>

<script>
import { getAdvertising } from '@/api/ads'
import Loading from '@/components/utilities/Loading.vue'
import BaseSelectStatus from '@/components/inputs/BaseSelectStatus.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseSelect from '@/components/inputs/BaseSelect.vue'
import { useAppStore } from '../../store/app'
import { mapState } from 'pinia'

export default {
    components: { Loading, BaseSelectStatus, BaseButton, BaseSelect },
    data(){
        return {
            selectedStatus: 'all',
            statusList: [
				{ name: this.$t('All'), value: 'all'},
				{ name: this.$t('Active'), value: 'active'},
				{ name: this.$t('Stop'), value: 'stop'},
				{ name: this.$t('Done'), value: 'done'}
			],
            loadingAdvertising: true,
            loadmoreAdvertising: false,
            advertisementsList: [],
            advertisementPage: 1,
            loadingViewMore: false
        }
    },
    computed:{
        ...mapState(useAppStore, ['config'])
    },
    mounted(){
        if (! this.config.advertising.enable) {
            return this.$router.push({ 'name': 'permission' })
        }
        this.getAdvertisingsList(this.selectedStatus, this.advertisementPage)
    },
    methods:{
        async getAdvertisingsList(type, page){
            this.loadingViewMore = true
            try {
                const response = await getAdvertising(type, page)
                if(page === 1){
                    this.advertisementsList = response.items
                }else{
                    this.advertisementsList = window._.concat(this.advertisementsList, response.items);
                }
                if(response.has_next_page){
                    this.loadmoreAdvertising = true
                    this.advertisementPage++;
                }else{
                    this.loadmoreAdvertising = false
                }
                this.loadingAdvertising = false
                this.loadingViewMore = false
            } catch (error) {
                this.showError(error.error)
                this.loadingAdvertising = false
                this.loadingViewMore = false
            }
        },
        filterAdvertisingsList(){
            this.advertisementPage = 1;
            this.getAdvertisingsList(this.selectedStatus, this.advertisementPage)
        },
        
    }
}
</script>