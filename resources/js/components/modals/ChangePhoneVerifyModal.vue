<template>
    <form @submit.prevent="handleChangePhone" class="flex flex-col gap-y-base-2">
        <BaseInputTel v-model="phoneNumber" :placeholder="$t('Phone Number')" :error="error.phone_number" autofocus />
        <div v-if="enableWidget" class="text-center">
            <CloudFlareTurnstile v-model="turnstileToken" />
        </div>
        <BaseButton :loading="loadingChange" :disabled="!isVerified()">{{$t('Save and send code')}}</BaseButton>
    </form>
</template>

<script>
import { mapState } from 'pinia'
import { useAppStore } from '@/store/app'
import { changePhoneVerify } from '@/api/user'
import { useCaptcha } from '@/hooks/useCaptcha'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseInputTel from '@/components/inputs/BaseInputTel.vue';
import CloudFlareTurnstile from '@/components/utilities/CloudFlareTurnstile.vue'

export default {
    components: { 
        BaseButton, 
        BaseInputTel,
        CloudFlareTurnstile 
    },
    inject: ['dialogRef'],
    data(){
        const appStore = useAppStore()
        const captcha = useCaptcha(appStore.config.sendPhoneEnableRecapcha, this.enableRecapcha, this.enableTurnstile)
        return {
            token: null,
            phoneNumber: null,
            error:{
                phone_number: null
            },
            loadingChange: false,
            ...captcha
        }
    },
    computed:{
        ...mapState(useAppStore, ['config'])
    },
    mounted(){
        setTimeout(() => {
            this.loadRecaptcha(this.$recaptchaInstance)
        }, 2000);
    },
    unmounted(){
        this.unloadRecaptcha(this.$recaptchaInstance)
    },
    methods: {
        async handleChangePhone(){
            if (this.loadingChange) {
                return
            }
            this.loadingChange = true
            try {
                this.token = await this.getCaptchaToken(this.$recaptcha, this.turnstileToken, "send_phone")
                await changePhoneVerify({
                    phone_number: this.phoneNumber,
                    token: this.token
                })
                this.dialogRef.close({phone_number: this.phoneNumber})
                this.resetErrors(this.error)
                this.showSuccess(this.$t('Phone number updated. A new code has been sent.'))
            } catch (error) {
                this.handleApiErrors(this.error, error)
            } finally {
                this.loadingChange = false
            }
        }
    },
    emits: ['confirm']
}
</script>