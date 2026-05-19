<template>
    <div class="flex items-center justify-between gap-3 mb-5">
        <div class="flex items-center gap-base-2">
            <router-link :to="{ name: isMobile ? 'list_index' : 'list_lists' }" class="text-inherit">
                <BaseIcon name="caret_left" class="align-middle" />
            </router-link>
            <h2 class="text-xl font-bold">{{ $t('Followers') }}</h2>
        </div>
    </div>
    <div class="tabs-list flex gap-base-2 mb-4">
        <button @click="filterFollowerUsersList('user')" class="tabs-list-item p-base-2 rounded-base-lg" :class="followerType === 'user' ? 'active bg-primary-color text-white dark:bg-dark:bg-slate-800' : 'bg-web-wash dark:bg-dark-web-wash'">{{ $t('Users') }}</button>
        <button @click="filterFollowerUsersList('page')" class="tabs-list-item p-base-2 rounded-base-lg" :class="followerType === 'page' ? 'active bg-primary-color text-white dark:bg-dark:bg-slate-800' : 'bg-web-wash dark:bg-dark-web-wash'">{{ $t('Pages') }}</button>
    </div>
    <Error v-if="error">{{error}}</Error>
    <UsersList :loading="loadingUsers" :users-list="followerUsersList" :has-load-more="loadmoreStatus" @load-more="getFollowerUsersList(followerType, page)">
        <template #empty>
            <div class="text-center">
                {{ followerType === 'user' ? $t('No Users') : $t('No Pages') }}
            </div>
		</template>
    </UsersList>
</template>

<script>
import { mapState } from 'pinia'
import { useAppStore } from '@/store/app';
import { getMyFollowerUsers } from '@/api/follow';
import Error from '@/components/utilities/Error.vue';
import UsersList from '@/components/lists/UsersList.vue';
import BaseIcon from '@/components/icons/BaseIcon.vue'

export default{
    components: { 
        Error, 
        UsersList,
        BaseIcon
    },
    data(){
        return{
            error: null,
            loadmoreStatus: false,
            loadingUsers: true,
            followerUsersList: [],
            followerType: 'user',
            page: 1
        }
    },
    computed: {
        ...mapState(useAppStore, ['isMobile'])
    },
    mounted() {
        this.getFollowerUsersList(this.followerType, this.page);
    },
    methods: {
        async getFollowerUsersList(type, page){
            try {
				const response = await getMyFollowerUsers(type, page)
                if(page === 1){
                    this.followerUsersList = response.items
                }else{
                    this.followerUsersList = window._.concat(this.followerUsersList, response.items);
                }
                if(response.has_next_page){
                    this.loadmoreStatus = true
                    this.page++;
                }else{
                    this.loadmoreStatus = false
                }
                this.loadingUsers = false
			} catch (error) {
                this.error = error
                this.loadingUsers = false
			}
        },
        filterFollowerUsersList(type){
            this.followerType = type
            this.page = 1
            this.getFollowerUsersList(this.followerType, this.page);
        }
    } 
}
</script>