<template>
    <VibbContent v-if="post.type === 'vibb'" :item="post" class="mb-base-2" />
    <div
        v-else
        class="feed-item feed-item-hover hover:bg-feed-hover hover:border-feed-hover dark:hover:bg-feed-hover dark:hover:border-feed-hover cursor-pointer"
        :class="{'shadow-post-item dark:shadow-post-item-dark': shadow}"
        @click="handleClickFeed"
    >
        <PostContent :post="post" @comment_click="showPostDetail" @comment_count_click="showPostDetail" />
    </div>
</template>

<script>
import { mapState } from 'pinia';
import { checkPopupBodyClass, changeUrl } from '@/utility/index'
import { useAppStore } from '@/store/app'
import PostDetailModal from '@/components/posts/PostDetailModal.vue'
import PostContent from '@/components/posts/PostContent.vue'
import VibbContent from '@/components/posts/VibbContent.vue'

export default {
    components: {  PostContent, VibbContent },
    props: {
        post: Object,
        shadow: {
            type: Boolean,
            default: false
        }
    },
    computed:{
        ...mapState(useAppStore, ['setOpenedModalCount'])
    },
    methods: {
        showPostDetail(){
            let postUrl = this.$router.resolve({
                name: 'post',
                params: { 'id': this.post.id }
            });
            changeUrl(postUrl.fullPath)
            this.setOpenedModalCount()
            this.$dialog.open(PostDetailModal, {
                data: {
                    post: this.post
                },
                props:{
                    class: 'post-detail-modal p-dialog-lg',
                    modal: true,
                    showHeader: false,
                    draggable: false,
                    dismissableMask: false,
                    closeOnEscape: false
                },
                onClose: () => {
                    changeUrl(this.$router.currentRoute.value.fullPath)
                    checkPopupBodyClass();
                    this.setOpenedModalCount(false)
                }
            });
        },
        handleClickFeed(event) {
            // Prevent if user is selecting text
            const selection = window.getSelection();
            if (selection && selection.type === 'Range' && selection.toString().length > 0) {
                return;
            }

            // Prevent if user is clicking inside buttons or links
            let el = event.target;
            while (el && el !== event.currentTarget) {
                if (
                    el.tagName === 'BUTTON' ||
                    el.tagName === 'A' ||
                    el.getAttribute('role') === 'button'
                ) {
                    return;
                }
                el = el.parentElement;
            }
            this.showPostDetail()
        }
    }
}
</script>