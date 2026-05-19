<template>
    <Error v-if="error">{{error}}</Error>
    <UsersList :loading="loading_users" :usersList="votesList" :has-load-more="loadmore_status" @load-more="getVotesList(dialogRef.data.pollItemId, currentPage)" />
</template>

<script>
import { getPollVotes } from '@/api/posts'
import UsersList from '@/components/lists/UsersList.vue'
import Error from '@/components/utilities/Error.vue'

export default {
    components: { Error, UsersList },
    inject: {
        dialogRef: {
            default: null
        }
    },
    data(){
        return{
            error: null,
            loadmore_status: false,
            loading_users: true,
            votesList: [],
            currentPage: 1
        }
    },
    mounted(){
        if(this.dialogRef.data.pollItemId){
            this.getVotesList(this.dialogRef.data.pollItemId, this.currentPage)
        }
    },
    methods: {
        async getVotesList(pollItemId, page) {
            try {
                const response = await getPollVotes(pollItemId, page)
                if(page === 1){
                    this.votesList = response.items
                }else{
                    this.votesList = window._.concat(this.votesList, response.items);
                }
                if(response.has_next_page){
                    this.loadmore_status = true
                    this.currentPage++;
                }else{
                    this.loadmore_status = false
                }
            }
            catch (error) {
                this.error = error
            } finally {
                this.loading_users = false
            }
        }
    }
}
</script>