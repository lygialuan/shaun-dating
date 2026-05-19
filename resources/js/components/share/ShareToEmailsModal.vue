<template>
    <div class="mb-4">
        <div class="mb-base-2">{{$t('Email')}}</div>
        <BaseInputText v-model="emails" :placeholder="$filters.numberShortener(config.shareEmailMax, $t('Add up to [number] email address, separated by commas'), $t('Add up to [number] email addresses, separated by commas'))" />
    </div>
    <div class="mb-4">
        <div class="mb-base-2">{{$t('Message')}}</div>
        <BaseTextarea :rows="5" :placeholder="$t('Message')" v-model="message" class="mb-base-2"/>
    </div>
    <div class="text-center mb-base-2">
        <CloudFlareTurnstile v-if="enableWidget" v-model="turnstileToken" />
    </div>
    <div class="text-end">
        <BaseButton @click="shareToEmail()" :loading="loadingShare" :disabled="!isVerified()">{{$t('Share')}}</BaseButton>
    </div>
</template>

<script>
import { mapState } from 'pinia'
import { shareToEmails } from '@/api/utility'
import { useAppStore } from '@/store/app';
import { useCaptcha } from '@/hooks/useCaptcha'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import BaseTextarea from '@/components/inputs/BaseTextarea.vue'
import CloudFlareTurnstile from '@/components/utilities/CloudFlareTurnstile.vue'

export default {
    components: { BaseButton, BaseInputText, BaseTextarea, CloudFlareTurnstile },
    inject: {
        dialogRef: {
            default: null
        }
    },
    data(){
        const appStore = useAppStore()
        const captcha = useCaptcha(appStore.config.shareEmailEnableRecapcha, this.enableRecapcha, this.enableTurnstile)
        return{
            loadingShare: false,
            token: null,
            emails: null,
            message: null,
            type: this.dialogRef.data.type,
            subject: this.dialogRef.data.subject,
            ...captcha
        }
    },
    computed: {
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
    methods:{
        async shareToEmail(){
            this.loadingShare = true
            try {
                this.token = await this.getCaptchaToken(this.$recaptcha, this.turnstileToken, "share")
                await shareToEmails({
                    token: this.token,
                    subject_type: this.type,
                    subject_id: this.subject.id,
                    emails: this.emails,
                    message: this.message
                })
                this.dialogRef.close()
                this.showSuccess(this.$t('Shared Successfully.'))
            } catch (error) {
                this.showError(error.error)
            } finally {
                this.loadingShare = false
            }
        }
    }
}
</script>