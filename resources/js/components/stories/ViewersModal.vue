<template>
    <Error v-if="error">{{error}}</Error>
    <UsersList :loading="loading_users" :usersList="viewersList" :has-load-more="loadmore_status" @load-more="getStoryViewers(dialogRef.data.itemId, page)" />
</template>

<script>
import { getViewers } from '@/api/stories';
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
            viewersList: [],
            page: 1
        }
    },
    mounted(){
        if(this.dialogRef.data.itemId){
            this.getStoryViewers(this.dialogRef.data.itemId, this.page)
        }
    },
    methods: {
        async getStoryViewers(itemId, page) {
            try {
                const response = await getViewers(itemId, page)
                if(page === 1){
                    this.viewersList = response.items
                }else{
                    this.viewersList = window._.concat(this.viewersList, response.items);
                }
                if(response.has_next_page){
                    this.loadmore_status = true
                    this.page++;
                }else{
                    this.loadmore_status = false
                }
                this.loading_users = false
            }
            catch (error) {
                this.error = error
                this.loading_users = false
            }
        }
    }
}
</script>