<template>
    <div class="options-menu-modal flex flex-col text-main-color dark:text-white" tabindex="0" autofocus>
        <button v-if="story.canReport" :class="importantActionClass" @click="reportStory(story.id)">{{$t('Report')}}</button>
        <button v-if="story.canDelete" :class="importantActionClass" @click="deleteStory(story.id)">{{$t('Delete Story')}}</button>
        <button :class="normalActionClass" @click="closeDialog()">{{$t('Cancel')}}</button>
    </div>
</template>

<script>
import { mapState, mapActions } from 'pinia';
import { checkPopupBodyClass } from '@/utility/index'
import { useStoriesStore } from '@/store/stories';
import { useAuthStore } from '@/store/auth';
import ReportModal from '@/components/modals/ReportModal.vue';
import PermissionModal from '@/components/modals/PermissionModal.vue';
import ShareStoriesModal from '@/components/modals/ShareStoriesModal.vue';

export default {
    data(){
        return{
            story: this.dialogRef.data.story
        }
    },
    inject: {
        dialogRef: {
            default: null
        }
    },
    computed:{
        ...mapState(useAuthStore, ['user']),
        importantActionClass(){
            return 'options-menu-modal-text options-menu-modal-border text-center p-4 border-t border-light-divider first:border-none dark:border-white/10 text-base-red font-bold';
        },
        normalActionClass(){
            return 'options-menu-modal-sub-text options-menu-modal-border text-center p-4 border-t border-light-divider first:border-none dark:border-white/10';
        }
    },
    methods:{
        ...mapActions(useStoriesStore, ['doDeleteStoryItem', 'setHasLayerModal']),
        reportStory(storyItemId){
            setTimeout(() => this.$dialog.open(ReportModal, {
                data: {
                    type: 'story_items',
                    id: storyItemId
                },
                props:{
                    header: this.$t('Report'),
                    class: 'post-report-modal',
                    modal: true,
                    draggable: false
                },
                onClose: () => {
                    checkPopupBodyClass()
                    this.dialogRef.close();
                }
            }), 300);
        },
        deleteStory(storyItemId){
            setTimeout(() => this.$confirm.require({
                message: this.$t('Are you sure you want to delete this story?'),
                header: this.$t('Please confirm'),
                acceptLabel: this.$t('Ok'),
                rejectLabel: this.$t('Cancel'),
                accept: () => {
                    this.doDeleteStoryItem({
                        id: storyItemId,
                        storyId: this.story.story_id
                    });
                    checkPopupBodyClass()
                    this.dialogRef.close();
                },
                reject: () => {
                    checkPopupBodyClass()
                },
                onHide: () => {
                    checkPopupBodyClass()
                }
            }), 300);         
        },
         async handleShareStory(storyId){
            let permission = 'chat.allow'
            if (! window._.has(this.user.permissions, permission) || ! this.user.permissions[permission]) {
                this.$dialog.open(PermissionModal, {
                    props:{
                        header: this.$t('Permission'),
                        modal: true,
                        draggable: false
                    },
                    data: {
                        permission: permission
                    }
                })
                return
            }
            this.closeDialog();
            this.$dialog.open(ShareStoriesModal, {
                data: { storyId },
                props:{
                    header: this.$t('Share'),
                    class: 'follow-modal',
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                }, 
                onClose: () => {
                    this.setHasLayerModal(false)
                }
            });
            this.setHasLayerModal(true)
        },
        closeDialog() {
            this.dialogRef.close();
        }
    }
}
</script>