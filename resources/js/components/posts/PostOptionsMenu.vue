<template>
    <div class="options-menu-modal flex flex-col text-main-color dark:text-white outline-none" tabindex="0" autofocus>
        <button v-if="post.canReport" :class="importantActionClass" @click="reportPost(post.id)">{{ $t('Report') }}</button>
        <button v-if="post.canDelete" :class="importantActionClass" @click="deletePost(post.id)">{{ $t('Delete Post') }}</button>
        <button v-if="post.canEdit" :class="normalActionClass" @click="editPost(post)">{{ $t('Edit Post') }}</button>
        <button v-if="post.isEdited" :class="normalActionClass" @click="viewEditHistory(post.id)">{{ $t('View Edit History') }}</button>
        <button v-if="post.enable_notify" :class="normalActionClass" @click="toggleNotification(post.id, 'remove')">{{ $t('Stop Notifications') }}</button>
        <button v-else :class="normalActionClass" @click="toggleNotification(post.id, 'add')">{{ $t('Turn on Notifications') }}</button>
        <button v-if="post.canChangeCommentPrivacy" :class="normalActionClass" @click="openCommentPrivacyModal(post)">{{ $t('Change Who Can Comment') }}</button>
        <button v-if="post.canChangeContentWarning" :class="normalActionClass" @click="openContentWarningModal(post)">{{ $t('Change Content Warning Setting') }}</button>
        <button v-if="post.canPin" :class="normalActionClass" @click="togglePin(post, 'pin')">{{ $t('Pin Post') }}</button>
        <button v-if="post.canUnPin" :class="normalActionClass" @click="togglePin(post, 'unpin')">{{ $t('Unpin Post') }}</button>
        <button v-if="post.canStopComment" :class="normalActionClass" @click="toggleCommenting(post)">{{ post.stop_comment ? $t('Turn on commenting') : $t('Turn off commenting') }}</button>
        <button v-if="post.canPinProfile" :class="normalActionClass" @click="handlePinProfile(post.id, 'pin')">{{ $t('Pin to my profile') }}</button>
        <button v-if="post.canUnPinProfile" :class="normalActionClass" @click="handlePinProfile(post.id, 'unpin')">{{ $t('Unpin from my profile') }}</button>
        <button v-if="post.canPinHome" :class="normalActionClass" @click="handlePinHomePage(post.id, 'pin')">{{ $t('Pin to home page') }}</button>
        <button v-if="post.canUnPinHome" :class="normalActionClass" @click="handlePinHomePage(post.id, 'unpin')">{{ $t('Unpin from home page') }}</button>
        <button v-if="post.canChangePaidContent" :class="normalActionClass" @click="handleChangePaidContent(post)">{{ $t('Change Paid Content') }}</button>
        <button :class="normalActionClass" @click="closeDialog()">{{ $t('Cancel') }}</button>
    </div>
</template>

<script>
import { mapState, mapActions } from 'pinia';
import { checkPopupBodyClass } from '@/utility/index'
import { usePostStore } from '@/store/post';
import { useAuthStore } from '@/store/auth';
import { useAppStore } from '@/store/app'
import ReportModal from '@/components/modals/ReportModal.vue';
import EditModal from '@/components/modals/EditModal.vue'
import EditHistoriesListModal from '@/components/modals/EditHistoriesListModal.vue';
import CommentPrivacyModal from '@/components/modals/CommentPrivacyModal.vue';
import ChangeContentWarningModal from '@/components/modals/ChangeContentWarningModal.vue';
import ChangePaidContentModal from '@/components/modals/ChangePaidContentModal.vue';

export default {
    data(){
        return{
            post: this.dialogRef.data.post
        }
    },
    inject: {
        dialogRef: {
            default: null
        }
    },
    computed:{
        ...mapState(useAuthStore, ['user']),
        ...mapState(useAppStore, ['setOpenedModalCount']),
        importantActionClass(){
            return 'options-menu-modal-text options-menu-modal-border text-center p-4 border-t border-light-divider first:border-none dark:border-white/10 text-base-red font-bold';
        },
        normalActionClass(){
            return 'options-menu-modal-sub-text options-menu-modal-border text-center p-4 border-t border-light-divider first:border-none dark:border-white/10';
        }
    },
    methods:{
        ...mapActions(usePostStore, [
            'deletePostItem',
            'toggleEnableNotificationPostItem',
            'togglePinPostItem',
            'togglePostCommenting',
            'togglePinProfile',
            'togglePinHomePage',
        ]),
        openModal(component, data, props = {}) {
            this.closeDialog();
            this.setOpenedModalCount();
            setTimeout(() => {
                this.$dialog.open(component, {
                    data,
                    props: {
                        modal: true,
                        draggable: false,
                        ...props,
                    },
                    onClose: () => {
                        checkPopupBodyClass();
                        this.setOpenedModalCount(false);
                    },
                });
            }, 300);
        },
        reportPost(postId) {
            this.openModal(ReportModal, { type: 'posts', id: postId }, { header: this.$t('Report') });
        },
        editPost(post) {
            this.openModal(EditModal, { type: 'posts', id: post.id, content: post.content }, { header: this.$t('Edit') });
        },
        viewEditHistory(postId) {
            this.openModal(EditHistoriesListModal, { type: 'posts', id: postId }, { header: this.$t('Edit History') });
        },
        openCommentPrivacyModal(post) {
            this.openModal(CommentPrivacyModal, { post }, { header: this.$t('Change Comment Privacy') });
        },
        openContentWarningModal(post) {
            this.openModal(ChangeContentWarningModal, { post }, { header: this.$t('Change Content Warning Setting') });
        },
        deletePost(postId){
            this.closeDialog();
            this.setOpenedModalCount()
            setTimeout(() => this.$confirm.require({
                message: this.$t('Are you sure you want to delete this post?'),
                header: this.$t('Please confirm'),
                acceptLabel: this.$t('Ok'),
                rejectLabel: this.$t('Cancel'),
                accept: () => {
                    this.deletePostItem({
                        id: postId
                    });
                    checkPopupBodyClass()
                    this.setOpenedModalCount(false)
                },
                reject: () => {
                    checkPopupBodyClass()
                    this.setOpenedModalCount(false)
                },
                onHide: () => {
                    checkPopupBodyClass()
                    this.setOpenedModalCount(false)
                }
            }), 300);         
        },
        async handleAsyncAction(action, successMessage) {
            try {
                await action();
                this.showSuccess(successMessage);
            } catch (error) {
                this.showError(error.error);
            } finally {
                this.closeDialog();
            }
        },
        toggleNotification(postId, action) {
            this.handleAsyncAction(() =>
                this.toggleEnableNotificationPostItem({
                    subject_type: 'posts',
                    subject_id: postId,
                    action,
                }),
                action === 'add' ? this.$t('Notifications are turned on') : this.$t('Notifications are turned off')
            );
        },
        togglePin(post, action) {
            this.handleAsyncAction(() =>
                this.togglePinPostItem({ id: post.source.id, post_id: post.id, action }),
                action === 'pin' ? this.$t('Post is pinned.') : this.$t('Post is unpinned.')
            );
        },
        handlePinProfile(id, action) {
            this.handleAsyncAction(() =>
                this.togglePinProfile({ id, action }),
                action === 'pin' ? this.$t('Post is pinned to profile.') : this.$t('Post is unpinned from profile.')
            );
        },
        handlePinHomePage(id, action) {
            this.handleAsyncAction(() =>
                this.togglePinHomePage({ id, action }),
                action === 'pin' ? this.$t('Post is pinned to home page.') : this.$t('Post is unpinned from home page.')
            );
        },
        toggleCommenting(post) {
            this.handleAsyncAction(() =>
                this.togglePostCommenting({ id: post.id, stop: !post.stop_comment }),
                post.stop_comment ? this.$t('Comments are turned on') : this.$t('Comments are turned off')
            );
        },
        handleChangePaidContent(post){
            this.openModal(ChangePaidContentModal, { post }, { header: this.$t('Paid Content Settings'), class: 'p-dialog-md' });
        },
        closeDialog() {
            this.dialogRef.close();
        }
    }
}
</script>