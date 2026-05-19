<template>
	<form @submit.prevent="saveAccount" class="space-y-3">
		<div class="flex flex-wrap gap-x-5">
			<div class="md:flex-1 md:text-end w-full mb-1 pt-0 md:pt-2"><label>{{ $t('Email') }}</label></div>
			<div class="md:flex-2 w-full">
				<BaseInputText v-model="email" :error="error.email" />
			</div>
		</div>
		<div class="flex flex-wrap gap-x-5">
			<div class="md:flex-1 md:text-end w-full mb-1 pt-0 md:pt-2"><label>{{ $t('Username') }}</label></div>
			<div class="md:flex-2 w-full">
				<BaseInputText v-model="user_name" :error="error.user_name" />
			</div>
		</div>
		<div v-if="config.phoneVerify" class="flex flex-wrap gap-x-5">
			<div class="md:flex-1 md:text-end w-full mb-1"><label>{{ $t('Phone Number') }}</label></div>
			<div class="md:flex-2 w-full">
				<div class="flex items-center gap-base-2">
					<div class="flex-1">{{ phoneNumber }}</div>
					<button class="underline" @click.prevent="handleChangePhone">{{ $t('Change') }}</button>
				</div>
			</div>
		</div>
		<div class="flex flex-wrap gap-x-5">
			<div class="md:flex-1 md:text-end w-full"></div>
			<div class="md:flex-2 w-full">
				<BaseButton fluid>{{ $t('Save') }}</BaseButton>
			</div>
		</div>
	</form>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { useAuthStore } from '@/store/auth'
import { useAppStore } from '@/store/app'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import PasswordModal from '@/components/modals/PasswordModal.vue'
import ChangePhoneEditModal from '@/components/modals/ChangePhoneEditModal.vue'

export default {
	components: { BaseButton, BaseInputText },
	data() {
		return {
			email: null,
			user_name: null,
			phoneNumber: null,
			error: {
				email: null,
				user_name: null,
				phone_number: null
			}
		}
	},
	computed:{
		...mapState(useAuthStore, ['user']),
		...mapState(useAppStore, ['config'])
	},
	watch:{
		user(){
			this.handleInitData()
		}
	},
	mounted() {
		if (this.user?.is_page) {
			this.$router.replace({ name: 'setting_subscriptions' })
		}
		this.handleInitData()
	},
	methods: {
		...mapActions(useAuthStore, ['storeAccountSettings']),
		handleInitData(){
			this.email = this.user.email
			this.user_name = this.user.user_name
			this.phoneNumber = this.user.phone_number
		},
		async saveAccount() {
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
								await this.storeAccountSettings({
									email: this.email,
									password: data.password,
									user_name: this.user_name
								})
								this.showSuccess(this.$t('Your changes have been saved.'))
								this.resetErrors(this.error)
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
		handleChangePhone(){
			this.$dialog.open(ChangePhoneEditModal, {
				props:{
					header: this.$t('Update Phone Number'),
					modal: true,
					dismissableMask: true,
					draggable: false,
					class: 'enter-phone-modal'
				}
			});
		}
	}
}
</script>