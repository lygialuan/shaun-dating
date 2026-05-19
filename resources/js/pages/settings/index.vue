<template>
	<div v-if="isMobile" class="main-content-section">
		<div v-if="!currentTab" class="flex-1 lg:border-r border-divider dark:border-white/10">
			<div class="settings-list">
				<router-link
                    v-for="item in visibleSettingsList"
                    :key="item.name"
                    class="main-content-menu-item settings-list-item flex justify-between items-center py-base-2 text-main-color dark:text-white"
                    :to="{ name: item.name }"
                >
                    {{ item.label }}
                    <BaseIcon name="caret_right" size="16" />
                </router-link>
				<div v-if="user.can_delete" class="mt-5">
					<BaseButton type="danger-outlined" @click="deleteUser" fluid>{{$t('Delete Account')}}</BaseButton>
				</div>
			</div>
		</div>
		<div v-if="currentTab" class="flex-2 min-w-0">
			<router-link :to="{ name: 'setting_index'}" class="flex items-center gap-3 mb-5 text-inherit">
				<BaseIcon name="arrow_left" class="align-middle" />
				<h2 class="text-2xl capitalize font-bold font-workSans">{{ settingTitle }}</h2>
			</router-link>
			<router-view></router-view>
		</div>
	</div>
	<div v-else class="main-content-section p-0">
		<div class="flex">
			<div class="main-content-section-left flex-1 py-4 md:border-e border-divider dark:border-white/10 rounded-s-base-lg">
				<div class="px-4 pb-4">
					<div class="main-content-section-header-title">{{ $t('Settings') }}</div>
				</div>
				<div class="settings-list">
					<router-link
						v-for="item in visibleSettingsList"
						:key="item.name"
						class="main-content-menu-item settings-list-item flex justify-between items-center px-4 py-base-2 text-main-color dark:text-white"
						:class="{'router-link-exact-active': isActive(item.name)}"
						:to="{ name: item.name }"
					>
						{{ item.label }}
					</router-link>
					<div v-if="user.can_delete" class="px-4 mt-4">
						<BaseButton type="danger-outlined" class="btn-block" @click="deleteUser">{{ user.is_page ? $t('Delete User') : $t('Delete Account')}}</BaseButton>
					</div>
				</div>
			</div>
			<div class="main-content-section-right flex-2 min-w-0">
				<div class="border-b border-divider p-4 dark:border-white/10">
					<div class="main-content-section-header-title">{{ settingTitle }}</div>
				</div>
				<div class="p-4">
					<router-view></router-view>
				</div>
			</div>
		</div>
	</div>
</template>
<script>
import { mapState } from 'pinia';
import { deleteUserAccount } from '@/api/user'
import { deletePageAccount } from '@/api/page'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import PasswordModal from '@/components/modals/PasswordModal.vue'
import { useAppStore } from '@/store/app';
import { useAuthStore } from '@/store/auth';


export default {
    components: { BaseIcon, BaseButton },
	props: ["tab"],
	data(){
		return{
			currentTab: this.$route.path.split("/")[2],
			error: {
				password: null
			}
		}
	},
	computed: {
		...mapState(useAppStore, ['isMobile', 'config']),
		...mapState(useAuthStore, ['user']),
		settingsList() {
            const baseSettings = [
                {
                    name: this.isMobile ? 'setting_account' : 'setting_index',
                    label: this.user.is_page ? this.$t('General Settings') : this.$t('Account'),
					isShow: !this.user.is_page && this.user.has_email
                },
                { name: 'setting_subscriptions', label: this.$t('Subscriptions') },
				{ name: 'setting_two_factor', label: this.$t('Two-factor authentication'), isShow: !this.user.is_page && this.config.two_factor.enable },
				{ name: 'setting_password', label: this.$t('Change password'), isShow: !this.user.is_page && this.user.has_email },
                { name: 'setting_add_email_password', label: this.$t('Add email and password'), isShow: !this.user.is_page && !this.user.has_email},
				{ name: 'setting_privacy', label: this.$t('Privacy') },
				{ name: 'setting_email', label: this.$t('Email Notifications'), isShow: !this.user.is_page},
                { name: 'setting_notifications', label: this.$t('Push Notifications') },
                { name: 'setting_display', label: this.$t('Display') },
                { name: 'setting_download', label: this.$t('Download your Data') },
				{ name: 'setting_pwa', label: this.$t('PWA Settings'), isShow: this.showPwa()}
            ];
            return baseSettings;
        },
		visibleSettingsList() {
            return this.settingsList.filter(setting => setting.isShow || typeof(setting.isShow) == 'undefined');
        },
		settingTitle(){
			const titles = {
                account: this.user.is_page ? this.$t('General Settings') : this.$t('Account'),
                password: this.$t('Change password'),
                privacy: this.$t('Privacy'),
                email: this.$t('Email'),
                notifications: this.$t('Notifications'),
                display: this.$t('Display'),
                download: this.$t('Download'),
                add_email_password: this.$t('Add email and password'),
                subscriptions: this.$t('Subscriptions'),
				pwa: this.$t('PWA Settings')
            };
            return titles[this.currentTab] || this.$t('Account');
		}
	},
	methods:{
		async deleteUser(){
			const passwordDialog = this.$dialog.open(PasswordModal, {
				props: {
					header: this.$t('Enter Password'),
					class: 'password-modal',
					modal: true,
					dismissableMask: true,
					draggable: false
				},
				emits: {
                    onConfirm: async (data) => {
                        if (data.password) {
                            try {
								if (this.user.is_page) {
									await deletePageAccount({
										password: data.password
									})
								} else {
									await deleteUserAccount({
										password: data.password
									})
									useAuthStore().remove()
								}							
								window.location.href = window.siteConfig.siteUrl;
								passwordDialog.close()
							} catch (error) {
								this.handleApiErrors(this.error, error)
								passwordDialog.close()
							}
                        }
                    }
                }
			})
		},
		isActive(routeName){
            return ['setting_creator_dashboard', 'setting_subscriber_detail'].includes(this.$router.currentRoute.value.name) && routeName === 'setting_creator_dashboard' || 
			['setting_subscription_detail'].includes(this.$router.currentRoute.value.name) && routeName === 'setting_subscriptions'
        },
		showPwa() {
			return this.config.pwa.enable && ("Notification" in window)
		}
	}
}
</script>