<template>
    <div class="main-content-section">
        <form @submit.prevent="handleSearchPendingPosts" class="flex flex-col gap-base-2">
            <div class="flex flex-wrap items-center gap-base-2">
                <BaseInputText v-model="keyword" :placeholder="$t('Keyword')" class="md:flex-3"/>
                <BaseSelect v-model="sort" :options="sortsList" optionLabel="name" optionValue="value" class="md:flex-1" />
            </div>
            <div class="flex flex-wrap gap-base-2">
                <BaseSuggest v-model="selectedUser" :suggestions="membersList" optionLabel="user.name" @complete="handleGetGroupMember" :placeholder="$t('Author')" :emptySearchMessage="$t('No users found')" class="md:w-max">
                    <template #option="{ option }">
                        <AutoSuggestUserItem :user="option.user"/>
                    </template>
                </BaseSuggest>
                <BaseCalendar v-model="fromDate" :placeholder="$t('Start Date')" class="md:flex-1" showButtonBar />
                <BaseCalendar v-model="toDate" :placeholder="$t('End Date')" class="md:flex-1" showButtonBar />
                <BaseSelect v-model="type" :options="typesList" :placeholder="$t('All content type')" optionLabel="name" optionValue="value" class="md:flex-1" />
            </div>
            <BaseButton>{{ $t('Search') }}</BaseButton>
        </form>
    </div>
    <PendingPostsList :loading="loadingPendingPosts" :posts-list="pendingPostsList" @load-more="loadMorePosts">
        <template #actions="{ item }">
            <div class="flex gap-base-2">
                <BaseButton @click="handleAcceptPost(item.id)" class="flex-1">{{ $t('Accept') }}</BaseButton>
                <BaseButton @click="handleRejectPost(item.id)" class="flex-1" type="danger">{{ $t('Reject') }}</BaseButton>
            </div>
        </template>
        <template #empty>{{ $t('No pending posts') }}</template>
    </PendingPostsList>
</template>

<script>
import { getGroupMembers, getPendingPosts, deletePendingPost, acceptPendingPost } from '@/api/group'
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import BaseSelect from '@/components/inputs/BaseSelect.vue'
import PendingPostsList from '@/components/lists/PendingPostsList.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseCalendar from '@/components/inputs/BaseCalendar.vue'
import AutoSuggestUserItem from '@/components/user/AutoSuggestUserItem.vue'
import BaseSuggest from '@/components/inputs/BaseSuggest.vue'

export default {
    props: ['adminConfig'],
    components: { BaseInputText, BaseSelect, PendingPostsList, BaseButton, BaseCalendar, AutoSuggestUserItem, BaseSuggest },
    data(){
        return{
            selectedUser: '',
            keyword: '',
            fromDate: '',
            toDate: '',
            type: '',
            typesList: [],
            sort: 'last',
            currentPage: 1,
            loadingPendingPosts: true,
            pendingPostsList: [],
            currentMemberPage: 1,
            membersList: []
        }
    },
    computed:{
        sortsList(){
            return [
                { value: 'last', name: this.$t('Newest first') },
                { value: 'first', name: this.$t('Oldest first') },
            ]
        },
        userId() {
            return this.selectedUser.length ? this.selectedUser[0].user.id : ''
        }
    },
    mounted(){
        this.handleGetPendingPosts(this.adminConfig.group.id, this.userId, this.keyword, this.currentPage, this.formatDateTime(this.fromDate), this.formatDateTime(this.toDate), this.type, this.sort)
        this.typesList = Object.entries(this.adminConfig.postTypes).map(([value, name]) => ({ value, name }));
    },
    methods: {
        async handleGetGroupMember(event){
            try {
                const response = await getGroupMembers(this.adminConfig.group.id, event.query, this.currentMemberPage)
                if (this.currentMemberPage == 1) {
                    this.membersList = [];
                }
                this.membersList = window._.concat(this.membersList, response.items)
                if(response.has_next_page){
                    this.currentMemberPage++;
                }
            } catch (error) {
                this.showError(error.error)
            }
        },
        async handleGetPendingPosts(groupId, userId, keyword, page, fromDate, toDate, type, sort){
            try {
                const response = await getPendingPosts(groupId, userId, keyword, page, fromDate, toDate, type, sort)
                if (page == 1) {
                    this.pendingPostsList = [];
                }
                this.pendingPostsList = window._.concat(this.pendingPostsList, response.items)
                return response
            } catch (error) {
                this.showError(error.error)
            } finally {
                this.loadingPendingPosts = false
            }
        },
        loadMorePosts($state) {
			this.handleGetPendingPosts(this.adminConfig.group.id, this.userId, this.keyword, ++this.currentPage, this.formatDateTime(this.fromDate), this.formatDateTime(this.toDate), this.type, this.sort).then((response) => {
				if (response.items.length === 0) {
					$state.complete()
				} else {
					$state.loaded()
				}
			})
		},
        handleRejectPost(pendingPostId){
            this.$confirm.require({
                message: this.$t('Are you sure you want to reject this pending post?'),
                header: this.$t('Please confirm'),
                acceptLabel: this.$t('Ok'),
                rejectLabel: this.$t('Cancel'),
                accept: async () => {
                    try {
                        await deletePendingPost(pendingPostId)
                        this.pendingPostsList = this.pendingPostsList.filter(item => item.id !== pendingPostId)
                        this.$emit('updated');
                    } catch (error) {
                        this.showError(error.error)
                    }
                }
            });
        },
        async handleAcceptPost(pendingPostId){
            try {
                await acceptPendingPost(pendingPostId)
                this.pendingPostsList = this.pendingPostsList.filter(item => item.id !== pendingPostId)
                this.$emit('updated');
            } catch (error) {
                this.showError(error.error)
            }
        },
        handleSearchPendingPosts(){
            this.currentPage = 1
            this.handleGetPendingPosts(this.adminConfig.group.id, this.userId, this.keyword, this.currentPage, this.formatDateTime(this.fromDate), this.formatDateTime(this.toDate), this.type, this.sort)
        }
    },
    emits: ['updated']
}
</script>