<template>
    <form @submit.prevent="changePassword" class="space-y-3">
		<div class="flex flex-wrap gap-x-5"> 
			<div class="md:flex-1 md:text-end w-full mb-1 pt-0 md:pt-2"><label>{{$t('Old Password')}}</label></div>
			<div class="md:flex-2 w-full">
                <BasePassword v-model="passwordData.password" :error="error.password" :left_icon="false" />			
			</div>  
		</div>
		<div class="flex flex-wrap gap-x-5"> 
			<div class="md:flex-1 md:text-end w-full mb-1 pt-0 md:pt-2"><label>{{$t('New Password')}}</label></div>
			<div class="md:flex-2 w-full">
				<BasePassword v-model="passwordData.password_new" :error="error.password_new" :left_icon="false" />	
			</div>  
		</div>
        <div class="flex flex-wrap gap-x-5"> 
			<div class="md:flex-1 md:text-end w-full mb-1 pt-0 md:pt-2"><label>{{$t('Confirm Password')}}</label></div>
			<div class="md:flex-2 w-full">
				<BasePassword v-model="passwordData.password_new_confirmed" :error="error.password_new_confirmed" :left_icon="false" />	
			</div>  
		</div>
		<div class="flex flex-wrap gap-x-5">
			<div class="md:flex-1 md:text-end w-full"></div>
			<div class="md:flex-2 w-full">
				<BaseButton :loading="loading" fluid>{{$t('Save')}}</BaseButton>
			</div>
		</div>
    </form>
</template>

<script>
import { storeChangePassword, removeLoginOtherDevice } from '@/api/user'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BasePassword from '@/components/inputs/BasePassword.vue'

export default {
	components: { BaseButton, BasePassword },
    data(){
		return {
            passwordData: {
				password: '',
				password_new: '',
				password_new_confirmed: ''
			},
			error: {
				password: null,
				password_new: null,
				password_new_confirmed: null			
			},
			loading: false
		}
	},
	methods:{
		async changePassword(){
			if (this.loading) {
				return
			}
			this.loading = true
            try {
                await storeChangePassword(this.passwordData)
                this.showSuccess(this.$t('Your password has been changed.'))
				let password = this.passwordData.password_new
				this.$confirm.require({
					message: this.$t('Do you want to log out of other devices?'),
					header: this.$t('Please confirm'),
					acceptLabel: this.$t('Ok'),
					rejectLabel: this.$t('Cancel'),
					accept: async () => {
						try {
							await removeLoginOtherDevice(password);
							this.showSuccess(this.$t('You are successfully logged out.'))
						}
						catch (error) {
							this.showError(error.error)
						}
					}
				});

                Object.keys(this.passwordData).forEach((key) => this.passwordData[key] = null)
                this.resetErrors(this.error)
            } catch (error) {
                this.handleApiErrors(this.error, error)
            } finally {
				this.loading = false
			}
        }
    }
}
</script>