<template>
    <div class="flex flex-col md:flex-row md:items-center justify-center gap-x-10 gap-y-3 p-0 md:p-5">
        <div class="flex-2 text-center min-w-0">
            <Avatar :user="user" :size="120" class="mx-auto mb-base-2"/>
            <UserName :user="user" class="text-xl justify-center" />
            <router-link :to="{name: 'profile', params: { user_name: user.user_name }}" class="break-word">{{ mentionChar + user.user_name }}</router-link>
        </div>
        <div class="flex-3 space-y-5">
            <h3 class="text-main-color text-2xl font-extrabold dark:text-white text-center">
                {{ $t('Tip') }} 
                <router-link :to="{name: 'profile', params: { user_name: user.user_name }}" class="break-word">{{ mentionChar + user.user_name }}</router-link>
                {{ (tipAmount || 0 )+ ' ' + config.wallet.tokenName }}
            </h3>
            <div v-if="tipPackages.length" class="grid grid-cols-2 md:grid-cols-4 gap-base-2 md:gap-3">
                <button 
                    v-for="tipPackage in tipPackages" 
                    :key="tipPackage.id"
                    class="p-base-1 rounded-[100px] text-center"
                    :class="selectedTipPackage?.id === tipPackage.id ? 'bg-primary-color dark:bg-dark-primary-color' : 'bg-badge-color dark:bg-dark-web-wash'"
                    @click="handleSelectPackage(tipPackage)"
                >
                    {{ tipPackage.amount }}
                </button>
            </div>
            <div class="font-bold text-center">{{ $t('Enter amount') }}</div>
            <BaseInputNumber v-model="tipAmount" />
            <BaseButton @click="handleSendTip" fluid>{{ $t('Send Tip') }}</BaseButton>
        </div>
    </div>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { useAppStore } from '@/store/app'
import { useUtilitiesStore } from '@/store/utilities'
import { getTipPackages, storeTip } from '@/api/paid_content'
import Constant from '@/utility/constant'
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'
import PasswordModal from '@/components/modals/PasswordModal.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseInputNumber from '@/components/inputs/BaseInputNumber.vue'

export default {
    inject: {
        dialogRef: {
            default: null
        }
    },
    components: {
        Avatar,
        UserName,
        BaseButton,
        BaseInputNumber
    },
    data(){
        return{
            mentionChar: Constant.MENTION,
            user: this.dialogRef.data.user,
            tipPackages: [],
            selectedTipPackage: null,
            tipAmount: null
        }
    },
    computed:{
        ...mapState(useAppStore, ['config'])
    },
    watch: {
        tipAmount(newVal) {
            if (this.selectedTipPackage && newVal !== this.selectedTipPackage.amount) {
                this.selectedTipPackage = null;
            }
        }
    },
    mounted(){
        this.getTipPackages();
    },
    methods:{
        ...mapActions(useUtilitiesStore, ['pingNotification']),
        async getTipPackages(){
            try {
                this.tipPackages = await getTipPackages();
            } catch (error) {
                this.showError(error.error)
            }
        },
        handleSelectPackage(selectedPackage){
            if(this.selectedTipPackage?.id === selectedPackage.id){
                this.selectedTipPackage = null
                this.tipAmount = null
                return
            }
            this.selectedTipPackage = selectedPackage
            this.tipAmount = selectedPackage.amount
        },
        handleSendTip(){
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
                                await storeTip({
                                    package_id: this.selectedTipPackage?.id,
                                    amount: this.tipAmount,
                                    user_id: this.user.id,
                                    password: data.password
                                });
                                this.showSuccess(this.$t('Tip successful'))
                                this.dialogRef.close()
                                this.pingNotification()
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
    }
}
</script>