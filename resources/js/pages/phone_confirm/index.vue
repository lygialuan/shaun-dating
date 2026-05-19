<template>
	<WidgetContainer class="max-w-sm mx-auto rounded-base-lg">
        <template v-slot:title> {{ $t('Enter verification code') }} </template>
        <template v-slot:body>
            <div class="space-y-3">
                <p>{{$t('We just sent a code to your phone number')}}&nbsp;{{ phoneNumber }}.&nbsp;{{ $t('Code will expire in 2 minutes.') }}</p>	
                <BaseInputOtp v-model="code" />
                <div v-if="enableWidget" class="text-center">
                    <CloudFlareTurnstile v-model="turnstileToken" />
                </div>
                <div class="flex flex-col gap-y-3">
                    <BaseButton :loading="loadingSend" :disabled="!isVerified()" @click="handleCheckPhoneVerify()">{{$t('Continue')}}</BaseButton>
                    <BaseButton :loading="loadingResend" :disabled="!isVerified()" type="secondary" @click="handleSendPhoneVerify(false)">{{$t('Resend code')}}</BaseButton>
                    <BaseButton :disabled="!isVerified()" type="secondary" @click="reEnterPhoneNumber()">{{$t('Re-enter phone number')}}</BaseButton>
                </div>
                <div class="text-center">{{$t('Switch account?')}}&nbsp;<button class="text-primary-color dark:text-dark-primary-color" @click="logout()">{{$t('Logout')}}</button></div>
            </div>
        </template>
	</WidgetContainer>
</template>
<script>
import { sendPhoneVerify, checkPhoneVerify } from '@/api/user'
import { mapState } from 'pinia'
import { useAuthStore } from '@/store/auth'
import { useAppStore } from '@/store/app'
import { useCaptcha } from '@/hooks/useCaptcha'
import BaseInputOtp from '@/components/inputs/BaseInputOtp.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import ChangePhoneVerifyModal from '@/components/modals/ChangePhoneVerifyModal.vue'
import CloudFlareTurnstile from '@/components/utilities/CloudFlareTurnstile.vue'
import WidgetContainer from '@/components/article/WidgetContainer.vue'

export default {
	components:{ 
        BaseInputOtp, 
        BaseButton,
        CloudFlareTurnstile,
        WidgetContainer
    },
    data(){
        const appStore = useAppStore()
        const captcha = useCaptcha(appStore.config.sendPhoneEnableRecapcha, this.enableRecapcha, this.enableTurnstile)
		return{
            token: null,
            code: null,
            loadingSend: false,
			loadingResend: false,
            phoneNumber: null,
            ...captcha
		}
	},
    computed: {
        ...mapState(useAuthStore, ['user']),
        ...mapState(useAppStore, ['config']),
    },
    mounted(){
        this.phoneNumber = this.user.phone_number
        this.handleSendPhoneVerify()

        setTimeout(() => {
            this.loadRecaptcha(this.$recaptchaInstance)
        }, 2000);
    },
    unmounted(){
        this.unloadRecaptcha(this.$recaptchaInstance)
    },
	methods: {
        async handleSendPhoneVerify(isFirst = true){
            if(!isFirst){
                this.loadingResend = true
            }
            try {
                this.token = await this.getCaptchaToken(this.$recaptcha, this.turnstileToken, "send_phone")
                await sendPhoneVerify({
                    token: this.token
                })
                if(!isFirst){
                    this.showSuccess(this.$t('A new code has been sent to your phone number.'))
                }
            } catch (error) {
                if(!isFirst){
                    this.showError(error.error)
                }
            } finally{
                if(!isFirst){
                    this.loadingResend = false
                }
            }
        },
        reEnterPhoneNumber(){
            this.$dialog.open(ChangePhoneVerifyModal, {
                props:{
					header: this.$t('Enter new phone number'),
                    modal: true,
                    dismissableMask: true,
                    draggable: false,
                    class: 'enter-phone-modal'
                },
				onClose: (options) => {
                    if(options.data){
                        this.phoneNumber = options.data.phone_number
                    }
                }
            });
        },
		async handleCheckPhoneVerify(){
            this.loadingSend = true
            try {
                await checkPhoneVerify(this.code)
                if (! this.user.already_setup_login) {
                    this.$router.push({'name' : 'first_login'})
                } else {
                    this.$router.push({'name' : 'home'})
                }
            } catch (error) {                
                this.showError(error.error)
            } finally {
                this.loadingSend = false
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