<template>
    <div class="options-menu-modal flex flex-col text-main-color dark:text-white" tabindex="0" autofocus>
        <button v-if="comment.canReport" :class="importantActionClass" @click="reportCommentItem(comment.id)">{{$t('Report')}}</button>
        <button v-if="comment.canDelete" :class="importantActionClass" @click="deleteCommentItem(comment)">{{$t('Delete Comment')}}</button>
        <button v-if="comment.canEdit" :class="normalActionClass" @click="editComment(comment)">{{$t('Edit Comment')}}</button>
        <button v-if="comment.isEdited" :class="normalActionClass" @click="viewEditHistory(comment.id)">{{$t('View Edit History')}}</button>
        <button :class="normalActionClass" @click="cancel()">{{$t('Cancel')}}</button>
    </div>
</template>

<script>
import { mapActions } from 'pinia';
import { checkPopupBodyClass } from '@/utility/index'
import ReportModal from '@/components/modals/ReportModal.vue';
import EditModal from '@/components/modals/EditModal.vue'
import EditHistoriesListModal from '@/components/modals/EditHistoriesListModal.vue'
import { useCommentStore } from '@/store/comment';
import { usePostStore } from '@/store/post'

export default {
    data(){
        return{
            item: this.dialogRef.data.item,
            comment: this.dialogRef.data.comment
        }
    },
    inject: ['dialogRef'],
    computed:{
        importantActionClass(){
            return 'options-menu-modal-text options-menu-modal-border text-center p-4 border-t border-light-divider first:border-none dark:border-white/10 text-base-red font-bold';
        },
        normalActionClass(){
            return 'options-menu-modal-sub-text options-menu-modal-border text-center p-4 border-t border-light-divider first:border-none dark:border-white/10';
        }
    },
    methods:{
        ...mapActions(useCommentStore, ['deleteComment']),
        ...mapActions(usePostStore, ['decreaseCommentCount']),
        reportCommentItem(commentId){
            this.dialogRef.close();
            setTimeout(() => this.$dialog.open(ReportModal, {
                data: {
                    type: 'comments',
                    id: commentId
                },
                props:{
                    header: this.$t('Report'),
                    class: 'comment-report-modal',
                    modal: true,
                    draggable: false
                },
                onClose: () => {
                    checkPopupBodyClass()
                }
             }), 300);
        },
        deleteCommentItem(comment){
            this.dialogRef.close();
            setTimeout(() => this.$confirm.require({
                message: this.$t('Are you sure you want to delete this comment?'),
                header: this.$t('Please confirm'),
                acceptLabel: this.$t('Ok'),
                rejectLabel: this.$t('Cancel'),
                accept: () => {
                    this.deleteComment({
                        id: comment.id
                    })
                    checkPopupBodyClass()
                    this.decreaseCommentCount(this.item.id, comment.reply_count)
                },
                reject: () => {
                    checkPopupBodyClass()
                },
                onHide: () => {
                    checkPopupBodyClass()
                }
            }), 300);         
        },
        editComment(comment){
            this.dialogRef.close();
            setTimeout(() => this.$dialog.open(EditModal, {
                data: {
                    type: 'comments',
                    id: comment.id,
                    content: comment.content
                },
                props:{
                    header: this.$t('Edit'),
                    class: 'comment-edit-modal',
                    modal: true,
                    draggable: false
                },
                onClose: () => {
                    checkPopupBodyClass()
                }
             }), 300);
        },
        viewEditHistory(commentId){
            this.dialogRef.close();
            setTimeout(() => this.$dialog.open(EditHistoriesListModal, {
                data: {
                    type: 'comments',
                    id: commentId
                },
                props:{
                    header: this.$t('Edit History'),
                    class: 'edit-history-modal',
                    modal: true,
                    draggable: false
                },
                onClose: () => {
                    checkPopupBodyClass()
                }
            }), 300);
        },
        cancel(){
            this.dialogRef.close();
        }
    }
}
</script>