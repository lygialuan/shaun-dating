<template>
    <div class="main-content-section-header">
        <h3 class="main-content-section-header-title">{{ $t('Admin') }}</h3>
        <BaseButton v-if="adminConfig.is_owner" @click="handleTransferAdmin()">{{$t('Transfer')}}</BaseButton>
    </div>
    <GroupMembersList :loading="!adminConfig.owner" :members-list="[adminConfig.owner]"/>
    <div class="flex flex-wrap items-center justify-between gap-2 my-base-2 pt-base-2 border-t border-divider dark:border-white/10">
        <h3 class="main-content-section-header-title">{{ $t('Moderators') }}</h3>
        <BaseButton v-if="adminConfig.is_owner" @click="handleAddModerator()">{{$t('Add moderator')}}</BaseButton>
    </div>
    <GroupMembersList :loading="loadingModeratorList" :members-list="moderatorsList" :has-load-more="loadmoreModeratorsList" @load-more="handleGetAdminsList(adminConfig.group.id, currentModeratorPage)">
        <template #actions="{ member }">
            <BaseButton v-if="adminConfig.is_owner || user.id === member.user.id" size="xs" @click="handleRemoveModerator(member)" type="danger">{{ $t('Remove') }}</BaseButton>
        </template>
        <template #empty>{{ $t('No moderators') }}</template>
    </GroupMembersList>
</template>

<script>
import { mapState } from 'pinia'
import { useAuthStore } from '@/store/auth'
import { getGroupAdmin, removeGroupAdmin } from '@/api/group'
import GroupMembersList from '@/components/lists/GroupMembersList.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import TransferAdminModal from '@/components/modals/TransferAdminModal.vue'
import AddModeratorsModal from '@/components/modals/AddModeratorsModal.vue'

export default {
    props: ['adminConfig'],
    components: { GroupMembersList, BaseButton },
    data(){
        return{
            groupOwner: null,
            currentModeratorPage: 1,
            loadingModeratorList: true,
            moderatorsList: [],
            loadmoreModeratorsList: false
        }
    },
    computed:{
        ...mapState(useAuthStore, ["user"])
    },
    mounted(){
        this.handleGetAdminsList(this.adminConfig.group.id, this.currentModeratorPage)
    },
    methods: {
        async handleGetAdminsList(groupId, page){
            try {
                const response = await getGroupAdmin(groupId, page)
                if (page == 1) {
                    this.moderatorsList = [];
                    this.groupOwner = response.owner
                }
                this.moderatorsList = window._.concat(this.moderatorsList, response.items)
                if(response.has_next_page){
                    this.loadmoreModeratorsList = true
                    this.currentModeratorPage++;
                }else{
                    this.loadmoreModeratorsList = false
                }
            } catch (error) {
                this.showError(error.error)
            } finally {
                this.loadingModeratorList = false
            }
        },
        handleTransferAdmin(){
            this.$dialog.open(TransferAdminModal, {
                data: {
                    subject_type: 'group',
                    subject: this.adminConfig.group
                },
                props: {
                    header: this.$t('Transfer Admin Account'),
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                }
            })
        },
        handleAddModerator(){
            this.$dialog.open(AddModeratorsModal, {
                data: {
                    subject_type: 'group',
                    subject_id: this.adminConfig.group.id
                },
                props: {
                    header: this.$t('Add Moderator'),
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
                onClose: (options) => {
                    if (options.data) {
                        this.moderatorsList.push(options.data.adminInfo)
                        this.$emit('updated');
                    }
                }
            })
        },
        async handleRemoveModerator(member){
            this.$confirm.require({
                message: this.adminConfig.is_owner ? this.$t('Are you sure you want to remove this moderator?') : this.$t('Are you sure you want to remove yourself from the moderators list?'),
                header: this.$t('Please confirm'),
                acceptLabel: this.$t('Ok'),
                rejectLabel: this.$t('Cancel'),
                accept: async () => {
                    try {
                        await removeGroupAdmin(member.id)
                        this.showSuccess(this.$t('Remove Successfully'))
                        this.moderatorsList = this.moderatorsList.filter(moderator => moderator.id !== member.id)
                        if(this.user.id === member.user.id){
                            this.$router.push({ name: 'group_profile', params: { id: this.adminConfig.group.id, slug: this.adminConfig.group.slug }})
                        } else {
                            this.$emit('updated');
                        }
                    } catch (error) {
                        this.showError(error.error)
                    }
                }
            });
        }
    },
    emits: ['updated']
}
</script>