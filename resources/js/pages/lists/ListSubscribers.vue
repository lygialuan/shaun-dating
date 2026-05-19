<template>
    <div class="flex items-center justify-between gap-3 mb-5">
        <div class="flex items-center gap-base-2">
            <router-link :to="{ name: isMobile ? 'list_index' : 'list_lists' }" class="text-inherit">
                <BaseIcon name="caret_left" class="align-middle" />
            </router-link>
            <h2 class="text-xl font-bold">{{ $t('Subscribers') }}</h2>
        </div>
    </div>
    <GroupMembersList :loading="loadingSubcribers" :members-list="subcribersList" :has-load-more="hasMoreSubscribers" @load-more="handleGetUserSubscriber(dateType, status, page, keyword, fromDate, toDate)" />
</template>

<script>
import { mapState } from 'pinia'
import { useAppStore } from '@/store/app';
import { getUserSubscriber } from '@/api/paid_content'
import GroupMembersList from '@/components/lists/GroupMembersList.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'

export default {
    components: { 
        GroupMembersList,
        BaseIcon
    },
    data(){
        return{
            dateType: 'all',
            status: 'all',
            page: 1,
            keyword: '',
            fromDate: '',
            toDate: '',
            loadingSubcribers: true,
            hasMoreSubscribers: false,
            subcribersList: []
        }
    },
    computed: {
        ...mapState(useAppStore, ['isMobile'])
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
                    this.hasMoreSubscribers = true
                    this.page++;
                }else{
                    this.hasMoreSubscribers = false
                }
            } catch (error) {
                this.showError(error.error)
            } finally {
                this.loadingSubcribers = false
            }
        }
    }
}
</script>