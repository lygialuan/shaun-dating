<template>
	<div class="main-content-section">
        <div class="main-content-section-header">
            <div class="main-content-section-header-title">{{ $t('Contact Us') }}</div>
        </div>
        <div class="space-y-3">
            <div class="flex flex-wrap gap-x-5"> 
                <div class="md:flex-1 md:text-end w-full mb-1 pt-2"><label>{{$t('Name')}}</label></div>
                <div class="md:flex-4 w-full">
                    <BaseInputText v-model="data.name" :placeholder="$t('Enter your name')" :error="error.name"/>
                </div>  
            </div>
            <div class="flex flex-wrap gap-x-5"> 
                <div class="md:flex-1 md:text-end w-full mb-1 pt-2"><label>{{$t('Email')}}</label></div>
                <div class="md:flex-4 w-full">
                    <BaseInputText v-model="data.email" :placeholder="$t('Enter your email')" :error="error.email"/>
                </div>  
            </div>
            <div class="flex flex-wrap gap-x-5"> 
                <div class="md:flex-1 md:text-end w-full mb-1 pt-2"><label>{{$t('Subject')}}</label></div>
                <div class="md:flex-4 w-full">
                    <BaseInputText v-model="data.subject" :placeholder="$t('Enter your subject')" :error="error.subject"/>
                </div>  
            </div>
            <div class="flex flex-wrap gap-x-5"> 
                <div class="md:flex-1 md:text-end w-full mb-1 pt-2"><label>{{$t('Message')}}</label></div>
                <div class="md:flex-4 w-full">
                    <BaseTextarea v-model="data.message" :placeholder="$t('Enter your message')" autoResize :error="error.message" />
                </div>  
            </div>
            <div class="text-center">
                <CloudFlareTurnstile v-if="enableWidget" v-model="turnstileToken" class="mb-base-1"/>
                <BaseButton @click="handleContact()" :loading="loadingSend" :disabled="!isVerified()">{{$t('Send')}}</BaseButton>
            </div>
        </div>
    </div>
</template>

<script>
import { mapState } from 'pinia'
import { storeContact } from '@/api/utility'
import { useAppStore } from '@/store/app'
import { useCaptcha } from '@/hooks/useCaptcha'
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import BaseTextarea from '@/components/inputs/BaseTextarea.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import CloudFlareTurnstile from '@/components/utilities/CloudFlareTurnstile.vue'

export default {
    components: { BaseInputText, BaseTextarea, BaseButton, CloudFlareTurnstile },
    data(){
        const appStore = useAppStore()
        const captcha = useCaptcha(appStore.config.contactEnableRecapcha, this.enableRecapcha, this.enableTurnstile)
		return {
            data: {
                email: null,
                message: null,
                subject: null,
                name: null
            },
			error: {
				name: null,
				email: null,
				subject: null,
				message: null
			},
            loadingSend: false,
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
        async handleContact(){
            this.loadingSend = true
            try {
                this.data.token = await this.getCaptchaToken(this.$recaptcha, this.turnstileToken, "contact")
                await storeContact(this.data)
                this.showSuccess(this.$t('Thank you! Your message has been sent'))
                Object.keys(this.data).forEach((key) => this.data[key] = null)
                this.resetErrors(this.error)
            } catch (error) {
                this.handleApiErrors(this.error, error)
            } finally {
                this.loadingSend = false
            }
        }
    }
}
</script>