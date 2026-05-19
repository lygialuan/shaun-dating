<template>
    <div class="main-content-section-header">
        <h3 class="main-content-section-header-title">{{ $t('Pending requests') }}</h3>
        <div v-if="selectedJoinRequestsList.length" class="flex flex-wrap gap-base-2">
            <BaseButton @click="handleAcceptMultiRequests()">{{$t('Approve Selected')}}</BaseButton>
            <BaseButton @click="handleDeleteMultiJoinRequests()" type="danger">{{$t('Reject Selected')}}</BaseButton>
        </div>
    </div>
    <BaseInputText v-model="searchJoinRequestText" @input="handleSearchJoinRequests" :placeholder="$t('Search join requests')" class="max-w-xs mb-base-2"/>
    <GroupMembersList :loading="loadingJoinRequestsList" :members-list="joinRequestsList" :has-load-more="loadmoreJoinRequestsList" @load-more="handleGetGroupJoinRequests(adminConfig.group.id, searchJoinRequestText, currentJoinRequestPage)">
        <template #actions="{ member }">
            <div class="flex items-center gap-base-2">
                <BaseButton size="xs" @click="handleApproveJoinRequest(member.id)">{{ $t('Approve') }}</BaseButton>
                <BaseButton size="xs" @click="handleDeleteJoinRequest(member.id)" type="danger">{{ $t('Reject') }}</BaseButton>
                <BaseCheckbox v-model="selectedJoinRequestsList" :value="member.id" />
            </div>
        </template>
        <template #empty>{{ $t('No pending requests') }}</template>
    </GroupMembersList>
</template>

<script>
import { getGroupJoinRequests, deleteMultiJoinRequests, acceptMultiJoinRequest, deleteJoinRequest, acceptJoinRequest } from '@/api/group'
import GroupMembersList from '@/components/lists/GroupMembersList.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseCheckbox from '@/components/inputs/BaseCheckbox.vue'

export default {
    props: ['adminConfig'],
    components: { BaseCheckbox, GroupMembersList, BaseButton },
    data(){
        return{
            selectedJoinRequestsList: [],
            searchJoinRequestText: '',
            currentJoinRequestPage: 1,
            loadingJoinRequestsList: true,
            joinRequestsList: [],
            loadmoreJoinRequestsList: false
        }
    },
    mounted(){
        this.handleGetGroupJoinRequests(this.adminConfig.group.id, this.searchJoinRequestText, this.currentJoinRequestPage)
    },
    methods: {
        async handleGetGroupJoinRequests(groupId, query, page){
            try {
                const response = await getGroupJoinRequests(groupId, query, page)
                if (page == 1) {
                    this.joinRequestsList = [];
                }
                this.joinRequestsList = window._.concat(this.joinRequestsList, response.items)
                if(response.has_next_page){
                    this.loadmoreJoinRequestsList = true
                    this.currentJoinRequestPage++;
                }else{
                    this.loadmoreJoinRequestsList = false
                }
            } catch (error) {
                this.showError(error.error)
            } finally {
                this.loadingJoinRequestsList = false
            }        
        },
        debouncedGetJoinRequestsList: window._.debounce(function() {
            this.handleGetGroupJoinRequests(this.adminConfig.group.id, this.searchJoinRequestText, this.currentJoinRequestPage);
        }, 500),
        handleSearchJoinRequests(){
            this.currentJoinRequestPage = 1
            this.debouncedGetJoinRequestsList();
        },
        async handleApproveJoinRequest(requestId){
            try {
                await acceptJoinRequest(requestId)
                this.joinRequestsList = this.joinRequestsList.filter(request => request.id !== requestId)
                this.$emit('updated');
            } catch (error) {
                this.showError(error.error)
            }
        },
        handleAcceptMultiRequests(){
            this.$confirm.require({
                message: this.$t('Are you sure you want to approve selected requests?'),
                header: this.$t('Please confirm'),
                acceptLabel: this.$t('Ok'),
                rejectLabel: this.$t('Cancel'),
                accept: async () => {
                    try {
                        await acceptMultiJoinRequest(this.adminConfig.group.id, this.selectedJoinRequestsList)
                        this.joinRequestsList = this.joinRequestsList.filter(request => !this.selectedJoinRequestsList.includes(request.id))
                        this.$emit('updated');
                    } catch (error) {
                        this.showError(error.error)
                    }
                }
            })
        },
        async handleDeleteJoinRequest(requestId){
            this.$confirm.require({
                message: this.$t('Are you sure you want to reject this request?'),
                header: this.$t('Please confirm'),
                acceptLabel: this.$t('Ok'),
                rejectLabel: this.$t('Cancel'),
                accept: async () => {
                    try {
                        await deleteJoinRequest(requestId)
                        this.joinRequestsList = this.joinRequestsList.filter(request => request.id !== requestId)
                        this.$emit('updated');
                    } catch (error) {
                        this.showError(error.error)
                    }
                }
            })
        },
        handleDeleteMultiJoinRequests(){
            this.$confirm.require({
                message: this.$t('Are you sure you want to reject selected requests?'),
                header: this.$t('Please confirm'),
                acceptLabel: this.$t('Ok'),
                rejectLabel: this.$t('Cancel'),
                accept: async () => {
                    try {
                        await deleteMultiJoinRequests(this.adminConfig.group.id, this.selectedJoinRequestsList)
                        this.joinRequestsList = this.joinRequestsList.filter(request => !this.selectedJoinRequestsList.includes(request.id))
                        this.$emit('updated');
                    } catch (error) {
                        this.showError(error.error)
                    }
                }
            })
        }
    },
    emits: ['updated']
}
</script>