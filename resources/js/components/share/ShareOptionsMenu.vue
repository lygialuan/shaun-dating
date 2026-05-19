<template>
    <div class="options-menu-modal flex flex-col text-main-color dark:text-white" tabindex="0" autofocus>
        <button :class="normalActionClass" @click="copyLink(subject.href)">{{$t('Copy link')}}</button>
        <button :class="normalActionClass" @click="shareToProfile(subjectType, subject)">{{$t('Share to your profile')}}</button>
        <button :class="normalActionClass" @click="shareToEmails(subjectType, subject)">{{$t('Mail')}} </button>
        <button :class="normalActionClass" @click="shareToSocialNetworks(subject)">{{$t('Share to social networks')}}</button>
        <button v-if="subject.canSendMessage" :class="normalActionClass" @click="handleSendPostMessage(subjectType, subject)">{{$t('Send Message')}}</button>
        <button :class="normalActionClass" @click="cancel()">{{$t('Cancel')}}</button>
    </div>
</template>

<script>
import { mapState } from 'pinia';
import { defineAsyncComponent } from 'vue'
import { checkPopupBodyClass } from '@/utility/index'
import { useAppStore } from '@/store/app'
import SendItemMessage from '@/components/modals/SendItemMessage.vue';

export default {
    data() {
        return {
            subjectType: this.dialogRef.data.subjectType,
            subject: this.dialogRef.data.subject
        }
    },
    inject: {
        dialogRef: {
            default: null
        }
    },
    computed:{
        ...mapState(useAppStore, ['setOpenedModalCount']),
        normalActionClass(){
            return 'options-menu-modal-sub-text options-menu-modal-border text-center p-4 border-t border-light-divider first:border-none dark:border-white/10';
        }
    },
    methods: {
        copyLink(url) {
            navigator.clipboard.writeText(url)
            this.showSuccess(this.$t('This link copied!'))
            this.dialogRef.close()
        },
        shareToProfile(subjectType, subject){
            this.dialogRef.close();
            const ShareToProfileModal = defineAsyncComponent(() =>
                import('./ShareToProfileModal.vue')
            )
            this.setOpenedModalCount()
            setTimeout(() =>this.$dialog.open(ShareToProfileModal, {
                data: {
                    type: subjectType,
                    subject: subject
                },
                props:{
                    header: this.$t('Share to your profile'),
                    class: 'share-modal p-dialog-lg',
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
                onClose: () => {
                    checkPopupBodyClass();
                    this.setOpenedModalCount(false)
                }
            }), 300);
        },
        shareToEmails(subjectType, subject){
            this.dialogRef.close();
            const ShareToEmailsModal = defineAsyncComponent(() =>
                import('./ShareToEmailsModal.vue')
            )
            this.setOpenedModalCount()
            setTimeout(() =>this.$dialog.open(ShareToEmailsModal, {
                data: {
                    type: subjectType,
                    subject: subject
                },
                props:{
                    header: this.$t('Share to email'),
                    class: 'share-modal p-dialog-md',
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
                onClose: () => {
                    checkPopupBodyClass();
                    this.setOpenedModalCount(false)
                }
            }), 300);
        },
        shareToSocialNetworks(subject){
            this.dialogRef.close();
            const ShareToSocialNetworksModal = defineAsyncComponent(() =>
                import('./ShareToSocialNetworksModal.vue')
            )
            this.setOpenedModalCount()
            setTimeout(() =>this.$dialog.open(ShareToSocialNetworksModal, {
                data: {
                    subject: subject
                },
                props:{
                    header: this.$t('Share to social networks'),
                    class: 'share-modal p-dialog-sm',
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
                onClose: () => {
                    checkPopupBodyClass();
                    this.setOpenedModalCount(false)
                }
            }), 300);
        },
        handleSendPostMessage(subjectType, subject){
            this.dialogRef.close();
            this.setOpenedModalCount()
            setTimeout(() => this.$dialog.open(SendItemMessage, {
                data: {
                    type: subjectType,
                    subject: subject
                },
                props:{
                    header: this.$t('Select list to send'),
                    modal: true,
                    draggable: false
                },
                onClose: () => {
                    checkPopupBodyClass()
                    this.setOpenedModalCount(false)
                }
            }), 300);
        },
        cancel() {
            this.dialogRef.close();
        }
    }
}
</script>