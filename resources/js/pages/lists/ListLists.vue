<template>
    <ListListsDetail v-if="isListDetail" :item="selectedList" @back="handleBack" />
    <template v-else>
        <div class="flex items-center justify-between gap-3 mb-5">
            <h2 class="text-xl font-bold">{{ $t('Lists') }}</h2>
            <BaseButton @click="handleCreateNewList">{{ $t('Create New List') }}</BaseButton>
        </div>
        <div v-if="listCount" class="flex flex-col gap-5 text-main-color dark:text-white">
            <router-link 
                v-for="defaultItem in defaultList" 
                :key="defaultItem.name" 
                class="flex gap-base-2 items-center text-inherit"
                :to="{name: defaultItem.tab}"
            >
                <div class="flex items-center justify-center bg-web-wash w-[50px] h-[50px] flex-shrink-0 rounded-full dark:bg-dark-web-wash">
                    <BaseIcon :name="defaultItem.icon" size="30"/>
                </div>
                <div class="flex-1">
                    <div><b>{{ defaultItem.title }}</b></div>
                    <div class="text-sub-color dark:text-slate-400">{{  $filters.numberShortener(listCount[defaultItem.name], $t('[number] member'), $t('[number] members')) }}</div>
                </div>
            </router-link>
        </div>
        <div v-if="listsList.length" class="flex flex-col gap-5 border-t mt-5 pt-5 border-divider dark:border-white/10">
            <div
                v-for="item in listsList" 
                :key="item.id" 
                class="flex gap-base-2 items-center cursor-pointer text-main-color dark:text-white"
                @click="handleSelectList(item)"
            >
                <div class="flex items-center justify-center bg-web-wash w-[50px] h-[50px] flex-shrink-0 rounded-full dark:bg-dark-web-wash">
                    <BaseIcon name="list_users" size="30"/>
                </div>
                <div class="flex-1">
                    <div><b>{{ item.name }}</b></div>
                    <div class="text-sub-color dark:text-slate-400">{{ $filters.numberShortener(item.member_count, $t('[number] member'), $t('[number] members')) }}</div>
                </div>
                <div class="flex gap-2 items-center">
                    <button @click.stop="handleCreateNewList(item)" class="text-primary-color dark:text-dark-primary-color p-2">{{ $t('Edit') }}</button>
                    <button @click.stop="handleDeleteList(item.id)" class="text-base-red p-2">{{ $t('Delete') }}</button>
                </div>
            </div>
            <BaseButton v-if="hasMoreLists" @click="getUserList(this.page)">{{ $t('View more') }}</BaseButton>
        </div>
    </template>
</template>

<script>
import { getListCount, getUserList, deleteUserList } from '@/api/user_list'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import CreateUserList from '@/components/modals/CreateUserList.vue'
import ListListsDetail from './ListListsDetail.vue'

export default {
    components: {
        BaseIcon,
        BaseButton,
        ListListsDetail
    },
    data(){
        return{
            listCount: null,
            page: 1,
            loadingLists: true,
            listsList: [],
            hasMoreLists: false,
            isListDetail: false,
            selectedList: null
        }
    },
    computed:{
        defaultList(){
            return [
                {name: 'block_count', title: this.$t('Blocking User'), icon: 'list_blocking', tab: 'list_block_member'},
            ]
        }
    },
    mounted(){
        this.getListCount()
        this.getUserList(this.page)
    },
    methods:{
        async getListCount(){
            try {
                this.listCount = await getListCount()
            } catch (error) {
                this.showError(error.error)
            }
        },
        async getUserList(page){
            try {
                const response = await getUserList(page)
                if(page === 1){
                    this.listsList = []
                }
                this.listsList = window._.concat(this.listsList, response.items);
                if(response.has_next_page){
                    this.hasMoreLists = true
                    this.page++;
                }else{
                    this.hasMoreLists = false
                }
            } catch (error) {
                this.showError(error.error)
            } finally {
                this.loadingLists = false
            }
        },
        handleCreateNewList(item){
            this.$dialog.open(CreateUserList, {
                data:{
                    item: item
                },
                props:{
                    header: this.$t('Add New List'),
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
                onClose: (options) => {
                    const updatedItem = options.data?.item;
                    if (updatedItem) {
                        const index = this.listsList.findIndex((item) => item.id === updatedItem.id);
                        if (index !== -1) {
                            this.listsList[index] = updatedItem
                        } else {
                            this.listsList.unshift(updatedItem);
                        }
                    }
                }
            });
        },
        handleDeleteList(listId){
            this.$confirm.require({
                message: this.$t('Are you sure you want to delete this list?'),
                header: this.$t('Please confirm'),
                acceptLabel: this.$t('Ok'),
                rejectLabel: this.$t('Cancel'),
                accept: async () => {
                    try {
						await deleteUserList(listId)
                        this.listsList = this.listsList.filter(item => item.id !== listId)
                        this.showSuccess(this.$t('Remove Successfully'))
					} catch (error) {
						this.showError(error.error)
					}
                }
            })
        },
        handleSelectList(item){
            this.isListDetail = true
            this.selectedList = item
        },
        handleBack(item){
            this.isListDetail = false
            this.selectedList = null
            const index = this.listsList.findIndex(i => i.id === item.id);
            if (index !== -1) {
                this.listsList[index].member_count = item.member_count;
            }
        }
    }
}
</script>