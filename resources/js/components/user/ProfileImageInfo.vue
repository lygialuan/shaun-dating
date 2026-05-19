<template>
    <div v-if="userInfo" class="relative aspect-[4/5] w-full h-[90%] md:max-h-[733px] rounded-[14px] overflow-hidden">
        <div v-if="owner" class="absolute top-0 left-0 right-0 h-20 bg-gradient-to-b from-black/50 to-transparent z-10"></div>
        <div v-if="userInfo.photos_verify_approve && userInfo.photos_verify_approve.length > 0">
            <div @click="handleOpenListingModal(currentIndex)" class="cursor-pointer">
                <img :src="userInfo.photos_verify_approve[currentIndex]?.photo" class="absolute inset-0 w-full h-full object-cover"/>
                <div class="absolute top-3 left-3 right-3 flex gap-1 z-10" v-if="userInfo.photos_verify_approve.length > 1" >
                    <div 
                        v-for="(img, index) in userInfo.photos_verify_approve"
                        :key="index"
                        class="h-1 flex-1 rounded-full transition-all duration-300"
                        :class="index === currentIndex ? 'bg-white' : 'bg-white/30'"
                    ></div>
                </div>
            </div>
            <PhotoProfileDatingTheater ref="photoProfileDatingTheater" :photos="userInfo.photos_verify_approve"/>
            <div v-if="currentIndex !== 0" class="absolute left-3 top-1/2 -translate-y-1/2 z-20" @click="prevImage">
                <div class="w-12 h-12 flex items-center justify-center rounded-full bg-[#FFFFFF80] text-black hover:bg-gray-200 transition cursor-pointer">
                    <BaseIcon name="caret_left" size="20"/>
                </div>
            </div>
            <div v-if="userInfo.photos_verify_approve.length > 1 && currentIndex !== userInfo.photos_verify_approve.length-1" class="absolute right-3 top-1/2 -translate-y-1/2 z-20" @click="nextImage">
                <div class="w-12 h-12 flex items-center justify-center rounded-full bg-[#FFFFFF80] text-black hover:bg-gray-200 transition cursor-pointer">
                    <BaseIcon name="caret_right" size="20"/>
                </div>
            </div>
        </div>
        <div v-else>
            <img :src="userInfo.avatar" class="absolute inset-0 w-full h-full object-cover">
        </div>
        <div class="absolute bottom-0 left-0 right-0 h-40 bg-gradient-to-t from-black/50 to-transparent z-10"></div>
        <div v-if="owner" class="absolute top-5 right-3 z-20 text-white flex items-center gap-1 cursor-pointer" @click="openPopupEditPhotos()">
            <BaseIcon name="photo" size="20"/>
            <span>{{ userInfo.photos_verify_approve?.length ?? 0 }}</span>
        </div>
        <div>
            <div class="absolute bottom-6 left-5 right-5 text-white z-10">
                <UserName :user="userInfo" :activePopover="false" class="header-profile-name text-base-lg font-extrabold" :show-age="true" :show-gender="true"/>
                <div class="md:flex items-center gap-2">
                    <div class="truncate font-semibold">
                        {{ mentionChar + userInfo.user_name }}
                    </div>
                    <button class="space-x-1 flex flex-wrap mt-1" @click="openGiftReceived(userInfo)" v-if="userInfo.can_view_my_gift && userInfo.total_gift_received > 0 && config.gift.enable">
                        <BaseIcon name="gift"/>
                        <span>{{ userInfo.total_gift_received }} {{ $t('Gift Received') }}</span>
                    </button>
                </div>
                <div v-if="userInfo.dating_addresses_fulltext && userInfo.can_view_location">
                    <BaseIcon name="location" size="16"/>
                    {{ userInfo.dating_addresses_fulltext }}
                </div>
            </div>
            <div class="absolute bottom-5 right-5 text-white z-10">
                <button @click="openDropdownProfileOptionsMenu" v-if="!owner">
                    <BaseIcon name="more_horiz_outlined"/>
                </button>
                <button @click="openPopupEditBasicInfo" v-else>
                    <BaseIcon name="pencil" size="20"/>
                </button>
            </div>
        </div>
    </div>
</template>
<script>
import { checkPopupBodyClass } from '@/utility/index'
import { mapState } from 'pinia'
import { useAppStore } from '@/store/app'
import ProfileUserMenu from '@/pages/profile/ProfileUserMenu.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import Constant from '@/utility/constant'
import PhotoProfileDatingTheater from '@/components/modals/PhotoProfileDatingTheater.vue';
import UserName from '@/components/user/UserName.vue'
import PhotosConfirm from '@/pages/photos_confirm/index.vue'
import ProfileBasicInfoModal from '@/components/modals/ProfileBasicInfoModal.vue';
import GiftReceivedModal from '@/components/modals/GiftReceivedModal.vue'

export default {
    components: { 
        BaseIcon,
        PhotoProfileDatingTheater,
        UserName
    },
    props: {
        user: {
            type: Object,
            required: true
        },
        owner: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            currentIndex: 0,
            mentionChar: Constant.MENTION,
            userInfo: this.user ?? []
        };
    },
    computed: {
		...mapState(useAppStore, ['config']),
	},
    methods: {
        nextImage() {
            if (this.currentIndex < this.userInfo.photos_verify_approve.length - 1) {
                this.currentIndex++
            }
        },
        prevImage() {
            if (this.currentIndex > 0) {
                this.currentIndex--
            }
        },
        openDropdownProfileOptionsMenu(){
            this.$dialog.open(ProfileUserMenu, {
                data: {
                    userInfo: this.user
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
        handleOpenListingModal(photoIndex) {
			this.$refs.photoProfileDatingTheater.openPhotosTheater(photoIndex)
		},
        openPopupEditPhotos(){
            this.$dialog.open(PhotosConfirm, {
                data: {
                    owner: this.owner,
                    user: this.userInfo
                },
                props:{
                    showHeader: false,
                    class: 'dropdown-menu-modal',
                    modal: true,
                    draggable: false
                },
                onClose: (data) => {
                    if (data?.data?.updatedUser) {
                        this.currentIndex = 0
                        this.userInfo.photos_verify_approve = data.data.updatedUser.photos_verify_approve
                        if(data.data.updatedUser.photos_verify_approve.length === 0 && !this.userInfo.fake_user) this.userInfo.avatar = this.config.avatar_default
                    }
                    checkPopupBodyClass();
                }
            });
        },
        openPopupEditBasicInfo(){
            this.$dialog.open(ProfileBasicInfoModal, {
                data: {
                    user: this.userInfo
                },
                props:{
                    header: this.$t('Basic Info'),
                    class: 'profile-basic-info-modal',
                    modal: true,
                    draggable: false
                },
                onClose: ({ data }) => {
                    if (data) {
                        this.userInfo = {
                            ...this.userInfo,
                            ...data
                        }
                    }
                    checkPopupBodyClass();
                }
            });
        },
        openGiftReceived(userInfo){
            this.$dialog.open(GiftReceivedModal, {
                data: { 
                    userInfo: userInfo
                },
                props:{
                    header: this.$t('Gifts Received'),
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                }
            });
        }
    }
}
</script>