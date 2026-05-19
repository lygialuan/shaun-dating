<template>
    <Error v-if="error">{{error}}</Error>
    <UsersList :loading="loading_users" :usersList="followerUsersList" :has-load-more="loadmore_status" @load-more="getFollowerUsersList(user.id, page)" />
</template>
<script>
import { getFollowerUsers } from '@/api/follow';
import Error from '@/components/utilities/Error.vue';
import UsersList from '@/components/lists/UsersList.vue';

export default{
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
            followerUsersList: [],
            page: 1
        }
    },
    mounted() {
        this.getFollowerUsersList(this.user.id, this.page);
    },
    computed: {
        user(){
            return this.dialogRef.data.user
        }
    },
    methods: {
        async getFollowerUsersList(userId, page){
            try {
				const response = await getFollowerUsers(userId, page)
                if(page === 1){
                    this.followerUsersList = response.items
                }else{
                    this.followerUsersList = window._.concat(this.followerUsersList, response.items);
                }
                if(response.has_next_page){
                    this.loadmore_status = true
                    this.page++;
                }else{
                    this.loadmore_status = false
                }
                this.loading_users = false
			} catch (error) {
                this.error = error
                this.loading_users = false
			}
        }
    } 
}
</script>