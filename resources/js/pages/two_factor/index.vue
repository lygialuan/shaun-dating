<template>
    <WidgetContainer class="max-w-md mx-auto rounded-base-lg">
        <template v-slot:title>{{ $t('Two-Factor Authentication') }}</template>
        <template v-slot:body>
            <div class="space-y-base-2">
                <div v-if="loginInfoText">{{ loginInfoText }}</div>
                <BaseInputOtp v-model="code" />
                <div class="flex gap-base-2">
                    <BaseButton v-if="!isAuthApp" :loading="loadingResend" class="flex-1" type="secondary" @click="handleSendLoginCode(false)">{{$t('Resend code')}}</BaseButton>
                    <BaseButton :loading="loadingVerify" class="flex-1" @click="handleVerifyLoginCode()">{{$t('Continue')}}</BaseButton>
                </div>
                <router-link v-if="loginInfoHelpText" :to="{name: 'contact'}" class="inline-block">{{ loginInfoHelpText }}</router-link>
            </div>
        </template>
    </WidgetContainer>
</template>

<script>
import { getLoginCurrent, sendLoginCode, verifyLoginCode } from '@/api/two_factor'
import localData from '@/utility/localData'
import BaseInputOtp from '@/components/inputs/BaseInputOtp.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import WidgetContainer from '@/components/article/WidgetContainer.vue'

export default {
    components: {
        BaseInputOtp,
        BaseButton,
        WidgetContainer
    },
    data(){
        return {
            twoFactorCode: localData.get('two_factor_code', null),
            loginInfo: null,
            code: null,
            loadingResend: false,
            loadingVerify: false
        }
    },
    computed: {
        loginInfoText(){
            switch (this.loginInfo?.provider?.type) {
                case 'mail':
					return this.$filters.textTranslate(this.$t("Your account is protected with two-factor authentication. We've sent you a confirmation code to [1]. Please enter the code below."), {1 : this.loginInfo.params.email});
                case 'sms':
                    return this.$filters.textTranslate(this.$t("Your account is protected with two-factor authentication. We've sent you a confirmation code to [1]. Please enter the code below."), {1: this.loginInfo.params.phone_number});
				case 'auth_app':
                    return this.$filters.textTranslate(this.$t('Open your authentication app and enter the code for [1].'), {1: window.siteConfig.siteName})
                default: 
					return null
            }
        },
        loginInfoHelpText(){
            switch (this.loginInfo?.provider?.type) {
				case 'auth_app':
                    return this.$t("Can't access your authentication app? Contact Support")
                default: 
					return this.$t("Can't get confirmation code? Contact Support")
            }
        },
        isAuthApp(){
            return this.loginInfo?.provider?.type === 'auth_app'
        }
    },
    mounted(){
        this.handleGetLoginCurrent()
        this.handleSendLoginCode()
    },
    methods: {
        async handleGetLoginCurrent(){
            try {
                this.loginInfo = await getLoginCurrent({two_factor_code: this.twoFactorCode})
            } catch (error) {
                this.showError(error.error)
            }
        },
        async handleSendLoginCode(isFirst = true){
            if(!isFirst){
                this.loadingResend = true
            }
            try {
                await sendLoginCode({two_factor_code: this.twoFactorCode})
                if(!isFirst){
                    switch (this.loginInfo?.provider?.type) {
                        case 'mail':
                            this.showSuccess(this.$t('A verification code has been sent to your email account.'))
                            break;
                        case 'sms':
                            this.showSuccess(this.$t('A verification code has been sent to your phone number.'))
                            break;
                    }
                }
            } catch (error) {
                if(!isFirst){
                    this.showError(error.error)
                }
            } finally {
                if(!isFirst){
                    this.loadingResend = false
                }
            }
        },
        async handleVerifyLoginCode(){
            this.loadingVerify = true
            try {
                await verifyLoginCode({
                    two_factor_code: this.twoFactorCode,
                    code: this.code
                })
                const redirect = this.$route.query.redirect
                const target = redirect && atob(redirect).includes(window.siteConfig.siteUrl)
                    ? atob(redirect)
                    : window.siteConfig.siteUrl

                window.location.href = target
                localData.set('authenticated', true);
            } catch (error) {
                this.showError(error.error)
            } finally {
                this.loadingVerify = false
            }
        }
    }
}
</script>