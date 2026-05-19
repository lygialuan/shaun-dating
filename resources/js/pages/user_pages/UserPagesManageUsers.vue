<template>
    <div class="main-content-section">
        <div class="main-content-section-header">
            <h3 class="main-content-section-header-title">{{ $t('Admin') }}</h3>
            <BaseButton v-if="user.is_owner_page" @click="handleClickTransferAdmin()">{{$t('Transfer Page')}}</BaseButton>
        </div>
        <UsersList v-if="user.page_owner" :loading="false" :usersList="[user.page_owner]">
            <template #sub_text="{ item }">
                <p v-if="item.show_follower" class="list_items_sub_text_color text-xs text-sub-color dark:text-slate-400">{{ $filters.numberShortener(item.follower_count, $t('[number] follower'), $t('[number] followers')) }}</p>
            </template>
            <template #actions="{}">
                <div></div>
            </template>
        </UsersList>
    </div>
    <div class="main-content-section">
        <div class="main-content-section-header">
            <h3 class="main-content-section-header-title">{{ $t('Moderators') }}</h3>
            <BaseButton v-if="user.is_owner_page" @click="handleClickAddModerator()">{{$t('Add moderator')}}</BaseButton>
        </div>
        <UsersList :loading="loadingAdminsList" :usersList="adminsList" :has-load-more="loadmoreAdminsList" @load-more="getAdminsList(currentPage)">
            <template #sub_text="{ item }">
                <p v-if="item.show_follower" class="list_items_sub_text_color text-xs text-sub-color dark:text-slate-400">{{ $filters.numberShortener(item.follower_count, $t('[number] follower'), $t('[number] followers')) }}</p> 
            </template>
            <template #actions="{ item }">
                <button v-if="user.is_owner_page || user.parent.id === item.id" @click="handleRemoveModerator(item.id)" class="list_items_button text-xs text-primary-color cursor-pointer font-bold dark:text-dark-primary-color">{{$t('Remove')}}</button>
            </template>
        </UsersList>
    </div>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { useAuthStore } from '@/store/auth'
import { useAppStore } from '@/store/app'
import { getAdminPage, removeAdminPage } from '@/api/page'
import BaseButton from '@/components/inputs/BaseButton.vue'
import AddModeratorsModal from '@/components/modals/AddModeratorsModal.vue'
import TransferAdminModal from '@/components/modals/TransferAdminModal.vue'
import UsersList from '@/components/lists/UsersList.vue'

export default {
    components: { BaseButton, UsersList },
    data(){
        return{
            currentPage: 1,
            loadingAdminsList: true,
            loadmoreAdminsList: false,
            adminsList: []
        }
    },
    mounted(){
        if (! this.user.is_page) {
            this.setErrorLayout(true)
        } else {
            this.getAdminsList(this.currentPage)
        }
    },
    computed: {
        ...mapState(useAuthStore, ["user"])
    },
    methods:{
        ...mapActions(useAppStore, ['setErrorLayout']),
        async getAdminsList(page){
            try {
                const response = await getAdminPage(page)
                if (page == 1) {
                    this.adminsList = [];
                }
                this.adminsList = window._.concat(this.adminsList, response.items)
                if(response.has_next_page){
                    this.loadmoreAdminsList = true
                    this.currentPage++;
                }else{
                    this.loadmoreAdminsList = false
                }
            } catch (error) {
                this.showError(error.error)
            } finally {
                this.loadingAdminsList = false
            }
        },
        handleClickTransferAdmin(){
            this.$dialog.open(TransferAdminModal, {
                props: {
                    header: this.$t('Transfer Admin Account'),
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                }
            })
        },
        handleClickAddModerator(){
            this.$dialog.open(AddModeratorsModal, {
                props: {
                    header: this.$t('Add Moderator'),
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
                onClose: (options) => {
                    if (options.data) {
                        this.adminsList = [...this.adminsList, options.data.adminInfo];
                    }
                }
            })
        },
        async handleRemoveModerator(userId){
            this.$confirm.require({
                message: this.$t('Do you want to remove this user?'),
                header: this.$t('Please confirm'),
                acceptLabel: this.$t('Ok'),
                rejectLabel: this.$t('Cancel'),
                accept: async () => {
                    try {
                        await removeAdminPage(userId)
                        this.showSuccess(this.$t('Remove Successfully'))
                        if(this.user.is_owner_page){
                            this.adminsList = this.adminsList.filter(admin => admin.id !== userId)
                        } else {
                            window.location.href = window.siteConfig.siteUrl
                        }
                    } catch (error) {
                        this.showError(error.error)
                    }
                }
            });
        }
    }
}
</script>