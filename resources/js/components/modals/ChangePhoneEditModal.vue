<template>
    <form @submit.prevent="handleChangePhone" class="flex flex-col gap-y-base-2">
        <p class="font-medium dark:text-white">{{ $t('Please enter your phone number in the box below to verify your phone number. We will send the verification code to your device.') }}</p>
        <BaseInputTel v-model="phoneNumber" :placeholder="$t('Phone Number')" :error="error.phone_number" autofocus />
        <div v-if="enableWidget" class="text-center">
            <CloudFlareTurnstile v-model="turnstileToken" />
        </div>
        <BaseButton :loading="loadingChange" :disabled="!isVerified()">{{$t('Change')}}</BaseButton>
    </form>
</template>

<script>
import { mapState } from 'pinia'
import { useAppStore } from '@/store/app'
import { changePhoneWhenEdit } from '@/api/user'
import { useCaptcha } from '@/hooks/useCaptcha'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseInputTel from '@/components/inputs/BaseInputTel.vue';
import PasswordModal from '@/components/modals/PasswordModal.vue'
import CheckPhoneEditModal from '@/components/modals/CheckPhoneEditModal.vue'
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
            const passwordDialog = this.$dialog.open(PasswordModal, {
				props: {
					header: this.$t('Enter Password'),
					class: 'password-modal',
					modal: true,
					dismissableMask: true,
					draggable: false
				},
				emits: {
					onConfirm: async (data) => {
						if (data.password) {
							if (this.loadingChange) {
                                return
                            }
                            this.loadingChange = true
                            try {
                                this.token = await this.getCaptchaToken(this.$recaptcha, this.turnstileToken, "send_phone")
                                await changePhoneWhenEdit({
                                    phone_number: this.phoneNumber,
                                    password: data.password,
                                    token: this.token
                                })
                                this.resetErrors(this.error)
                                this.dialogRef.close();
                                this.$dialog.open(CheckPhoneEditModal, {
                                    data: {
                                        phone_number: this.phoneNumber,
                                        password: data.password
                                    },
                                    props:{
                                        header: this.$t('Enter your code'),
                                        modal: true,
                                        dismissableMask: true,
                                        draggable: false
                                    }
                                });
                            } catch (error) {
                                this.handleApiErrors(this.error, error)
                            } finally {
                                this.loadingChange = false
                                passwordDialog.close()
                            }
						}
					}
				}
			})
        }
    }
}
</script>