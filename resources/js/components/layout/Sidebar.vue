<template>
    <div class="sidebar-user-menu inline-flex flex-col p-4 bg-[#FFFBF3] text-main-color max-w-[320px] sm:max-w-[265.5px] w-full overflow-auto lg:sticky top-0 start-0 bottom-0 lg:h-screen lg:self-start dark:bg-dark-body dark:text-white" :class="{'show': isOpenSidebar}">
        <BaseIcon name="arrow_left" class="absolute top-8 end-5 rtl:rotate-180 block sm:hidden" @click="setIsOpenSidebar(false)" />
        <div class="sidebar-user-menu-box bg-[#FFFBF3] dark:bg-dark-body dark:border-white/10">
            <div class="flex items-center gap-4 p-2">
                <Avatar :user="user" :activePopover="false" :size="50" :show-progress-badge="true" :progress="user.profile_completion_percent"/>
                <div class="flex-1 min-w-0">
                    <UserName :user="user" :activePopover="false" class="sidebar-user-menu-name"/>
                    <span @click="handleOpenProfile(user)" class="max-w-[100px] sidebar-user-menu-sub-text inline-block text-xs text-sub-color dark:text-dark-text-base truncate cursor-pointer">{{mentionChar + user.user_name}}</span>  
                    <div v-if="config.wallet.enable" class="text-xs"><router-link :to="{name: 'wallet'}" class="sidebar-user-menu-link dark:text-dark-text-base">{{ exchangeTokenCurrency(user.wallet_balance) }}</router-link></div>
                    <div>
                        <a v-if="user.is_moderator" :href="user.admin_link" target="_blank" class="flex-1 sidebar-user-menu-link text-xs">{{$t('Go to admin panel')}}</a>
                    </div>
                </div>
                <div v-if="user.can_show_switch_page">
                    <button @click="handleSwitchPage()">
                        <BaseIcon name="user_switch" size="20" class="sidebar-user-menu-icon mt-6"/>
                    </button>
                </div>
            </div>
        </div>
        <div v-if="config.membership.enable && !user.is_moderator" class="mt-2 pt-2 pb-2 border-y border-gray-200 flex flex-row items-center">
            <div class="flex-1">
                <div class="text-sm dark:text-dark-text-base">{{ user.membership_package_name ? $t('Current Package') : $t('Current Package Free') }}</div>
                <div class="font-semibold mb-1 text-sm">{{ user.membership_package_name }}</div>
                <BaseButton :to="{name: 'membership'}" size="xs">{{ $t('Upgrade') }}</BaseButton>
            </div>
        </div>
        <div class="sidebar-main-menu flex-1 pb-5">
            <SidebarMenu />
        </div>
        <FooterSite class="border-t border-divider"/>
    </div>
</template>

<script>
import { mapState, mapActions} from 'pinia'
import { useAuthStore } from '@/store/auth'
import { useAppStore } from '@/store/app'
import { useUtilitiesStore } from '@/store/utilities'
import { loginBack } from '@/api/page'
import Constant from '@/utility/constant'
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import SwitchPagesModal from '@/components/modals/SwitchPagesModal.vue'
import SidebarMenu from '@/components/sidebar/index.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import FooterSite from '@/components/layout/FooterSite.vue';

export default {
    components: {
        UserName,
        BaseIcon,
        Avatar,
        SidebarMenu,
        BaseButton,
        FooterSite,
    },
    data(){
        return{
            mentionChar: Constant.MENTION
        }
    },
    computed:{
        ...mapState(useAuthStore, ['user']),
        ...mapState(useAppStore, ['config', 'isOpenSidebar', 'menus']),
        createItemClass(){
            return 'rounded hover:bg-gray-100 dark:hover:bg-gray-600'
        }
    },
    methods: {
        ...mapActions(useUtilitiesStore, ['setSelectedPage']),
        ...mapActions(useAppStore, ['setIsOpenSidebar']),
        openStatusBox(){
			this.showPostStatusBox()
		},
        handleSwitchPage(){
            this.$dialog.open(SwitchPagesModal, {
                props:{
					header: this.$t('Switch Profile'),
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                }
            });
        },
        handleClickLoginBack(){
            try {
                this.setSelectedPage(this.user.parent)
                setTimeout(async() => {
                    await loginBack()
                    window.location.href = "/"
                }, 1500);
            } catch (error) {
                this.showError(error.error)
            }
        },
        handleOpenProfile(user) {
            if (document.querySelector('.p-dialog-profile')) return
            this.openProfile({ user })
        }
    }
}
</script>