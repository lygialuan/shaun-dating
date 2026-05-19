<template>
    <div class="flex items-center justify-between gap-3 mb-5">
        <div class="flex items-center gap-base-2">
            <router-link :to="{ name: isMobile ? 'list_index' : 'list_lists' }" class="text-inherit">
                <BaseIcon name="caret_left" class="align-middle" />
            </router-link>
            <h2 class="text-xl font-bold">{{ $t('Blocked') }}</h2>
        </div>
    </div>
    <div class="tabs-list flex gap-base-2 mb-4">
        <button @click="filterBlockUsersList('user')" class="tabs-list-item p-base-2 rounded-base-lg" :class="blockType === 'user' ? 'active bg-primary-color text-white dark:bg-dark:bg-slate-800' : 'bg-web-wash dark:bg-dark-web-wash'">{{ $t('Users') }}</button>
        <button @click="filterBlockUsersList('page')" class="tabs-list-item p-base-2 rounded-base-lg" :class="blockType === 'page' ? 'active bg-primary-color text-white dark:bg-dark:bg-slate-800' : 'bg-web-wash dark:bg-dark-web-wash'">{{ $t('Profiles') }}</button>
    </div>
    <Error v-if="error">{{error}}</Error>
    <UsersList :loading="loadingUsers" :users-list="blockUsersList" :has-load-more="loadmoreStatus" @load-more="getBlockUsersList(blockType, page)">
        <template #empty>
            <div class="text-center">
                {{ blockType === 'user' ? $t('No Users') : $t('No Profiles') }}
            </div>
		</template>
        <template #actions="{ item }">
            <button class="list_items_button text-xs text-primary-color cursor-pointer font-bold dark:text-dark-primary-color" @click="unBlock(item.id)">{{$t('Unblock')}}</button>
        </template>
    </UsersList>
</template>

<script>
import { mapState } from 'pinia'
import { useAppStore } from '@/store/app';
import { getBlockUsersList, toggleBlockUser } from '@/api/user'
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
            blockUsersList: [],
            blockType: 'user',
            page: 1
        }
    },
    computed: {
        ...mapState(useAppStore, ['isMobile'])
    },
    mounted() {
        this.getBlockUsersList(this.blockType, this.page);
    },
    methods: {
        async getBlockUsersList(type, page){
            try {
				const response = await getBlockUsersList(type, page)
                if(page === 1){
                    this.blockUsersList = response.items
                }else{
                    this.blockUsersList = window._.concat(this.blockUsersList, response.items);
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
        filterBlockUsersList(type){
            this.blockType = type
            this.page = 1
            this.getBlockUsersList(this.blockType, this.page);
        },
        async unBlock(userId){
            try {
                await toggleBlockUser({
                    id: userId,
                    action: "remove"
                });
                this.blockUsersList = this.blockUsersList.filter(user => user.id !== userId);
            } catch (error) {
                this.showError(error.error)
            }         
        }
    } 
}
</script>