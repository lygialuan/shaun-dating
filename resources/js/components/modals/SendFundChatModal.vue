<template>
    <form @submit.prevent="sendFundToUser">
        <div class="mb-base-2">
            <div class="mb-1"><label>{{ $t('Amount') }}</label></div>
            <BaseInputNumber v-model="fundAmount" :placeholder="$t('Value')" autofocus class="mb-base-2" />
            <p class="text-base-xs mb-base-2">{{ $t('Availables') + ': ' + exchangeTokenCurrency(user.wallet_balance) }}</p>
        </div>
        <div class="text-end">
            <BaseButton :disabled="!fundAmount">{{$t('Send')}}</BaseButton>
        </div>
    </form>
</template>
<script>
import { mapState, mapActions } from 'pinia'
import BaseInputNumber from '@/components/inputs/BaseInputNumber.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import PasswordModal from '@/components/modals/PasswordModal.vue'
import { useAuthStore } from '@/store/auth'
import { useAppStore } from '@/store/app'
import { useUtilitiesStore } from '@/store/utilities'
import { useChatStore } from '@/store/chat'
import { checkPopupBodyClass } from '@/utility/index'

export default {
    components: { BaseInputNumber, BaseButton },
    data(){
        return{
            disableAutoComplete: false,
            userMentionList: null,
            selectedUser: this.dialogRef.data.selectedUser,
            fundAmount: null,
            onlySearchUser: false,
            notSearchMyself: true,
            messageData: this.dialogRef.data.messageData
        }
    },
    inject: ['dialogRef'],
    computed:{
        ...mapState(useAuthStore, ['user']),
        ...mapState(useAppStore, ['config'])
    },
    methods:{
        ...mapActions(useAuthStore, ['sendFund']),
        ...mapActions(useUtilitiesStore, ['pingNotification']),
        ...mapActions(useChatStore, ['sendFundMessage']),
        sendFundToUser(){
            if(this.selectedUser.length === 0){
                this.showError(this.$t('Please select user to send fund.'));
                return;
            }
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
                                await this.sendFundMessage({
                                    amount: this.fundAmount,
                                    user_id: this.selectedUser[0].id,
                                    password: data.password,
                                    room_id: this.messageData.room_id,
                                    content: this.messageData.content
                                })
                                this.pingNotification()
                                this.dialogRef.close()
                                passwordDialog.close()
                                checkPopupBodyClass();
                                this.showSuccess(this.$t('Send fund successfully.'))
                            } catch (error) {
                                this.showError(error.error)
                                passwordDialog.close()
                                checkPopupBodyClass();
                            }
						}
					}
				}
			})
        }
    }
}
</script>