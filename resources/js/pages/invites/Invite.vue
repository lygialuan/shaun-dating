<template>
    <div class="py-5">
        <p class="font-bold text-center mb-base-2">{{$t('Invite by link')}}</p>
        <p class="text-center mb-base-2">{{$t('Copy the link below to invite people')}}</p>
        <div class="invites-button flex justify-between items-center gap-2 bg-web-wash px-5 py-base-2 rounded-base-10xl border border-dashed border-secondary-box-color dark:bg-dark-web-wash dark:border-white/10">
            <div ref="urlRef" class="flex-1 truncate">{{ user.ref_url }}</div>
            <button class="flex align-items-center gap-2 whitespace-nowrap" @click="copyURL()">
                <BaseIcon name="link_simple" class="invites-button-icon text-primary-color dark:text-dark-primary-color"/>
                <span>{{$t('Copy link')}}</span>
            </button>
        </div>
        <p class="font-bold text-center mt-base-7 mb-base-2">{{$t('Invite by email')}}</p>
        <form @submit.prevent="inviteEmails">
            <div class="mb-base-2">
                <BaseInputText v-model="emails" :placeholder="$filters.numberShortener(config.inviteMax, $t('Add up to [number] email address, separated by commas'), $t('Add up to [number] email addresses, separated by commas'))" :tooltip_mobile="$filters.numberShortener(config.inviteMax, $t('Add up to [number] email address, separated by commas'), $t('Add up to [number] email addresses, separated by commas'))"/>
            </div>
            <div class="mb-base-2">         
                <BaseTextarea v-model="message" :placeholder="$t('Message')" :rows="5" />
            </div>
            <div class="text-center">
                <BaseButton :loading="inviteEmailLoading" :disabled="!isVerified()">{{$t('Send')}}</BaseButton>
            </div>
        </form>
        <p class="font-bold text-center mt-base-7 mb-base-2">{{$t('Invite by CSV')}}</p>
        <div class="mb-1">
            <BaseInputFile ref="BaseInputFile" @upload-file="selectFile" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">{{$t('Import Contact')}}</BaseInputFile>
        </div>
        <div class="text-start mb-base-2">
            <a :href="asset('files/invite_example.csv')" download>{{$t('Download csv sample')}}</a>
        </div>
        <div class="text-center">
            <BaseButton :loading="inviteCSVLoading" :disabled="!isVerified()" @click="inviteCsvFile()" class="mb-base-2">{{$t('Send')}}</BaseButton>
            <CloudFlareTurnstile v-if="enableWidget" v-model="turnstileToken" />
        </div>
    </div> 
</template>

<script>
import { mapState } from 'pinia'
import { useAuthStore } from '@/store/auth'
import { useAppStore } from '@/store/app'
import { inviteByEmails, inviteByCsvFile } from '@/api/invite'
import { useCaptcha } from '@/hooks/useCaptcha'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseInputFile from '@/components/inputs/BaseInputFile.vue'
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import BaseTextarea from '@/components/inputs/BaseTextarea.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import CloudFlareTurnstile from '@/components/utilities/CloudFlareTurnstile.vue'

export default {
    components: { BaseIcon, BaseInputText, BaseTextarea, BaseButton, BaseInputFile, CloudFlareTurnstile },
    data(){
        const appStore = useAppStore()
        const captcha = useCaptcha(appStore.config.inviteEmailEnableRecapcha, this.enableRecapcha, this.enableTurnstile)
        return{
            emails: null,
            message: null,
            csv_file: null,
            token: null,
            inviteEmailLoading: false,
            inviteCSVLoading: false,
            ...captcha
        }
    },
    computed: {
        ...mapState(useAuthStore, ['user']),
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
        copyURL() {
            const urlElement = this.$refs.urlRef;
            const url = urlElement.textContent; 

            navigator.clipboard.writeText(url)
            this.showSuccess(this.$t('This link copied!'))

            const range = document.createRange();
            range.selectNodeContents(urlElement);
            const selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(range);
        },
        async inviteEmails(){
            this.inviteEmailLoading = true
            try {
                this.token = await this.getCaptchaToken(this.$recaptcha, this.turnstileToken, "invite")
                await inviteByEmails({
                    token: this.token,
                    emails: this.emails,
                    message: this.message
                })
                this.emails = null
                this.message = null
                this.showSuccess(this.$t('Your invitation has been sent.'))
            } catch (error) {
                this.showError(error.error)
            } finally {
                this.inviteEmailLoading = false
            }
        },
        selectFile(files) {
            this.csv_file = files.target.files[0]
        },
        async inviteCsvFile(){
            this.inviteCSVLoading = true
            try {
                if(this.config.inviteEmailEnableRecapcha){
                    if(this.enableRecapcha()){
                        await this.$recaptcha("invite").then(token => {
                            this.token = token
                        })
                    } else if(this.enableTurnstile()){
                        this.token = this.turnstileToken
                    }
                }
                let formData = new FormData()
                if (this.csv_file) {
                    if(! this.checkUploadedData(this.csv_file, 'csv')){
                        return
                    }
                    formData.append('csv_file', this.csv_file)
                }				
                formData.append('token', this.token)
                await inviteByCsvFile(formData)
                this.csv_file = null
                this.$refs.BaseInputFile.clearSelectedFile()
                this.showSuccess(this.$t('Your invitation has been sent.'))
            } catch (error) {
                this.showError(error.error)
            } finally {
                this.inviteCSVLoading = false
            }
        }
    }
}
</script>