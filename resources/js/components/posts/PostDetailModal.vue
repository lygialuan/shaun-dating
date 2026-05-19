<template>
    <button class="post-header-back sticky top-0 z-50 flex items-center justify-start w-full p-base-2 bg-white text-black border-b border-divider lg:hidden dark:bg-slate-800 dark:border-white/10 dark:text-white" @click="handleBeforeClose()">
        <BaseIcon name="arrow_left"></BaseIcon>
    </button>
    <div v-if="error" class="bg-white p-base-2 rounded-none md:rounded-base-lg dark:bg-slate-800">
        <Error class="mb-0">{{error}}</Error>
    </div>
    <PostContent v-else :post="post" @comment_click="focusForm()" />
    <CommentsList ref="commentsListRef" :item="post" :in-modal="true"/>
    <CloseButton @click="handleBeforeClose()" />
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { usePostStore } from '@/store/post'
import { useDraftStore } from '@/store/draft'
import Error from '@/components/utilities/Error.vue'
import PostContent from '@/components/posts/PostContent.vue'
import CommentsList from '@/components/comments/CommentsList.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import CloseButton from '@/components/utilities/CloseButton.vue'

export default {
    components: {
        Error,
        PostContent,
        CommentsList,
        BaseIcon,
        CloseButton
    },
    inject: ['dialogRef'],
    data() {    
        return {
            error: null,
            post: this.dialogRef.data.post,
            keydownHandler: null
		}
	},
    computed: {
        ...mapState(usePostStore, ['postsList']),
        ...mapState(useDraftStore, ['drafts']),
        draftKey(){
            return `comment_draft_posts_${this.post?.id}`;
        }
    },
    watch: {
        '$route'(){
            this.dialogRef.close()
        },
        postsList(newValue, oldValue) {
            if(newValue < oldValue){ // Post Item has been deleted
                this.closePostDetailModal()
            }
        }
    },
    mounted(){
        this.$refs.commentsListRef.scrollToBottom()

        const maskEl = document.querySelector('.p-dialog-mask');
		if (maskEl) {
            maskEl.addEventListener('mousedown', this.onMaskClick, true);
        }

		this.keydownHandler = (e) => {
            if (e.key === 'Escape') {
                this.handleBeforeClose();
            }
        }
        document.addEventListener('keydown', this.keydownHandler);
    },
    unmounted(){
        this.cleanupListeners()
    },
    methods: {
        ...mapActions(useDraftStore, ['removeDraft']),
        focusForm(){
            this.$refs.commentsListRef.focusForm()
        },
        closePostDetailModal(){
            this.dialogRef.close();
            this.removeDraft(this.draftKey)
        },
        async handleBeforeClose(){
            const draftKey = this.draftKey;
            const hasDraft = this.drafts && this.drafts[draftKey] && this.drafts[draftKey].trim() !== '';
            if (hasDraft) {
                const shouldLeave = await this.showHasDraftPopup(this.$t("You haven't finished your comment yet. Do you want to leave without finishing?"));
                if (!shouldLeave) return;
            }
			this.dialogRef.close();
        },
        onMaskClick(e) {
            if (e.target.classList.contains('p-dialog-mask')) {
                this.handleBeforeClose();
            }
        },
        cleanupListeners() {
            const maskEl = document.querySelector('.p-dialog-mask');
            if (maskEl) {
                maskEl.removeEventListener('mousedown', this.onMaskClick, true);
            }
            if (this.keydownHandler) {
                document.removeEventListener('keydown', this.keydownHandler);
                this.keydownHandler = null;
            }
        }
    }
}
</script>