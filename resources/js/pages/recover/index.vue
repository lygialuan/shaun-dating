<template>
	<div class="text-center mb-10">
		<Logo />
	</div>
	<WidgetContainer class="max-w-sm mx-auto rounded-base-lg">
		<template v-slot:title> {{ stepTitle }} </template>
		<template v-slot:body>
			<div v-if="currentStep === 1" class="space-y-3">
				<p>{{$t('Please enter your email address to search for your account.')}}</p>
				<BaseInputText v-model="email" :placeholder="$t('Email')" :error="error.email" left_icon="mail" autocomplete="username" @keyup.enter="nextStep" />
				<BaseButton :loading="loading_send" @click="nextStep()" fluid>{{$t('Continue')}}</BaseButton>
				<div class="text-center">{{$t('Already had an account?')}}&nbsp;<router-link :to="{name: 'login'}">{{$t('Login')}}</router-link></div>
			</div>
			<div v-if="currentStep === 2" class="space-y-3">
				<p>{{$t('Please check your email for a message with your code.')}}</p>
				<BaseInputOtp v-model="code" :error="error.code" @keyup.enter="nextStep" />
				<p>{{$t('We sent your code to')}}:&nbsp;{{ email }}</p>	
				<BaseButton :loading="loading" @click="nextStep()" fluid>{{$t('Continue')}}</BaseButton>
				<BaseButton type="transparent" :loading="loading_send" @click="sendCodeForEmail()" fluid>{{$t('Resend code')}}</BaseButton>
			</div>
			<div v-if="currentStep === 3" class="space-y-3">
				<BasePassword v-model="password" :placeholder="$t('Password')" @keyup.enter="nextStep" :error="error.password"/>
				<BasePassword v-model="password_confirmed" :placeholder="$t('Confirm Password')" @keyup.enter="nextStep" :error="error.password_confirmed"/>
				<BaseButton :loading="loading" @click="nextStep()" fluid>{{$t('Save')}}</BaseButton>
			</div>
			<div v-if="currentStep === 4" class="space-y-3">
				<p>{{$t('Your password was changed')}}</p>
				<BaseButton :to="{ name: 'login' }" fluid>{{$t('Login')}}</BaseButton>
			</div>
		</template>
	</WidgetContainer>
</template>
<script>
import { sendCodeForgotPassword, checkCodeForgotPassword, storeForgotPassword } from '@/api/user'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import BasePassword from '@/components/inputs/BasePassword.vue'
import BaseInputOtp from '@/components/inputs/BaseInputOtp.vue'
import Logo from '@/components/utilities/Logo.vue'
import WidgetContainer from '@/components/article/WidgetContainer.vue'

export default {
	components:{ BaseButton, BaseInputText, BasePassword, BaseInputOtp, Logo, WidgetContainer },
    data(){
		return{
            currentStep: 1,
            email: null,
            code: null,
			password: null,
            password_confirmed: null,
			error: {
				email: null,
				code: null,
				password: null,
				password_confirmed: null
			},
			loading: false,
			loading_send: false
		}
	},
	computed: {
		stepTitle() {
			const titles = [
				this.$t('Forgot password'),
				this.$t('Enter security code'),
				this.$t('Change Password'),
				this.$t('Password Updated')
			]
			return titles[this.currentStep - 1];
		}
	},
	methods: {
		async sendCodeForEmail(){
			if (this.loading_send) {
				return
			}
			this.loading_send = true
			try {
				await sendCodeForgotPassword({
					email: this.email
				})

				if(this.currentStep === 2){
					this.showSuccess(this.$t('Your code has been sent.'))
				}

				this.currentStep = 2
				this.resetErrors(this.error)
			} catch (error) {
				this.handleApiErrors(this.error, error)
			} finally {
				this.loading_send = false
			}
		},
		async checkValidateCode(){
			if (this.loading) {
				return
			}
			this.loading = true
			try {
				await checkCodeForgotPassword({
					email: this.email,
					code: this.code
				})
				this.currentStep = 3
				this.resetErrors(this.error)
			} catch (error) {
				this.handleApiErrors(this.error, error)
			} finally {
				this.loading = false
			}
		},
		async updatePassword(){
			if (this.loading) {
				return
			}
			this.loading = true
			try {
				await storeForgotPassword({
					password: this.password,
					password_confirmed: this.password_confirmed,
					email: this.email,
					code: this.code
				})
				this.currentStep = 4
				this.resetErrors(this.error)
			} catch (error) {
				this.handleApiErrors(this.error, error)
			} finally {
				this.loading = false
			}
		},
		nextStep(){
			if(this.currentStep === 1){
				this.sendCodeForEmail()
			}else if(this.currentStep === 2){
				this.checkValidateCode()
			}else if(this.currentStep === 3){
				this.updatePassword()
			}
		}
	}
}
</script>