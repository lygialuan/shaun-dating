<template>
    <div :class="owner ? 'bg-[#FFF9F0] relative h-10 flex items-center dark:bg-dark-body' : ''">
        <div class="absolute top-3 start-4 z-[1001]">
            <button @click="closeModal">
                <BaseIcon name="arrow_left" size="20" />
            </button>
        </div>
        <div class="absolute top-3 end-4 z-[1001]" v-if="!owner">
            <button @click="openFilterModal()" class="header-icons-list-item inline-block text-main-color dark:text-white relative space-x-2">
                <BaseIcon name="filter_data" class="align-middle" :size="15"></BaseIcon>
                <span class="text-sm">{{ $t("Filter") }}</span>
            </button>
        </div>
    </div>
    <div class="overflow-y-auto no-scrollbar scroll-smooth dark:bg-dark-body">
        <div class="bg-[#FFF9F0] flex items-center justify-center px-3 sm:px-4 dark:bg-dark-body" :class="!owner ? 'min-h-[100svh]' : 'min-h-[90svh]'">
            <div class="relative w-full max-w-[420px] sm:max-w-[500px] md:max-w-[550px] h-[clamp(480px,90dvh,835px)] mt-6 md:mt-0">
                <Loading v-if="activeResetUsers"/> 
                <template v-else>
                    <div v-if="userInfo" :key="userInfo.id + '-cardTop'" :style="swipeStyle" class="absolute inset-0 z-20 transition-transform duration-500 ease-out will-change-transform transform-gpu origin-bottom">
                        <ProfileContent :user="userInfo" :owner="owner"/>
                    </div>
                    <div v-else>
                       <div v-if="users[1]" :key="users[1].id + '-cardBot'" class="absolute inset-0 z-10 h-full overflow-hidden transform-gpu transition-all duration-500 ease-out origin-bottom"
                            :class="{ 'scale-90 translate-y-6 opacity-0': !isAnimating, 'scale-100 translate-y-0 opacity-100': isAnimating }">
                            <ProfileContent :user="users[1]" class="h-full overflow-hidden"/>
                        </div>
                        <div v-if="users[0]" :key="users[0].id + '-cardTop'" :style="swipeStyle" class="absolute inset-0 z-20 transition-transform duration-500 ease-out will-change-transform transform-gpu origin-bottom">
                            <div class="h-full overflow-y-auto no-scrollbar scroll-smooth">
                                <ProfileContent :user="users[0]" :owner="owner"/>
                                <div v-if="!owner && user.id">
                                    <div class="absolute bottom-0 left-0 right-0 h-40 z-30 pointer-events-none bg-gradient-to-t from-[#FFF9F0] via-[#FFF9F0]/10 to-transparent dark:from-dark-body dark:via-dark-body/10"/>
                                    <div v-if="users[0]" class="absolute left-1/2 -translate-x-1/2 bottom-0 flex gap-4 z-30">
                                        <button v-if="!users[0].check_swipe && !hiddenSwipe" @click="swipe('dislike')" class="w-14 h-14 md:w-16 md:h-16 rounded-full bg-[#0A014F] dark:bg-gradient-to-br dark:from-[#F09819] dark:to-[#EDDE5D] shadow text-red-500 dark:text-black">
                                            <BaseIcon name="close" size="35" />
                                        </button>
                                        <button v-if="!users[0].check_swipe && !hiddenSwipe" @click="swipe('like')" class="w-14 h-14 md:w-16 md:h-16 rounded-full bg-[#0A014F] dark:bg-gradient-to-br dark:from-[#F09819] dark:to-[#EDDE5D] flex items-center justify-center animate-heart-glow">
                                            <BaseIcon name="heart" size="35" class="block dark:hidden"/>
                                            <BaseIcon name="heart_black" size="35" class="hidden dark:block"/>
                                        </button>
                                        <button v-if="users[0].matched && users[0].chat_room_id" @click="clickChat(users[0].chat_room_id)"  class="w-14 h-14 md:w-16 md:h-16 rounded-full bg-[#0A014F] dark:bg-gradient-to-br dark:from-[#F09819] dark:to-[#EDDE5D] flex items-center justify-center">
                                            <BaseIcon name="message_dating" size="35" class="block dark:hidden"/>
                                            <BaseIcon name="message_dating_black" size="35" class="hidden dark:block"/>
                                        </button>
                                        <button @click="openGiftModal(users[0])" v-if="users[0].can_use_gift" class="w-14 h-14 md:w-16 md:h-16 rounded-full bg-[#0A014F] dark:bg-gradient-to-br dark:from-[#F09819] dark:to-[#EDDE5D] flex items-center justify-center">
                                            <BaseIcon name="gift_color" size="35" class="block dark:hidden"/>
                                            <BaseIcon name="gift_black" size="35" class="hidden dark:block"/>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="!users[0]">
                        <EmptyDataUsersPageExplore/>
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>

<script>
import { checkPopupBodyClass } from '@/utility/index'
import { mapState, mapActions } from 'pinia'
import { useAppStore } from '@/store/app';
import { useUserStore } from '@/store/user'
import { useAuthStore } from '@/store/auth';
import { useDatingStore } from '@/store/dating'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import ProfileContent from '@/components/user/ProfileContent.vue'
import DatingFilterModal from '@/components/modals/DatingFilterModal.vue';
import Loading from '@/components/utilities/Loading.vue'
import SwipeMatchModal from '@/components/modals/SwipeMatchModal.vue';
import EmptyDataUsersPageExplore from '@/components/user/EmptyDataUsersPageExplore.vue'
import GiftModal from '@/components/modals/GiftModal.vue';

export default {
    components: { BaseIcon, ProfileContent, Loading, EmptyDataUsersPageExplore },
    inject: {
        dialogRef: { default: null }
    },
    data() {
        return {
            swipeDirection: null,
            isAnimating: false,
            owner: false,
            userInfo: null,
            hiddenSwipe: this.dialogRef?.data?.hiddenSwipe ?? false,
            pageProfile: this.dialogRef?.data?.pageProfile ?? false,
            swipeLoading: false
        }
    },
    async mounted() {
        document.body.style.overflow = 'hidden';
        this.owner = this.dialogRef?.data?.user.id === this.user.id
        if(this.owner){
            this.userInfo = this.dialogRef?.data?.user
        }else{
            await this.sortUser(this.dialogRef?.data?.user)
            if(this.user.id){
                await this.saveViewedUser()
            }
        }
    },
    computed: {
		...mapState(useAuthStore, ['user']),
		...mapState(useUserStore, ['users', 'hasNextPage', 'filterParams', 'activeResetUsers']),
        ...mapState(useAppStore, ['isMobile', 'config']),
		...mapState(useDatingStore, ['originAttributes', 'originInterestAttributes']),
        swipeStyle() {
            if (!this.swipeDirection) return { transform: 'translateX(0) rotate(0)' }
            const isDislike   = this.swipeDirection === 'dislike'
            const distance = this.isMobile ? (isDislike ? -120 : 120) : (isDislike ? -70 : 60)
            const rotate   = isDislike ? -30 : 30
            return { transform: `translateX(${distance}vw) rotate(${rotate}deg)`}
        },
    },
    methods: {
		...mapActions(useUserStore, ['loadUsers', 'sortUser', 'updateFilters', 'handleSwipe', 'resetUsers', 'removeUser']),
        closeModal() {
            if(this.pageProfile) return this.$router.push({ 'name': 'home' })
            this.dialogRef.close()
        },
        async swipe(dir) {
            if (this.isAnimating || !this.users.length || this.swipeLoading) return
            this.swipeLoading = true
            
            try {
                if (this.user.permissions['dating.maximum_number_of_right_swipes'] > 0 && dir === 'like' && this.user.total_swipe_right >= this.user.permissions['dating.maximum_number_of_right_swipes']) {
                    this.showPermissionPopup('dating', this.config.messPermissionSwipe)
                    return
                }

                this.isAnimating = true
                this.swipeDirection = dir

                const res = await this.handleSwipe(dir)

                this.removeUser(this.users[0])

                this.isAnimating = false
                this.swipeDirection = null

                if (res.roomId) {
                    this.openSwipeMatchModal(res)
                }

                this.saveViewedUser()

                this.user.total_swipe_right++
            } catch (error) {
                this.showError(error.error)
            } finally {
                this.swipeLoading = false
            }
        },
        openFilterModal() {
            this.$dialog.open(DatingFilterModal, {
                data: {
                    filterParams: this.filterParams,
                    ageRange: {
                        min: 18,
                        max: 80
                    },
					originAttributes: this.originAttributes,
                    originInterestAttributes: this.originInterestAttributes,
                },
                props: {
					class: 'comment-report-modal p-dialog-sm p-dialog-no-header-title',
					modal: true,
					draggable: false,
					showHeader: false,
                },
                onClose: (options) => {
                    checkPopupBodyClass();
                    const data = options.data;
                    if (data) {
						this.modifyUrl(data)
                    }
                }
            })
        },
		makeQuery(data) {
			let query = {}
			if(data.age.min){
				query.ageMin = data.age.min
			}
			if(data.age.max){
				query.ageMax = data.age.max
			}
			if(data.location){
				query.locationText = data.location[0]?.name
				query.locationId = data.location[0]?.id
			}
			if(data.gender){
				query.gender = data.gender
			}
			if(data.verifiedProfiles){
				query.verifiedProfiles = data.verifiedProfiles
			}
			if(data.isAdvancedFilter){
				if (data.attributeValues.length > 0) {
					query.attributeValues = data.attributeValues;
				}
				if (data.interestAttributeValues.length > 0) {
					query.interestAttributeValues = data.interestAttributeValues;
				}
				query.isAdvancedFilter = data.isAdvancedFilter;
			}
			return query
		},
		modifyUrl(data) {
			this.updateFilters(data)
        },
        openSwipeMatchModal(data) {
            this.$dialog.open(SwipeMatchModal, {
                data: { 
                    roomId: data.roomId,
                    user: data.target,
                    userInfo: this.user
                },
                props: {
					class: 'comment-report-modal p-dialog-sm p-dialog-no-header-title',
					modal: true,
					draggable: false,
					showHeader: false,
                },
            })
        },
        async saveViewedUser() {
            await this.handleSwipe('viewed') 
        },
        openGiftModal(user){
            this.$dialog.open(GiftModal, {
                data: { 
                    user: user
                },
                props: {
					class: 'comment-report-modal p-dialog-sm p-dialog-no-header-title',
					modal: true,
					draggable: false,
					showHeader: false,
                },
                onClose: (options) => {
                    if(options.data === true){
                        this.dialogRef.close()
                    }
                    if (options.data?.updatedGift) {
                        const user = this.users.find(u => u.id === options.data.userId)
                        if (user) user.total_gift_received++
                    }
                }
            })
        },
        clickChat(roomId) {
			let permission = 'chat.allow'
			if(this.checkPermission(permission)){
                this.$router.push({name: 'chat', params: { 'room_id': roomId }});
			}
            if(this.$router.currentRoute.value.name == 'chat'){
                this.dialogRef.close()
            }
		},
    }
}
</script>