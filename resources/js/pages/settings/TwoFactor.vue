<template>
    <div class="space-y-base-2">
        <h4 class="font-bold">{{ $t('Two-Factor Authentication') }}</h4>
        <div>{{ $t('Two-factor authentication (2FA) provides an additional layer of security beyond passwords and is strongly recommended. Your account is protected by requiring both your password and an authentication code from authenticated email.') }}</div>
        <div class="flex items-center gap-3">
            <label>{{ $t('Two-factor authentication (2FA)') }}</label>
            <BaseSwitch v-model="enable2FA" @click="handleClick2FAToggle" readonly /> 
        </div>
        <div v-if="currentInfo2FA">{{ currentInfo2FA }}</div>
    </div>
</template>

<script>
import { get2FACurrent, remove2FA } from '@/api/two_factor'
import BaseSwitch from '@/components/inputs/BaseSwitch.vue'
import PasswordModal from '@/components/modals/PasswordModal.vue'
import TwoFactorModal from '@/components/modals/TwoFactorModal.vue'

export default {
    components: {
        BaseSwitch
    },
    data(){
        return {
            current2FA: null
        }
    },
    computed: {
        enable2FA() {
            return !!this.current2FA
        },
        currentInfo2FA(){
            switch (this.current2FA?.provider?.type) {
                case 'mail':
					return this.$t('Authentication code to') + ': ' + this.current2FA.params.email;
                case 'sms':
                    return this.$t('Authentication code to') + ': ' + this.current2FA.params.phone_number;
                case 'auth_app':
					return this.$t('Authentication app: Configured')
				default: 
					return null
            }
        }
    },
    mounted(){
        this.handleGetCurrent2FA()
    },
    methods: {
        async handleGetCurrent2FA(){
            try {
                this.current2FA = await get2FACurrent()
            } catch (error) {
                this.showError(error.error)
            }
        },
        async handleDisable2FA(){
            this.$confirm.require({
                message: this.$t('Are you sure you want to disable Two-factor authentication (2FA) and remove your previous authenticated?'),
                header: this.$t('Please confirm'),
                acceptLabel: this.$t('Ok'),
                rejectLabel: this.$t('Cancel'),
                accept: () => {
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
                                    try {
                                        await remove2FA({password: data.password})
                                        this.showSuccess(this.$t("You've successfully disable 2FA Authentication"))
                                        this.handleGetCurrent2FA()
                                    } catch (error) {
                                        this.showError(error.error)
                                    } finally {
                                        passwordDialog.close()
                                    }
                                }
                            }
                        }
                    })
                }
            });
        },
        handleEnable2FA(){
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
                            this.$dialog.open(TwoFactorModal, {
                                data: {
                                    password: data.password
                                },
                                props:{
                                    header: this.$t('Choose an authentication method'),
                                    modal: true,
                                    dismissableMask: true,
                                    draggable: false
                                },
                                onClose: (options) => {
                                    if(options.data?.enable){
                                        this.handleGetCurrent2FA()
                                    }
                                }
                            });
                            passwordDialog.close()
                        }
                    }
                }
            })
        },
        handleClick2FAToggle(){
            this.enable2FA ? this.handleDisable2FA() : this.handleEnable2FA()
        }
    }
}
</script>