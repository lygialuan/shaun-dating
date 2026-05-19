<template>
    <PendingPostsList :loading="loadingMyPendingPosts" :posts-list="myPendingPostsList" @load-more="loadMorePosts">
        <template #actions="{ item }">
            <BaseButton @click="handleDeletePost(item.id)" type="danger" fluid>{{ $t('Delete') }}</BaseButton>
        </template>
        <template #empty>{{ $t('No pending posts') }}</template>
    </PendingPostsList>
</template>

<script>
import { getMyPendingPosts, deleteMyPendingPost } from '@/api/group'
import PendingPostsList from '@/components/lists/PendingPostsList.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'

export default {
    props: ['userConfig'],
    components: { PendingPostsList, BaseButton },
    data(){
        return{
            currentPage: 1,
            loadingMyPendingPosts: true,
            myPendingPostsList: []
        }
    },
    mounted(){
        this.handleGetMyPendingPosts(this.userConfig.group.id, this.currentPage)
    },
    methods: {
        async handleGetMyPendingPosts(groupId, page){
            try {
                const response = await getMyPendingPosts(groupId, page)
                if (page == 1) {
                    this.myPendingPostsList = [];
                }
                this.myPendingPostsList = window._.concat(this.myPendingPostsList, response.items)
                return response
            } catch (error) {
                this.showError(error.error)
            } finally {
                this.loadingMyPendingPosts = false
            }
        },
        loadMorePosts($state) {
			this.handleGetMyPendingPosts(this.userConfig.group.id, ++this.currentPage).then((response) => {
				if (response.items.length === 0) {
					$state.complete()
				} else {
					$state.loaded()
				}
			})
		},
        handleDeletePost(pendingPostId){
            this.$confirm.require({
                message: this.$t('Do you want to remove this pending post?'),
                header: this.$t('Please confirm'),
                acceptLabel: this.$t('Ok'),
                rejectLabel: this.$t('Cancel'),
                accept: async () => {
                    try {
                        await deleteMyPendingPost(pendingPostId)
                        this.myPendingPostsList = this.myPendingPostsList.filter(item => item.id !== pendingPostId)
                    } catch (error) {
                        this.showError(error.error)
                    }
                }
            });
        }
    }
}
</script>