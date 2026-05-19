<template>
    <div v-if="groupInfo">
        <div class="header-profile-cover relative overflow-hidden rounded-none md:rounded-t-base-lg pb-[38.5%]">
            <div class="absolute inset-0 h-full">
                <div class="bg-cover bg-center bg-no-repeat w-full h-full" :style="{ backgroundImage: `url(${groupInfo.cover})`}"></div>
            </div>
            <BaseUploadCover v-if="groupInfo.is_admin" type="group" :subject-id="groupInfo.id" class="absolute top-base-2 end-base-2" />
        </div>
        <div class="header-profile-info p-4 dark:bg-dark-form-base dark:border-dark-form-base">
            <div class="flex flex-wrap justify-between items-start gap-x-5 gap-y-base-2 mb-base-2">
                <div class="header-profile-info-content flex-1 max-w-full whitespace-nowrap">
                    <div class="flex items-start gap-base-2 mb-1">
                        <div class="header-profile-name text-base-lg font-extrabold truncate">{{ groupInfo.name }}</div>
                        <button v-if="groupInfo.is_admin" @click="handleEditName"><BaseIcon name="pencil" /></button>
                    </div>
                    <div class="flex items-center gap-base-1">
                        <BaseIcon :name="privacyIcon(groupInfo.type)"/>
                        <span>{{ groupInfo.type_text }}</span>
                    </div>
                </div>						
                <div class="header-profile-info-buttons flex flex-wrap items-center gap-2">             
                    <BaseButton v-if="groupInfo.canJoin" type="outlined" :loading="loadingJoinButton" @click="handleJoinGroup(groupInfo)">{{ $t('Join') }}</BaseButton>
                    <BaseButton v-if="groupInfo.canLeave" :loading="loadingLeaveButton" type="danger-outlined" @click="handleLeaveGroup(groupInfo)">{{ $t('Leave') }}</BaseButton>               
                    <BaseButton v-if="groupInfo.request_id" type="danger-outlined" :loading="loadingCancelButton" @click="handleCancelPending(groupInfo)">{{ $t('Cancel Request') }}</BaseButton>
                    <BaseButton v-if="groupInfo.is_admin" type="outlined" :to="{ name: 'groups_manage', params: { id: groupInfo.id } }" :badge="shortenNumber(countRequest)">{{ $t('Manage') }}</BaseButton>
                    <BaseButton v-if="groupInfo.is_member" type="outlined" icon="bell" @click="handleOpenNotificationSettings" />
                    <BaseButton v-if="groupInfo.canView" type="outlined" icon="search" @click="handleOpenSearch" />
                    <BaseButton type="outlined" icon="more_horiz_outlined" @click="handleOpenOptionsMenu" />
                </div>
            </div>
            <div v-if="groupInfo.categories && groupInfo.categories.length" class="header-profile-info-categories flex flex-wrap mb-1">
                <span v-for="(category, index) in groupInfo.categories" :key="category.id" class="header-profile-info-categories-item">
                    <router-link :to="{name: 'groups', params: { tab: 'all' }, query: {category_id: category.id}}">{{ category.name }}</router-link>
                    {{ (index === groupInfo.categories.length - 1) ? '' : '&middot;&nbsp;'}}
                </span>
            </div>
            <div v-if="groupInfo.members.length" class="header-profile-info-members flex items-center">
                <Avatar v-for="(member, index) in groupInfo.members" :key="member.id" :user="member" :border="false" :size="32" class="header-profile-info-members-item -ms-3 first:ms-0 relative border-3 border-white rounded-full dark:border-slate-800" :style="{ zIndex: groupInfo.members.length - index }" />
                <div @click="handleViewMembers()" class="text-base-xs text-sub-color ms-2 dark:text-slate-400" :class="groupInfo.canView ? 'cursor-pointer' : ''">{{ $filters.numberShortener(groupInfo.member_count, $t('[number] member'), $t('[number] members')) }}</div>
            </div>
        </div>
    </div>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { useGroupStore } from '@/store/group'
import { useAppStore } from '@/store/app'
import { changeUrl } from '@/utility'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import BaseUploadCover from '@/components/inputs/BaseUploadCover.vue'
import Avatar from '@/components/user/Avatar.vue'
import EditNameModal from '@/components/modals/EditNameModal.vue'
import GroupNotificationSettingsModal from '@/components/modals/GroupNotificationSettingsModal.vue'
import GroupRulesModal from '@/components/modals/GroupRulesModal.vue'
import GroupOptionsMenu from '@/components/group/GroupOptionsMenu.vue'
import SearchPostsModal from '@/components/modals/SearchPostsModal.vue'

export default {
    props: ['data', 'params', 'position'],
    components: { BaseButton, BaseIcon, BaseUploadCover, Avatar },
    data(){
        return{
            loadingJoinButton: false,
            loadingLeaveButton: false,
            loadingCancelButton: false
        }
    },
    computed:{
        ...mapState(useGroupStore, ['groupInfo', 'groupRulesList']),
        countRequest(){
            return this.groupInfo.member_request_count + this.groupInfo.post_pending_count;
        }
    },
    methods:{
        ...mapActions(useGroupStore, ['handleDeleteJoinRequest', 'joinGroup', 'leaveGroup', 'handleGetGroupDetail']),
        ...mapActions(useAppStore, ['setCurrentRouter']),
        handleEditName(){
            this.$dialog.open(EditNameModal, {
                data: {
                    nameData: { 
                        content: this.groupInfo.name,
                        subject_id: this.groupInfo.id,
						subject_type: 'group'
                    }
                },
                props:{
					header: this.$t('Edit Name'),
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
				onClose: (options) => {
                    if (options.data) {
                        this.groupInfo.name = options.data.name
                    }
                }
            });
        },
        privacyIcon(value){
            switch (value) {
                case 1:
                    return 'lock'
                default:
                    return 'earth'
            }
        },
        handleOpenNotificationSettings(){
            this.$dialog.open(GroupNotificationSettingsModal, {
                data: {
                    groupInfo: this.groupInfo
                },
                props:{
					header: this.$t('Notification Settings'),
                    modal: true,
                    dismissableMask: true,
                    draggable: false,
                    class: 'p-dialog-lg',
                }
            });
        },
        async handleJoinGroup(group) {
            const joinGroup = async () => {
                this.loadingJoinButton = true
                try {
                    await this.joinGroup(group)
                    this.handleGetGroupDetail(this.groupInfo.id)
                } catch (error) {
                    this.showError(error.error)
                } finally {
                    this.loadingJoinButton = false
                }
            };

            if (this.groupRulesList.length) {
                this.$dialog.open(GroupRulesModal, {
                    data: { rulesList: this.groupRulesList },
                    props: {
                        header: this.$t('Group rules'),
                        modal: true,
                        dismissableMask: true,
                        draggable: false,
                        class: 'p-dialog-lg',
                    },
                    onClose: async (options) => {
                        if (options.data.accepted) await joinGroup();
                    },
                });
            } else {
                await joinGroup();
            }
        },
        async handleLeaveGroup(group){
            this.$confirm.require({
                message: this.$t('Do you want to leave this group?'),
                header: this.$t('Please confirm'),
                acceptLabel: this.$t('Ok'),
                rejectLabel: this.$t('Cancel'),
                accept: async () => {
                    this.loadingLeaveButton = true
                    try {
                        await this.leaveGroup(group)
                        this.handleGetGroupDetail(this.groupInfo.id)
                    } catch (error) {
                        this.showError(error.error)
                    } finally {
                        this.loadingLeaveButton = false
                    }
                }
            })
        },
        async handleCancelPending(group){
            this.$confirm.require({
                message: this.$t('Do you want to cancel your group join request?'),
                header: this.$t('Please confirm'),
                acceptLabel: this.$t('Ok'),
                rejectLabel: this.$t('Cancel'),
                accept: async () => {
                    this.loadingCancelButton = true
                    try {
                        await this.handleDeleteJoinRequest(group)
                    } catch (error) {
                        this.showError(error.error)
                    } finally {
                        this.loadingCancelButton = false
                    }
                }
            });
        },
        handleOpenOptionsMenu(){
            this.$dialog.open(GroupOptionsMenu, {
                data: {
                    group: this.groupInfo
                },
                props:{
                    showHeader: false,
                    class: 'dropdown-menu-modal',
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                }
            });
        },
        handleOpenSearch(){
            this.$dialog.open(SearchPostsModal, {
                data: {
                    item: this.groupInfo,
                    type: 'group'
                },
                props:{
                    header: this.$t('Search Group'),
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                }
            });
        },
        handleViewMembers(){
            if(!this.groupInfo.canView) return;
            let groupUrl = this.$router.resolve({
				name: 'group_profile',
				params: { id: this.groupInfo.id, slug: this.groupInfo.slug, tab: 'members' }
			});
			changeUrl(groupUrl.fullPath)
            this.setCurrentRouter({ name: groupUrl.name, params: groupUrl.params })
        }
    }
}
</script>