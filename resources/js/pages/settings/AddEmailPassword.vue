<template>
	<form @submit.prevent="saveAccount" class="space-y-3">
		<div class="flex flex-wrap gap-x-5">
			<div class="md:flex-1 md:text-end w-full mb-1 pt-0 md:pt-2"><label>{{ $t('Email') }}</label></div>
			<div class="md:flex-2 w-full">
				<BaseInputText v-model="email" class="w-full" :error="error.email" />
			</div>
		</div>
		<div class="flex flex-wrap gap-x-5">
			<div class="md:flex-1 md:text-end w-full mb-1 pt-0 md:pt-2"><label>{{ $t('Password') }}</label></div>
			<div class="md:flex-2 w-full">
				<BasePassword v-model="password" :error="error.password" />
			</div>
		</div>
		<div class="flex flex-wrap gap-x-5">
			<div class="md:flex-1 md:text-end w-full mb-1 pt-0 md:pt-2"><label>{{ $t('Confirm Password') }}</label></div>
			<div class="md:flex-2 w-full">
				<BasePassword v-model="password_confirmed" :error="error.password_confirmed" />
			</div>
		</div>
		<div class="flex flex-wrap gap-x-5">
			<div class="md:flex-1 md:text-end w-full"></div>
			<div class="md:flex-2 w-full">
				<BaseButton :loading="loadingSave" fluid>{{ $t('Save') }}</BaseButton>
			</div>
		</div>
	</form>
</template>

<script>
import { mapState } from 'pinia'
import { useAuthStore } from '../../store/auth'
import { addVerifyEmailPassword } from '@/api/user'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import BasePassword from '@/components/inputs/BasePassword.vue'
import VerificationCodeModal from '@/components/modals/VerificationCodeModal.vue'
const authStore = useAuthStore()

export default {
	components: { BaseButton, BaseInputText, BasePassword },
	data() {
		return {
			loadingSave: false,
			email: null,
			password: null,
			password_confirmed: null,
			error: {
				email: null,
				password: null,
				password_confirmed: null
			}
		}
	},
	computed: {
		...mapState(useAuthStore, ['user'])
	},
	methods: {
		async saveAccount() {
			this.loadingSave = true
			try {
				await addVerifyEmailPassword(this.email, this.password, this.password_confirmed)
				this.$dialog.open(VerificationCodeModal, {
					data: {
						email: this.email,
						password: this.password,
						password_confirmed: this.password_confirmed
					},
					props: {
						header: this.$t('Enter verification code'),
						class: 'verification-modal',
						modal: true,
						dismissableMask: true,
						draggable: false
					},
					onClose: async() => {
						await authStore.me()
						this.$router.push({ name: 'setting_account'})
					}
				})
			} catch (error) {
				this.handleApiErrors(this.error, error)
			} finally {
				this.loadingSave = false
			}
		}
	}
}
</script>