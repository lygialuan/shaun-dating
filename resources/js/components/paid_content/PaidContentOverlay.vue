<template>
    <div class="flex justify-center items-center p-12 absolute inset-0 z-10 bg-black-trans-4">
        <div class="flex flex-col items-center gap-base-2 text-white">
            <BaseIcon :name="lock ? 'lock_open' : 'lock'" size="40px"/>
            <BaseButton
                v-if="item.paid_type === 'subscriber'"
                @click="handleSubscribe"
                @mouseover="toggleUnlock(true)"
                @mouseleave="toggleUnlock(false)"
            >
                {{ $t("Subscribe to unlock") }}
            </BaseButton>
            <BaseButton
                v-if="item.paid_type === 'pay_per_view'"
                @click="handlePayPerView"
                @mouseover="toggleUnlock(true)"
                @mouseleave="toggleUnlock(false)"
            >
                {{ $filters.textTranslate(this.$t('Pay [amount] [currency] to unlock'), { amount: item.content_amount, currency: config.wallet.tokenName }) }}
            </BaseButton>
        </div>
        <div class="absolute bottom-3 left-1/2 -translate-x-1/2 text-white">
            <BaseIcon :name="item.type === 'photo' ? 'images' : 'video_solid'" />
            {{ item.type === 'photo' ? item.paid_item_content : formatDuration(item.paid_item_content) }}
        </div>
    </div>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { useAppStore } from '@/store/app'
import { usePostStore } from '@/store/post'
import { useAuthStore } from '@/store/auth'
import { useUtilitiesStore } from '@/store/utilities'
import { checkPopupBodyClass } from '@/utility/index'
import localData from '@/utility/localData';
import BaseIcon from '@/components/icons/BaseIcon.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import SubscribeUserModal from '@/components/modals/SubscribeUserModal.vue'
import PasswordModal from '@/components/modals/PasswordModal.vue'
import ShareOptionsMenu from '@/components/share/ShareOptionsMenu.vue'

export default {
    props: {
        item: Object,
        parentItem: {
            type: Object,
            default: null
        }
    },
    data(){
        return{
            lock: false
        }
    },
    computed:{
        ...mapState(useAuthStore, ['authenticated']),
        ...mapState(useAppStore, ['config', 'setOpenedModalCount'])
    },
    components: {
        BaseIcon,
        BaseButton 
    },
    methods:{
        ...mapActions(usePostStore, ['storePaidPost']),
        ...mapActions(useUtilitiesStore, ['pingNotification']),
        handleSubscribe(){
            if(this.authenticated){
                if(!this.item.ownerIsCreator){
                    return this.showCreatorPermissionModal(this.item.user.user_name)
                }
                const refCode = this.parentItem?.owner_ref_code ?? localData.get('ref_code', null);
                this.$dialog.open(SubscribeUserModal, {
                    data: {
                        user: this.item.user,
                        refCode: refCode
                    },
                    props:{
                        showHeader: false,
                        modal: true,
                        dismissableMask: true,
                        draggable: false
                    }
                });
            } else {
                this.showRequireLogin()
            }
        },
        toggleUnlock(status) {
            this.lock = status;
        },
        async handlePayPerView(){     
            if(this.authenticated){
                if(!this.item.ownerIsCreator){
                    return this.showCreatorPermissionModal(this.item.user.user_name)
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
                                const refCode = this.parentItem?.owner_ref_code ?? localData.get('ref_code', null);
                                try {
                                    await this.storePaidPost({
                                        postId: this.item.id,
                                        id: this.item.id,
                                        password: data.password,
                                        ref_code: refCode
                                    })
                                    this.showSuccess(this.$t('Post has been unlocked'))
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
            } else {
                this.showRequireLogin()
            }
        },
        openShareModal(type, subject){
            if(this.authenticated){
                if(!this.item.ownerIsCreator){
                    return this.showCreatorPermissionModal(this.item.user.user_name)
                }
                this.setOpenedModalCount()
                this.$dialog.open(ShareOptionsMenu, {
                    data: {
                        subjectType: type,
                        subject: subject
                    },
                    props:{
                        showHeader: false,
                        class: 'dropdown-menu-modal',
                        modal: true,
                        dismissableMask: true,
                        draggable: false
                    },
                    onClose: () => {
                        checkPopupBodyClass();
                        this.setOpenedModalCount(false)
                    }
                })
            }else{
                this.showRequireLogin()
            }
        }
    }
}
</script>