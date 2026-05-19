<template>
    <div ref="popover_item" @mouseenter="hoverPopoverItem" @mouseleave="leavePopoverItem">
        <slot></slot>
        <Teleport to="body">
            <Transition name="fade">
                <div class="absolute max-w-xs w-full z-[1199]" ref="popover" v-if="userData && isShown && !isMobile"
                @mouseenter="hoverPopover"
                @mouseleave="leavePopover"
                :style="caretPosition ?
                {
                    top: `${ caretPosition.top ? caretPosition.top + 'px' : ''}`,
                    right: `${ caretPosition.right ? caretPosition.right + 'px' : ''}`,
                    bottom: `${ caretPosition.bottom ? caretPosition.bottom + 'px' : ''}`,
                    left: `${ caretPosition.left ? caretPosition.left + 'px' : ''}`,
                } : {}">
                    <div class="header-profile m-0 shadow-popover dark:shadow-dark-popover">
                        <div class="relative overflow-hidden rounded-none md:rounded-t-base pb-[38.5%]">
                            <div class="absolute inset-0 h-full">
                                <div class="bg-cover bg-center bg-no-repeat w-full h-full" :style="{ backgroundImage: `url(${userData.cover})`}"></div>
                            </div>
                        </div>
                        <div class="relative inline-block ms-base-2 -mt-14 p-1 rounded-full" :class="(!userData.story_id || !authenticated) ? 'bg-white dark:bg-slate-800' : 'bg-gradient-story cursor-pointer'">
                            <Avatar :user="userData" :size="80" :border="false" :active-popover="false" />
                        </div>
                        <div class="px-base-2 pb-base-2">
                            <div class="flex justify-between items-start gap-5">
                                <div class="w-full">
                                    <UserName :user="userData" :active-popover="false" class="text-base-lg" />
                                    <div class="header-profile-username text-base-sm text-sub-color mb-1 dark:text-slate-400 break-word">{{mentionChar + userData.user_name}}</div>
                                    <div v-if="userData.check_privacy" class="header-profile-count text-base-sm mb-base-1">
                                        <span v-if="userData.show_following" class="inline-block">{{ $filters.numberShortener(userData.following_count, $t('[number] Following'), $t('[number] Following')) }}</span> <span v-if="userData.show_following && userData.show_follower">&middot;</span> <span v-if="userData.show_follower" class="inline-block">{{ $filters.numberShortener(userData.follower_count, $t('[number] Follower'), $t('[number] Followers')) }}</span>
                                    </div>
                                </div>
                            </div>
                            <template v-if="userData.privacy">
                                <template v-if="!userData.check_privacy">
                                    <div class="text-base-sm mb-2">
                                        <template v-if="isPrivacyFollower(userData.privacy)">
                                            {{$t('This Account is Private. Follow to see their posts')}}
                                        </template>
                                        <template v-if="isPrivacyOnlyme(userData.privacy)">
                                            {{$t('This Account is Private')}}
                                        </template>
                                    </div>
                                </template>	
                            </template>  
                            <div v-if="userData.privacy && user.id !== userData.id" class="flex gap-base-2 w-full">
                                <template v-if="userData.can_send_message || userData.chat_room_id">
                                    <MessageButton :user_id="userData.id" class="flex-1"/> 
                                </template>
                                <template v-if="userData.can_follow">
                                    <BaseButton v-if="userData.is_followed" @click="unFollowUser(userData.id)" type="outlined" class="flex-1">{{$t('Unfollow')}}</BaseButton>
                                    <BaseButton v-else @click="followUser(userData.id)" type="outlined" class="flex-1">{{$t('Follow')}}</BaseButton>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
<script>
import { mapState, mapActions } from 'pinia'
import { getUserProfileInfo } from '@/api/user'
import { toggleFollowUser } from '@/api/follow'
import { useAuthStore } from '@/store/auth';
import { useActionStore } from '@/store/action';
import { useAppStore } from '@/store/app';
import Constant from '@/utility/constant';
import BaseButton from '@/components/inputs/BaseButton.vue';
import MessageButton from '@/components/utilities/MessageButton.vue';
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'

export default {
    components: { BaseButton, MessageButton, Avatar, UserName },
    data(){
        return{
            mentionChar: Constant.MENTION,
            userData: window._.clone(this.userProps),
            isShown: false,
			caretPosition: {
				top: null,
				right: null,
				bottom: null,
				left: null
			},
            hoverPopoverItemStatus: false,
            hoverPopoverStatus: false,        
        }
    },
    props: {
        userProps: {
            type: Object,
            default: null
        },
        activePopover: {
            type: Boolean,
            default: true
        },
    },
    computed: {
		...mapState(useAuthStore, ['user', 'authenticated']),
        ...mapState(useActionStore, ['userAction']),
        ...mapState(useAppStore, ['isMobile', 'config'])
	},
    watch: {
        userAction(){
            if(this.userData.id === this.userAction.userId){
                if(this.userAction.status === 'follow'){
                    this.userData.is_followed = true
                }else if(this.userAction.status === 'unfollow'){
                    this.userData.is_followed = false
                }
            } 
        },
        async isShown(){
            if (this.isShown) {
                const response = await getUserProfileInfo(this.userData.user_name)
                this.userData = response
            }
        }
    },
    methods:{
        ...mapActions(useActionStore, ['updateFollowStatus']),
        async followUser(userId) {
            if(this.authenticated){
				try {
					await toggleFollowUser({
						id: userId,
						action: "follow"
					});
                    this.userData.check_privacy = true
					if (this.userData.chat_privacy == Constant.CHAT_PRIVACY_FOLLOWER) {
						this.userData.can_send_message = true
					}

                    this.updateFollowStatus({userId: userId, status: 'follow'})
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
                if (this.userData.privacy == Constant.USER_PRIVACY_FOLLOWER) {
					this.userData.check_privacy = false
				}
				if (this.userData.chat_privacy == Constant.CHAT_PRIVACY_FOLLOWER) {
						this.userData.can_send_message = false
				}
                this.updateFollowStatus({userId: userId, status: 'unfollow'})
            }
            catch (error) {
                this.showError(error.error)
            }
        },
        hoverPopoverItem() {
            if(this.activePopover){
                this.hoverPopoverItemStatus = true
                setTimeout(() => {
                    if(this.hoverPopoverItemStatus){
                        this.updateCaretPosition()
                        this.isShown = !this.isShown;
                    }
                }, 1400);
            }
        },
		leavePopoverItem() {
            if(this.activePopover){              
                this.hoverPopoverItemStatus = false
                setTimeout(() => {         
                    if(!this.hoverPopoverStatus){
                        if (this.isShown) this.isShown = false;
                    }
                }, 400); 
            }
		},
        hoverPopover(){
            if(this.activePopover){              
                this.hoverPopoverStatus = true
            }
        },
        leavePopover(){
            if(this.activePopover){            
                this.hoverPopoverStatus = false
                if (this.isShown) this.isShown = false;       
            }
        },
        updateCaretPosition () {
			const popoverItemRect = this.$refs.popover_item?.getBoundingClientRect()
		
			// set X coordinate emoji box
            if(this.user.rtl){
                if(popoverItemRect?.right > 320){
                    this.caretPosition.right = window.innerWidth - popoverItemRect?.right
                }else{
                    this.caretPosition.left = popoverItemRect?.left
                }
            }else{
                if(window.innerWidth - popoverItemRect?.left > 320){
                    this.caretPosition.left = popoverItemRect?.left
                }else{
                    this.caretPosition.right = window.innerWidth - popoverItemRect?.right
                }
            }

			// set Y coordinate emoji box
			if((window.innerHeight - popoverItemRect?.top) > 290){
				this.caretPosition.top = popoverItemRect?.top + (window.pageYOffset ? window.pageYOffset : 0) + popoverItemRect?.height
			}else{
				this.caretPosition.bottom = window.innerHeight - (window.pageYOffset ? window.pageYOffset : 0) - popoverItemRect?.top + 10
			}
		},
        isPrivacyFollower(privacy){
			return privacy == Constant.USER_PRIVACY_FOLLOWER
		},
		isPrivacyOnlyme(privacy){
			return privacy == Constant.USER_PRIVACY_ONLYME
		},
    }
}
</script>