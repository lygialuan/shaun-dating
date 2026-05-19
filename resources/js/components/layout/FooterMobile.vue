<template>
    <div class="footer-mobile flex items-center justify-between lg:hidden fixed inset-x-0 bottom-0 bg-[#FFF9F0] px-5 py-4 z-[998] transition-all dark:bg-dark-form-base">
        <div class="footer-mobile-item text-center">
            <router-link :to="{ name: 'home' }" class="text-main-color dark:text-white">
                <BaseIcon name="discover" />            
                <div class="text-xs">{{ $t('Discover') }}</div>
            </router-link> 
        </div>
        <div class="footer-mobile-item text-center">
            <button @click="handleOpenProfile" class="text-main-color dark:text-white relative">
                <BaseIcon name="swipe" />
                <div class="text-xs">{{ $t('Swipe') }}</div>
            </button> 
        </div>
        <div class="footer-mobile-item text-center">
            <router-link :to="{ name: 'matched' }" class="text-main-color dark:text-white">
                <BaseIcon name="heart_not_color" />            
                <div class="text-xs">{{ $t('Matched') }}</div>
            </router-link> 
        </div>
        <div class="footer-mobile-item text-center">
            <button @click="clickChat" class="text-main-color dark:text-white relative" :class="{'router-link-exact-active': $route.name === 'chat'}">
                <BaseIcon name="message" />
                <span v-if="pingChatCount > 0" class="footer-icons-badge absolute -top-1 -right-1 flex items-center justify-center w-[18px] h-[18px] bg-base-red rounded-full text-[10px] text-white">{{pingChatCount > 9 ? '9+' : pingChatCount}}</span>
                <div class="text-xs">{{ $t('Chat') }}</div>
            </button> 
        </div>
        <div class="footer-mobile-item text-center">
            <router-link :to="{ name: 'notifications' }" class="text-main-color dark:text-white">
                <div class="relative inline-block">
                    <BaseIcon name="bell" />
                    <span v-if="pingNotificationCount > 0" class="absolute -top-1 -right-1 flex items-center justify-center min-w-[18px] h-[18px] px-1 bg-base-red rounded-full text-[10px] text-white">{{ pingNotificationCount > 9 ? '9+' : pingNotificationCount }}</span>
                </div>
                <div class="text-xs">{{ $t('Notifications') }}</div>
            </router-link>
        </div>
    </div>
</template>
<script>
import { mapState } from 'pinia';
import { useUtilitiesStore } from '@/store/utilities';
import { useAppStore } from '@/store/app'
import { useAuthStore } from '@/store/auth'
import BaseIcon from '@/components/icons/BaseIcon.vue';

export default {
    name: "FooterMobile",
    components: { 
        BaseIcon,
    },
    computed:{
		...mapState(useUtilitiesStore, ['pingNotificationCount', 'pingChatCount']),
        ...mapState(useAppStore, ['config']),
        ...mapState(useAuthStore, ['user']),
        createItemClass(){
            return 'rounded hover:bg-gray-100 dark:hover:bg-gray-600'
        }
	},
    methods:{
        openStatusBox(){
            this.showPostStatusBox()
		},
        clickChat() {
			let permission = 'chat.allow'
			if(this.checkPermission(permission)){
				this.$router.push({ name: 'chat', force: true})
			}
		},
        handleOpenProfile(user) {
            if (document.querySelector('.p-dialog-profile')) return
            this.openProfile({ user })
        }
    }
}
</script>