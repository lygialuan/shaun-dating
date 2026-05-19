<template>
    <button
        class="flex items-center justify-center gap-2"
        :class="[ subject.is_bookmarked ? 'text-primary-color dark:text-dark-primary-color is-bookmarked' : '', buttonActive ? 'active' : '' ]"
        @click.stop="handleToggleBookmark()"
    >
        <BaseIcon :name="subject.is_bookmarked ? 'bookmark_fill' : 'bookmarks'" :size="size" />
        <slot name="text"></slot>
    </button>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { useAuthStore } from '@/store/auth'
import { useBookmarkStore } from '@/store/bookmark';
import BaseIcon from '@/components/icons/BaseIcon.vue'

export default {
    props: {
        subject: {
            type: Object,
            default: null
        },
        subjectType: {
            type: String,
            default: 'posts'
        },
        size: {
            type: Number,
            default: 24
        }
    },
    components: {
        BaseIcon
    },
    data(){
        return {
            buttonActive: false,
            loadingBookmark: false
        }
    },
    computed: {
        ...mapState(useAuthStore, ['authenticated'])
    },
    methods: {
        ...mapActions(useBookmarkStore, ['toggleBookmark']),
        async handleToggleBookmark(){
            if (!this.authenticated) {
                return this.showRequireLogin()
            }

            if(this.loadingBookmark){
                return;
            }
            this.loadingBookmark = true;

            try {
                await this.toggleBookmark({
                    subject_type: this.subjectType,
                    subject_id: this.subject.id,
                    action: this.subject.is_bookmarked ? 'remove' : 'add'
                })
                this.buttonActive = this.subject.is_bookmarked;
            } catch (error) {
                this.showError(error.error)
            } finally {
                this.loadingBookmark = false;
            }
        }
    }
}
</script>