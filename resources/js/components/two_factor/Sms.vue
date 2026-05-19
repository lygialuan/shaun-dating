<template>
    <div class="space-y-base-2">
        <div v-if="currentStep === 1">
            <label class="block mb-base-1">{{ $t('Enter your phone number') }}</label>
            <BaseInputTel v-model="phoneNumber" />
            <p class="text-sub-color text-xs italic mt-base-1 dark:text-slate-400">{{ $t('We will use this phone number to send you a verification code.') }}</p>
        </div>
        <div v-if="currentStep === 2">
            <label class="block mb-base-1">{{ $t('Enter your verification code') }}</label>
            <BaseInputOtp v-model="code" />
        </div>
        <div class="flex flex-wrap gap-base-2">
            <BaseButton @click="$emit('back')">{{$t('Back')}}</BaseButton>
            <BaseButton :loading="loadingNextStep" @click="handleNext">{{$t('Next')}}</BaseButton>
        </div>
    </div>
</template>

<script>
import { mapState} from 'pinia'
import { useAuthStore } from '@/store/auth'
import { sendSetupPhone, verifySetupPhone } from '@/api/two_factor'
import BaseInputTel from '@/components/inputs/BaseInputTel.vue'
import BaseInputOtp from '@/components/inputs/BaseInputOtp.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'

export default {
    props: ['password'],
    components: {
        BaseInputTel,
        BaseInputOtp,
        BaseButton
    },
    data(){
        return {
            phoneNumber: null,
            code: null,
            currentStep: 1,
            loadingNextStep: false
        }
    },
    computed: {
        ...mapState(useAuthStore, ['user'])
    },
    mounted() {
        this.phoneNumber = this.user.phone_number
    },
    methods:{
        async handleSendPhone(){
            this.loadingNextStep = true
            try {
                await sendSetupPhone({
                    phone_number: this.phoneNumber,
                    password: this.password
                })
                this.showSuccess(this.$t('A verification code has been sent to your phone number.'))
                this.currentStep = 2
            } catch (error) {
                this.showError(error.error)
            } finally {
                this.loadingNextStep = false
            }
        },
        async handleVerifyPhone(){
            this.loadingNextStep = true
            try {
                await verifySetupPhone({
                    phone_number: this.phoneNumber,
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
        handleBack(){
            switch (this.currentStep) {
                case 1:
                    this.$emit('back')
                    break;
                case 2:
                    this.currentStep = 1
            }
        },
        handleNext(){
            switch (this.currentStep) {
                case 1:
                    this.handleSendPhone()
                    break;
                case 2:
                    this.handleVerifyPhone()
                    break;
            }
        }
    }
}
</script>