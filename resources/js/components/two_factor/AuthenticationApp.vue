<template>
    <div v-if="qrCode" class="space-y-base-2">
        <div>{{ $t('Scan this QR code with an authenticator app like Authy, 1Password, Microsoft Authenticator or Google Authenticator.') }}</div>
        <img :src="`data:image/png;base64,${qrCode}`" alt="QR Code" class="max-w-xs w-full mx-auto"/>
        <div>{{ $t('Once you have connected your app, enter your most recent 6-digit verification code.') }}</div>
        <BaseInputOtp v-model="code" />
        <div class="flex flex-wrap gap-base-2">
            <BaseButton @click="$emit('back')">{{$t('Back')}}</BaseButton>
            <BaseButton :loading="loadingVerifyCodeApp" @click="handleVerifyCodeApp">{{$t('Next')}}</BaseButton>
        </div>
    </div>
</template>

<script>
import { getCodeApp, verifyCodeApp } from '@/api/two_factor'
import BaseInputOtp from '@/components/inputs/BaseInputOtp.vue'
import BaseButton from '@/components/inputs/BaseButton.vue';

export default {
    props: ['password'],
    components: {
        BaseInputOtp,
        BaseButton
    },
    data(){
        return {
            qrCode: null,
            code: null,
            loadingVerifyCodeApp: false
        }
    },
    mounted() {
        this.handleGetCodeApp()
    },
    methods:{
        async handleGetCodeApp(){
            try {
                const response = await getCodeApp({ password: this.password })
                this.qrCode = response.qr_code
            } catch (error) {
                this.showError(error.error)
            }
        },
        async handleVerifyCodeApp(){
            this.loadingVerifyCodeApp = true
            try {
                await verifyCodeApp({
                    email: this.email,
                    password: this.password,
                    code: this.code
                })
                this.showSuccess(this.$t("You've successfully enable 2FA Authentication"))
                this.$emit('success')
            } catch (error) {
                this.showError(error.error)
            } finally {
                this.loadingVerifyCodeApp = false
            }
        }
    }
}
</script>