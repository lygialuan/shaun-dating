<template>
	<div class="main-content-section">
        <UsersList :loading="loading_users" :usersList="searchUsersList" :has-load-more="true" @load-more="loadMoreUsers" :auto-load-more="true">
            <template #sub_text="{ item }">
                <p class="users-list-item-content-sub list_items_sub_text_color text-xs text-sub-color mb-1 dark:text-slate-400 truncate">{{mentionChar + item.user_name}}</p>
                <p v-if="item.show_follower" class="users-list-item-content-sub list_items_sub_text_color flex items-center text-xs text-sub-color dark:text-slate-400"><BaseIcon name="users" size="16" class="me-1"/>{{ $filters.numberShortener(item.follower_count, $t('[number] follower'), $t('[number] followers')) }}</p>
            </template>
        </UsersList>
	</div>
</template>

<script>
import { getSearchResults } from '@/api/search';
import UsersList from '@/components/lists/UsersList.vue';
import BaseIcon from '@/components/icons/BaseIcon.vue'
import Constant from '@/utility/constant'

export default {
	components:{ 
        UsersList,
        BaseIcon
    },
	props: ["search_type", "type", "query"],
	data(){
        return{
            mentionChar: Constant.MENTION,
            error: null,
            queryData: this.query,
            loading_users: true,
            searchUsersList: [],
            currentPage: 1
        }
    },
    mounted(){
        this.getSearchUsersList(this.queryData, this.currentPage)
    },
    watch: {
        '$route'() {
			this.queryData = !window._.isNil(this.$route.query.q) ? this.$route.query.q : ''
            this.currentPage = 1
            if(this.queryData){
                this.getSearchUsersList(this.queryData, this.currentPage)
            }
        }
    },
    methods: {
        async getSearchUsersList(query, page){
            try {             
				const response = await getSearchResults(this.search_type, query, this.type, page)

                // Apply data to users list
                if(this.currentPage === 1){
                    this.searchUsersList = response
                }else{
                    this.searchUsersList = window._.concat(this.searchUsersList, response);
                }
                return response
			} catch (error) {
                this.showError(error.error)
			} finally {
                this.loading_users = false
            }
        },
        loadMoreUsers($state) {
			this.getSearchUsersList(this.queryData, ++this.currentPage).then((response) => {
				if (response.length === 0) {
					$state.complete()
				} else {
					$state.loaded()
				}
			})
		}
    } 
}
</script>