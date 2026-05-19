<template>
	<WidgetContainer class="max-w-sm mx-auto rounded-base-lg">
        <template v-slot:title> {{ $t('Enter verification code') }} </template>
        <template v-slot:body>
            <div class="space-y-3">
                <p>{{$t('Please check your email for a message with your code.')}}</p>
                <BaseInputOtp v-model="code" />
                <p class="break-words">{{$t('We sent your code to')}}:&nbsp;{{ user.email }}</p>	
                <BaseButton :loading="loading" @click="checkVerifyCode()" fluid>{{$t('Continue')}}</BaseButton>
                <BaseButton :loading="loading_resend" type="transparent" @click="resendCode()" fluid>{{$t('Resend code')}}</BaseButton>
                <div class="text-center">{{$t('Switch account?')}}&nbsp;<button class="text-primary-color dark:text-dark-primary-color" @click="logout()">{{$t('Login')}}</button></div>
            </div>
        </template>
	</WidgetContainer>
</template>
<script>
import { checkEmailVerify, sendEmailVerify } from '@/api/user'
import { mapState } from 'pinia'
import { useAuthStore } from '@/store/auth';
import BaseInputOtp from '@/components/inputs/BaseInputOtp.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import WidgetContainer from '@/components/article/WidgetContainer.vue'

export default {
	components:{ BaseInputOtp, BaseButton, WidgetContainer },
    data(){
		return{
            code: null,
            loading: false,
			loading_resend: false
		}
	},
    computed: {
        ...mapState(useAuthStore, ['user']),
    },
	methods: {
		async checkVerifyCode(){
            this.loading = true
            try {
                await checkEmailVerify(this.code)
                this.loading = false
                if (! this.user.already_setup_login) {
                    this.$router.push({'name' : 'first_login'})
                } else {
                    this.$router.push({'name' : 'home'})
                }
                
            } catch (error) {                
                this.loading = false
                this.showError(error.error)
            }
        },
        async resendCode() {
            this.loading_resend = true
            try {
                await sendEmailVerify()
                this.loading_resend = false             
                this.showSuccess(this.$t('A verification code has been sent to your email account.'))   
            } catch (error) {
                this.loading_resend = false
                this.showError(error.error)
            }
        },
        async logout() {
            try {
                await useAuthStore().logout();
                window.location.href = `${window.siteConfig.siteUrl}/login`;
            } catch (error) {
                this.showError(error.error)
            }
        }
	}
}
</script>