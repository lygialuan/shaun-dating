<template>
    <form class="flex flex-wrap gap-base-2 mb-base-2" @submit.prevent="searchSubscribersList">
        <div class="flex flex-col gap-base-2 flex-1">
            <div class="flex flex-col sm:flex-row flex-wrap gap-base-2">
                <BaseInputText v-model="keyword" :placeholder="$t('Keyword')" class="md:flex-1"/>
                <BaseSelect class="md:flex-1" v-model="status" :options="statusOptions" optionLabel="name" optionValue="value" :placeholder="$t('Status')"/>
                <BaseSelect class="md:flex-1" v-model="dateType" :options="dateTypeOptions" optionLabel="name" optionValue="value" :placeholder="$t('Type')"/>
            </div>
            <div v-if="dateType === 'custom'" class="flex flex-col sm:flex-row flex-wrap gap-base-2">
                <BaseCalendar v-model="fromDate" :placeholder="$t('From Date')" class="md:flex-1" showButtonBar />
                <BaseCalendar v-model="toDate" :placeholder="$t('End Date')" class="md:flex-1" showButtonBar />
            </div>
        </div>
        <BaseButton :loading="loadingSearch" class="self-start w-full md:w-auto">{{$t('Search')}}</BaseButton>
    </form>
    <div class="relative overflow-x-auto border border-b-0 border-table-border-color md:rounded-md dark:border-white/10">
        <table class="s-table w-full text-sm whitespace-nowrap text-center">
            <thead class="s-table-header text-xs uppercase bg-table-header-color dark:bg-dark-web-wash">
				<tr>
                    <th scope="col" class="p-3 text-start sticky start-0 bg-inherit">{{$t('User')}}</th>
                    <th scope="col" class="p-3">{{$t('Status')}}</th>
                    <th scope="col" class="p-3">{{$t('Package')}}</th>
                    <th scope="col" class="p-3">{{$t('Date')}}</th>
                    <th scope="col" class="p-3">{{$t('Action')}}</th>
                </tr>
            </thead>
            <tbody class="s-table-body">
                <tr v-if="loadingSubcribers">
                    <td colspan="5"><Loading class="mt-base-2"/></td>
                </tr>
                <template v-else>
                    <template v-if="subcribersList.length">                 
                        <tr v-for="subcriber in subcribersList" :key="subcriber.id" class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b border-table-border-color dark:border-gray-700">
                            <td class="p-3 text-start sticky start-0 bg-inherit">
                                <div class="flex gap-2 items-center">
                                    <Avatar :user="subcriber.user" :size="20" :active-popover="false" />
                                    <UserName :user="subcriber.user" :active-popover="false" class="!font-normal !max-w-44" />
                                </div>
                            </td>
                            <td class="p-3">{{ subcriber.status_text }}</td>
                            <td class="p-3">{{ subcriber.name }}</td>
                            <td class="p-3">{{ subcriber.created_at }}</td>
                            <td class="p-3">
                                <router-link :to="{name: 'setting_subscriber_detail', params: {id: subcriber.id}}">{{ $t('View') }}</router-link>
                                <template v-if="subcriber.canResume"> &middot; <button class="text-primary-color dark:text-dark-primary-color" @click="resumeSubscription(subcriber.id)"> {{ $t('Resume') }}</button></template>
                                <template v-if="subcriber.canCancel"> &middot; <button class="text-primary-color dark:text-dark-primary-color" @click="cancelSubscription(subcriber.id)"> {{ $t('Cancel') }}</button></template>
                            </td>
                        </tr>
                    </template>
                    <tr v-else class="border-b border-table-border-color dark:border-gray-700">
                        <td colspan="5" class="p-3">{{$t('No Subscribers')}}</td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
    <div v-if="loadmoreSubcribers" class="text-center my-base-2">
        <BaseButton @click="this.handleGetUserSubscriber(dateType, status, page, keyword, fromDate, toDate)">{{$t('View more')}}</BaseButton>
    </div>
</template>

<script>
import { getUserSubscriber, storeSubscriberResume, storeSubscriberCancel } from '@/api/paid_content'
import Loading from '@/components/utilities/Loading.vue';
import BaseButton from '@/components/inputs/BaseButton.vue';
import BaseInputText from '@/components/inputs/BaseInputText.vue';
import BaseSelect from '@/components/inputs/BaseSelect.vue';
import BaseCalendar from '@/components/inputs/BaseCalendar.vue';
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'

export default {
    components: { 
        Loading,
        BaseButton,
        BaseInputText,
        BaseSelect,
        BaseCalendar,
        Avatar,
        UserName
    },
    data(){
        return{
            dateType: 'all',
            dateTypeOptions: [
                { name: this.$t("All"), value: "all" },
                { name: this.$t("Past 30 days"), value: "30_day" },
                { name: this.$t("Past 60 days"), value: "60_day" },
                { name: this.$t("Past 90 days"), value: "90_day" },
                { name: this.$t("Custom"), value: "custom" },
            ],
            status: 'all',
            statusOptions: [
                { name: this.$t('All'), value: 'all'},
                { name: this.$t('Active'), value: 'active'},
                { name: this.$t('Cancel'), value: 'cancel'},
                { name: this.$t('Stop'), value: 'stop'}
            ],
            page: 1,
            keyword: '',
            fromDate: '',
            toDate: '',
            loadingSubcribers: true,
            loadmoreSubcribers: false,
            subcribersList: [],
            loadingSearch: false
        }
    },
    mounted(){
        this.handleGetUserSubscriber(this.dateType, this.status, this.page, this.keyword, this.fromDate, this.toDate)
    },
    methods:{
        async handleGetUserSubscriber(dateType, status, page, keyword, fromDate, toDate){
            try {
                const response = await getUserSubscriber(dateType, status, page, keyword, this.formatDateTime(fromDate), this.formatDateTime(toDate))
                if (page == 1) {
                    this.subcribersList = [];
                }
                this.subcribersList = window._.concat(this.subcribersList, response.items)
                if(response.has_next_page){
                    this.loadmoreSubcribers = true
                    this.page++;
                }else{
                    this.loadmoreSubcribers = false
                }
            } catch (error) {
                this.showError(error.error)
            } finally {
                this.loadingSubcribers = false
                this.loadingSearch = false
            }
        },
        searchSubscribersList(){
            this.loadingSubcribers = true
            this.loadmoreSubcribers = false
            this.page = 1;
            this.loadingSearch = true
            this.handleGetUserSubscriber(this.dateType, this.status, this.page, this.keyword, this.fromDate, this.toDate)
        },
        async resumeSubscription(id){
			this.$confirm.require({
                message: this.$t('Do you want to resume this subscription?'),
                header: this.$t('Please confirm'),
                acceptLabel: this.$t('Ok'),
                rejectLabel: this.$t('Cancel'),
                accept: async () => {
                    try {
						await storeSubscriberResume(id)
                        const sub = this.subcribersList.find(s => s.id === id);
                        if (sub) {
                            sub.canResume = false;
                            sub.canCancel = true;
                        }
					} catch (error) {
						this.showError(error.error)
					}
                }
            })
		},
        async cancelSubscription(id){
			this.$confirm.require({
                message: this.$t('Are you sure you want to cancel this subscription?'),
                header: this.$t('Please confirm'),
                acceptLabel: this.$t('Ok'),
                rejectLabel: this.$t('Cancel'),
                accept: async () => {
                    try {
						await storeSubscriberCancel(id)
                        const sub = this.subcribersList.find(s => s.id === id);
                        if (sub) {
                            sub.canCancel = false;
                            sub.canResume = true;
                        }
					} catch (error) {
						this.showError(error.error)
					}
                }
            })
		}
    }
}
</script>