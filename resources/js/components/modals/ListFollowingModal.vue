<template>
    <Error v-if="error">{{error}}</Error>
    <UsersList :loading="loading_users" :usersList="followingUsersList" :has-load-more="loadmore_status" @load-more="getFollowingUsersList(user.id, page)" />
</template>
<script>
import { getFollowingUsers } from '@/api/follow';
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
            followingUsersList: [],
            page: 1
        }
    },
    mounted() {
        this.getFollowingUsersList(this.user.id, this.page)
    },
    computed: {
        user(){
            return this.dialogRef.data.user
        }
    },
    methods: {
        async getFollowingUsersList(userId, page){
            try {             
				const response = await getFollowingUsers(userId, page)
                if(page === 1){
                    this.followingUsersList = response.items
                }else{
                    this.followingUsersList = window._.concat(this.followingUsersList, response.items);
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