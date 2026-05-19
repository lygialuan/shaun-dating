<template>
    <template v-if="userInfo">
        <div class="header-profile-cover relative overflow-hidden rounded-none md:rounded-t-base-lg pb-[38.5%]">
            <div class="absolute inset-0 h-full">
                <div class="bg-cover bg-center bg-no-repeat w-full h-full" :style="{ backgroundImage: `url(${userInfo.cover})`}"></div>
            </div>
            <BaseUploadCover v-if="authenticated && user.id == userInfo.id" class="header-profile-cover-button absolute top-base-2 end-base-2" />
        </div>
        <div class="header-profile-avatar relative inline-block p-base-1 ms-4 -mt-20 md:-mt-28 rounded-full" :class="(userInfo.story_id == 0) ? 'bg-white dark:bg-slate-800' : 'bg-gradient-story cursor-pointer'">
            <Avatar :user="userInfo" :size="screen.md ? 112 : 150" :activePopover="false" :border="false" :router="false" @click="(userInfo.story_id == 0) ? '' : showStoryDetail(userInfo.story_id)"/>
            <BaseUploadAvatar v-if="authenticated && user.id == userInfo.id" class="header-profile-avatar-button absolute top-0 end-1 md:top-2 md:end-2" />
        </div>
        <div class="header-profile-info px-4 pb-base-1">
            <div class="flex flex-wrap justify-between items-start gap-x-5 gap-y-1 mb-base-1">
                <div class="header-profile-info-content flex-1 max-w-full whitespace-nowrap">
                    <UserName :user="userInfo" :activePopover="false" class="header-profile-name text-base-lg font-extrabold" />
                    <div class="header-profile-username text-base-sm text-sub-color dark:text-slate-400 truncate">{{mentionChar + userInfo.user_name}}</div>				
                </div>						
                <div class="header-profile-info-buttons flex flex-wrap items-center gap-2">
                    <template v-if="userInfo.is_page">
                        <template v-if="userInfo.id === user.id">
                            <BaseButton v-if="config.advertising.enable" type="outlined" :to="{ name: 'advertisings' }">{{ $t('Advertise') }}</BaseButton>
                            <BaseButton v-if="config.user_page.featureEnable && !userInfo.is_page_feature" type="outlined" :to="{name: 'user_pages_feature'}">{{ $t('Feature Page') }}</BaseButton>
                            <BaseButton type="outlined" :to="{ name: 'user_pages_overview' }">{{ $t('Manage') }}</BaseButton>
                        </template>
                        <BaseButton v-if="userInfo.is_page_admin && config.advertising.enable" type="outlined" @click="handleClickSwitchPage(userInfo, true)">{{ $t('Advertise') }}</BaseButton>
                    </template>
                    <BaseButton v-if="userInfo.canSwitch" type="outlined" @click="handleClickSwitchPage(userInfo, false)">{{ $t('Switch') }}</BaseButton>
                    <template v-if="user.id === userInfo.id">
                        <BaseButton v-if="!userInfo.is_verify && config.userVerifyEnable" type="outlined" @click="clickVerify">{{ userInfo.is_page ? $t('Verify Page') : $t('Verify Profile') }}</BaseButton>
                        <BaseButton type="outlined" :to="{name: 'setting_index'}">{{ userInfo.is_page ? $t('Edit Page') : $t('Edit Profile')}}</BaseButton>
                    </template>
                    <template v-else>
                        <template v-if="userInfo.can_send_message || userInfo.chat_room_id">
                            <MessageButton :user_id="userInfo.id"></MessageButton>
                        </template>
                        <template v-if="userInfo.can_follow">
                            <BaseButton v-if="userInfo.is_followed" @click="unFollowUser(userInfo.id)" type="outlined">{{$t('Unfollow')}}</BaseButton>
                            <BaseButton v-else @click="followUser(userInfo.id)" type="outlined">{{$t('Follow')}}</BaseButton>
                        </template>
                        <BaseButton v-if="authenticated" type="outlined" icon="more_horiz_outlined" @click="openProfileMenu()" />
                    </template>
                    <BaseButton v-if="userInfo.is_page" type="outlined" icon="search" @click="handleOpenSearch" />
                </div>
            </div>
            <div v-if="userInfo.categories && userInfo.categories.length" class="flex flex-wrap mb-1">
                <span v-for="(category, index) in userInfo.categories" :key="category.id">
                    <router-link :to="{name: 'user_pages', query: {category_id: category.id}}">{{ category.name }}</router-link>
                    {{ (index === userInfo.categories.length - 1) ? '' : '&middot;&nbsp;'}}
                </span>
            </div>
            <div v-if="!userInfo.check_private" class="header-profile-count text-base-sm">
                <span>{{ $filters.numberShortener(userInfo.post_count, $t('[number] Post'), $t('[number] Posts')) }}</span>
                <template v-if="userInfo.show_following">
                    &middot;
                    <button @click="openFollowingModal()">{{ $filters.numberShortener(userInfo.following_count, $t('[number] Following'), $t('[number] Following')) }}</button> 
                </template>
                <template v-if="userInfo.show_follower">
                    &middot;
                    <button @click="openFollowerModal()">{{ $filters.numberShortener(userInfo.follower_count, $t('[number] Follower'), $t('[number] Followers')) }}</button>
                </template>
            </div>						
            <div v-if="userInfo.check_privacy" class="text-base-sm break-word mt-1">{{userInfo.bio}}</div>
        </div>
    </template>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { changeUrl, checkPopupBodyClass } from '@/utility'
import { toggleFollowUser } from '@/api/follow'
import { switchPage } from '@/api/page'
import { useAuthStore } from '@/store/auth'
import { useAppStore } from '@/store/app'
import { useProfileStore } from '@/store/profile'
import { useUtilitiesStore } from '@/store/utilities'
import Constant from '@/utility/constant'
import BaseButton from '@/components/inputs/BaseButton.vue'
import MessageButton from '@/components/utilities/MessageButton.vue'
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'
import ProfileUserMenu from '@/pages/profile/ProfileUserMenu.vue'
import ListFollowingModal from '@/components/modals/ListFollowingModal.vue'
import ListFollowerModal from '@/components/modals/ListFollowerModal.vue'
import StoryDetailModal from '@/components/stories/StoryDetailModal.vue'
import BaseUploadCover from '@/components/inputs/BaseUploadCover.vue'
import BaseUploadAvatar from '@/components/inputs/BaseUploadAvatar.vue'
import SearchPostsModal from '@/components/modals/SearchPostsModal.vue'
import localData from '@/utility/localData'

export default {
    components: { BaseButton, MessageButton, Avatar, UserName, BaseUploadCover, BaseUploadAvatar },
    props: ['data', 'params', 'position'],
    data() {
		return {
            mentionChar: Constant.MENTION
		}
	},
    computed: {
        ...mapState(useAppStore, ['config', 'screen']),
        ...mapState(useAuthStore, ['user', 'authenticated']),
        ...mapState(useProfileStore, ['userInfo']),
    },
    methods:{
        ...mapActions(useUtilitiesStore, ['setSelectedPage']),
        openProfileMenu(){
            this.$dialog.open(ProfileUserMenu, {
                data: {
                    userInfo: this.userInfo
                },
                props:{
                    showHeader: false,
                    class: 'dropdown-menu-modal',
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
                onClose: () => {
                    checkPopupBodyClass();
                }
            });
        },
		openFollowingModal(){
            this.$dialog.open(ListFollowingModal, {
				data: {
                    user: this.userInfo
                },
                props:{
                    header: this.$t('Following'),
                    class: 'follow-modal',
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                }         
            });
        },
        openFollowerModal(){
            this.$dialog.open(ListFollowerModal, {
				data: {
                    user: this.userInfo
                },
                props:{
                    header: this.$t('Followers'),
                    class: 'follow-modal',
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                }         
            });
        },
        showStoryDetail(storyId){
			if (storyId > 0) {
				let storyUrl = this.$router.resolve({
					name: 'story_view',
					params: { 'storyId': storyId }
				});
				changeUrl(storyUrl.fullPath)
				this.$dialog.open(StoryDetailModal, {
					data: {
						id: storyId,
						storiesList: [storyId]
					},
					props:{
						class: 'p-dialog-story p-dialog-story-detail p-dialog-no-header-title',
						modal: true,
						showHeader: false,
						draggable: false
					},
					onClose: () => {
						changeUrl(this.$router.currentRoute.value.fullPath)
						checkPopupBodyClass();
					}
				});
			}
        },
        async followUser(userId) {
            if(this.authenticated){
				try {
					await toggleFollowUser({
						id: userId,
						action: "follow"
					});
					this.userInfo.is_followed = true
					/*this.userInfo.check_privacy = true
					if (this.userInfo.chat_privacy == Constant.CHAT_PRIVACY_FOLLOWER) {
						this.userInfo.can_send_message = true
					}*/
					this.updateFollowStatus({userId, status: 'follow'})
				}
				catch (error) {
					this.showError(error.error)
				}
			}else{
				this.showRequireLogin()
			}
        },
        async unFollowUser(userId) {
            try {
                await toggleFollowUser({
                    id: userId,
                    action: "unfollow"
                });
                this.userInfo.is_followed = false
				/*if (this.userInfo.privacy == Constant.USER_PRIVACY_FOLLOWER) {
					this.userInfo.check_privacy = false
				}
				if (this.userInfo.chat_privacy == Constant.CHAT_PRIVACY_FOLLOWER) {
						this.userInfo.can_send_message = false
				}*/
				this.updateFollowStatus({userId, status: 'unfollow'})
            }
            catch (error) {
				this.showError(error.error)
            }
        },
        async handleClickSwitchPage(page, ads){
			this.setSelectedPage(page)
            setTimeout(async() => {
                try {  
                    await switchPage(page.id)
                    localData.removePwa()
                    if (ads) {
                        let url = this.$router.resolve({
                            name: 'advertisings',
                        });
                        window.location.href = window.siteConfig.siteUrl + url.fullPath
                    } else {
                        window.location.reload()
                    }
                    
                } catch (error) {
                    this.showError(error.error)
                    this.setSelectedPage(null)
                }
            }, 1500);
		},
        clickVerify() {
            let permission = 'user_verify.send_request'
			if(this.checkPermission(permission)){
				this.$router.push({ name: 'verify_profile'})
			}
        },
        handleOpenSearch(){
            this.$dialog.open(SearchPostsModal, {
                data: {
                    item: this.userInfo,
                    type: 'page'
                },
                props:{
                    header: this.$t('Search Page'),
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                }
            });
        }
    }
}
</script>