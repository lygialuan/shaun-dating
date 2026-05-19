<template>
    <div class="space-y-base-2">
        <div v-if="currentStep === 1">
            <label class="block mb-base-1">{{ $t('Enter your email') }}</label>
            <BaseInputText v-model="email" />
            <p class="text-sub-color text-xs italic mt-base-1 dark:text-slate-400">{{ $t('We will use this email to send you a confirmation code.') }}</p>
        </div>
        <div v-if="currentStep === 2">
            <label class="block mb-base-1">{{ $t('Enter your verification code') }}</label>
            <BaseInputOtp v-model="code" />
        </div>
        <div class="flex flex-wrap gap-base-2">
            <BaseButton @click="handleBack">{{$t('Back')}}</BaseButton>
            <BaseButton :loading="loadingNextStep" @click="handleNext">{{$t('Next')}}</BaseButton>
        </div>
    </div>
</template>

<script>
import { mapState} from 'pinia'
import { useAuthStore } from '@/store/auth'
import { sendSetupMail, verifySetupMail } from '@/api/two_factor'
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import BaseInputOtp from '@/components/inputs/BaseInputOtp.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'

export default {
    props: ['password'],
    components: {
        BaseInputText,
        BaseInputOtp,
        BaseButton
    },
    data(){
        return {
            email: null,
            code: null,
            currentStep: 1,
            loadingNextStep: false
        }
    },
    computed: {
        ...mapState(useAuthStore, ['user'])
    },
    mounted() {
        this.email = this.user.email
    },
    methods:{
        async handleSendMail(){
            this.loadingNextStep = true
            try {
                await sendSetupMail({
                    email: this.email,
                    password: this.password
                })
                this.showSuccess(this.$t('A verification code has been sent to your email.'))
                this.currentStep = 2
            } catch (error) {
                this.showError(error.error)
            } finally {
                this.loadingNextStep = false
            }
        },
        async handleVerifyMail(){
            this.loadingNextStep = true
            try {
                await verifySetupMail({
                    email: this.email,
                    password: this.password,
                    code: this.code
                })
                this.showSuccess(this.$t("You've successfully enable 2FA Authentication"))
                this.$emit('success')
            } catch (error) {
                this.showError(error.error)
            } finally {
                this.loadingNextStep = false
            }
        },
        handleNext(){
            switch (this.currentStep) {
                case 1:
                    this.handleSendMail()
                    break;
                case 2:
                    this.handleVerifyMail()
                    break;
            }
        },
        handleBack(){
            switch (this.currentStep) {
                case 1:
                    this.$emit('back')
                    break;
                case 2:
                    this.currentStep = 1
            }
        }
    }
}
</script>