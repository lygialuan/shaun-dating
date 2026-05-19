<template>
    <div class="p-4 space-y-6">
        <div class="flex">
            <button class="ml-auto text-left">
                <BaseIcon name="close" @click="closeModal(false)"/>
            </button>
        </div>
        <div class="text-center space-y-2">
            <h2 class="text-2xl font-bold">{{ $t('Send a Gift to Impress') }}</h2>
            <p class="text-sm">{{ $t('Want to stand out? Send a virtual gift to show your interest and make a memorable impression.') }}</p>
            <div class="border-b"></div>
        </div>
        <div class="flex items-center gap-3">
            <Avatar :user="user" :activePopover="false" :size="70" :show-progress-badge="true" :router="false"/>
            <div class="flex-1 min-w-0">
                <UserName :user="user" :activePopover="false" class="sidebar-user-menu-name"/>
                <span class="max-w-[100px] sidebar-user-menu-sub-text inline-block text-xs text-sub-color dark:text-dark-text-base truncate">{{mentionChar + user.user_name}}</span>  
            </div>
        </div>
        <div class="grid grid-cols-3 sm:grid-cols-3 md:grid-cols-4 gap-3">
            <div v-for="gift in gifts" :key="gift.id" class="rounded-xl border bg-white dark:bg-dark-form-surface dark:border-dark-form-surface overflow-hidden">
                <div class="flex flex-col items-center justify-center flex-1 p-2">
                    <img :src="gift.icon" class="w-12 h-12 object-contain"/>
                    <div class="flex items-center justify-center text-xs mt-2">
                        <img :src="asset('images/default/poly_coin.png')" class="w-4 h-4"/>
                        <span>{{ gift.price }}</span>
                    </div>
                </div>
                <BaseButton @click="sendGift(gift)" size="sm" fluid>{{$t('Send')}}</BaseButton>
            </div>
        </div>
        <div class="space-y-2">
            <BaseButton v-if="config.wallet.enable" @click="addCredit()" fluid>{{$t('Add Credit')}}</BaseButton>
            <BaseButton type="transparent" @click="closeModal(false)" fluid>{{$t('Cancel')}}</BaseButton>
        </div>
    </div>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { useAppStore } from '@/store/app'
import { sendGift } from '@/api/gift'
import { checkPopupBodyClass } from '@/utility/index'
import { useUtilitiesStore } from '@/store/utilities'
import { useProfileStore } from '@/store/profile'
import Constant from '@/utility/constant'
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import PasswordModal from '@/components/modals/PasswordModal.vue'

export default {
    components: {
        Avatar,
        UserName,
        BaseButton,
        BaseIcon
    },
    inject: {
        dialogRef: {
            default: null
        }
    },
    data(){
        return{
            mentionChar: Constant.MENTION,
            user: this.dialogRef.data.user,
            gifts: [],
            loading: false
        }
    },
    mounted(){
        this.gifts = this.config.gifts
    },
    computed: {
		...mapState(useAppStore, ['config']),
        ...mapState(useProfileStore, ['setTotalGiftReceived']),
	},
    methods: {
        ...mapActions(useUtilitiesStore, ['pingNotification']),
        closeModal(closeProfile){
            this.dialogRef.close(closeProfile)
        },
        addCredit() {
            this.closeModal(true)
            this.$router.push({name: 'wallet'})
        },
        async sendGift(selectedGift){
            if (!selectedGift) return this.showError(this.$t('Please select a gift'))
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
                            if(this.loading) return
                            this.loading = true
                            try {
                                await sendGift({ receiver_id: this.user.id, gift_id: selectedGift.id, quantity: 1, target_type: 'profile', target_id: this.user.id, password: data.password })
                                this.pingNotification()
                                passwordDialog.close()
                                checkPopupBodyClass();
                                this.dialogRef.close({
                                    updatedGift: true,
                                    userId: this.user.id
                                })
                                this.setTotalGiftReceived()
                                this.showSuccess(this.$t('Gift has been sent.'))
                                this.loading = false
                            } catch (error) {
                                passwordDialog.close()
                                checkPopupBodyClass();
                                this.showError(error.error)
                                this.loading = false
                            } 
						}
					}
				}
			})
        }
    }
}
</script>