<template>
    <p class="mb-base-2">{{ $t('Please check your email for a message with your code.') }}</p>
    <BaseInputOtp v-model="code" />
    <p class="mt-1">{{$t('We sent your code to')}}:&nbsp;{{ email }}</p>	
    <BaseButton :loading="loading" class="mt-3" @click="checkVerificationCode()" fluid>{{ $t('Verify') }}</BaseButton>
    <BaseButton :loading="loading_resend" type="transparent" class="mt-2" @click="resendCode()" fluid>{{ $t('Resend code')}}</BaseButton>
</template>

<script>
import { saveVerifyEmailPassword, addVerifyEmailPassword } from '@/api/user'
import BaseInputOtp from '@/components/inputs/BaseInputOtp.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'

export default {
    components: { BaseInputOtp, BaseButton },
    data() {
        return {
            email: this.dialogRef.data.email,
            password: this.dialogRef.data.password,
            password_confirmed: this.dialogRef.data.password_confirmed,
            code: null,
            loading: false,
            loading_resend: false
        }
    },
    inject: ['dialogRef'],
    methods: {
        async checkVerificationCode() {
            this.loading = true
            try {
                await saveVerifyEmailPassword(this.email, this.password, this.password_confirmed, this.code)
                this.showSuccess(this.$t('Email and password have been successfully updated.'))
                this.dialogRef.close();
            } catch (error) {
                this.showError(error.error)
            } finally {
                this.loading = false
            }
        },
        async resendCode() {
            this.loading_resend = true
            try {
                await addVerifyEmailPassword(this.email, this.password, this.password_confirmed)
                this.showSuccess(this.$t('A verification code has been sent to your email account.'))
            } catch (error) {
                this.showError(error.error)
            } finally {
                this.loading_resend = false
            }
        }
    }
}
</script>