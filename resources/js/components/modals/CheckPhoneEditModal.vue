<template>
	<div>
        <p class="mb-1">{{$t('We just sent a code to your phone number')}}&nbsp;{{ phoneNumber }}.&nbsp;{{ $t('Code will expire in 2 minutes.') }}</p>	
        <BaseInputOtp v-model="code" class="mb-base-2" />
        <div class="flex flex-col gap-base-2">
            <BaseButton :loading="loadingSend" @click="handleCheckPhoneEdit()">{{$t('Continue')}}</BaseButton>
            <div class="flex gap-base-2">
                <BaseButton :loading="loadingResend" class="flex-1" type="secondary" @click="handleSendPhoneVerify(false)">{{$t('Resend code')}}</BaseButton>
                <BaseButton type="secondary" class="flex-1" @click="reEnterPhoneNumber()">{{$t('Re-enter phone number')}}</BaseButton>
            </div>
        </div>
    </div>
</template>
<script>
import { mapState, mapActions } from 'pinia'
import { useAuthStore } from '@/store/auth'
import { checkPhoneWhenEdit, changePhoneWhenEdit } from '@/api/user'
import BaseInputOtp from '@/components/inputs/BaseInputOtp.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import ChangePhoneEditModal from '@/components/modals/ChangePhoneEditModal.vue'
import CheckPhoneEditModal from '@/components/modals/CheckPhoneEditModal.vue'

export default {
	components:{ BaseInputOtp, BaseButton },
    inject: ['dialogRef'],
    data(){
		return{
            code: null,
            loadingSend: false,
			loadingResend: false,
            phoneNumber: this.dialogRef.data.phone_number,
            password: this.dialogRef.data.password
		}
	},
    computed:{
        ...mapState(useAuthStore, ['user']),
        formattedPhoneNumber(){
            return this.phoneNumber.replace(/\s+/g, '')
        }
    },
	methods: {
        ...mapActions(useAuthStore, ['updateUserMeInfo']),
		async handleCheckPhoneEdit(){
            this.loadingSend = true
            try {
                await checkPhoneWhenEdit({
                    code: this.code,
                    phone_number: this.phoneNumber
                })
                this.dialogRef.close();
                this.updateUserMeInfo({
                    ...this.user,
                    phone_number: this.formattedPhoneNumber
                })
                this.showSuccess(this.$t('Successfully changed'))
            } catch (error) {                
                this.showError(error.error)
            } finally {
                this.loadingSend = false
            }
        },
        async handleSendPhoneVerify(){
            if (this.loadingResend) {
                return
            }
            this.loadingResend = true
            try {
                await changePhoneWhenEdit({
                    phone_number: this.phoneNumber,
                    password: this.password
                })
                this.resetErrors(this.error)
                this.dialogRef.close();
                this.$dialog.open(CheckPhoneEditModal, {
                    data: {
                        phone_number: this.phoneNumber
                    },
                    props:{
                        header: this.$t('Enter your code'),
                        modal: true,
                        dismissableMask: true,
                        draggable: false
                    }
                });
            } catch (error) {
                this.showError(error.error)
            } finally {
                this.loadingResend = false
            }
        },
        reEnterPhoneNumber(){
            this.dialogRef.close();
            this.$dialog.open(ChangePhoneEditModal, {
				props:{
					header: this.$t('Change Activate Phone'),
					modal: true,
					dismissableMask: true,
					draggable: false,
                    class: 'enter-phone-modal'
				}
			});
        }
	}
}
</script>