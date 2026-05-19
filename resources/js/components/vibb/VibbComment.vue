<template>
    <Transition :name="transitionName">
        <div v-if="showVibbComment" 
            class="h-full lg:py-base-2 lg:pe-4 md:w-96"
            :class="{
                'absolute bottom-0 start-0 end-0 max-h-[60vh] z-[1003]': screen.md,
                'absolute top-1/2 start-1/2 -translate-x-1/2 -translate-y-1/2 max-h-[70vh] z-[1003]': screen.lg && !screen.md
            }"
            >
            <div class="flex flex-col bg-white w-full h-full rounded-md overflow-hidden dark:bg-slate-800">
                <div v-if="currentVibb" class="flex gap-base-2 px-4 py-base-2">
                    <h3 class="text-lg font-bold flex-1">{{ $filters.numberShortener(currentVibb.comment_count, $t('[number] comment'), $t('[number] comments')) }}</h3>
                    <button @click="handleToggleComment">
                        <BaseIcon name="close" />
                    </button>
                </div>
                <CommentsList v-if="currentVibb" :item="currentVibb" :emptyMessage="$t('No comments')" :has-menu-footer="false" :in-modal="screen.md ? true : false" comments-list-class="overflow-auto scrollbar-hidden scroll-smooth" />
            </div>
        </div>
    </Transition>
    <div v-if="showVibbComment && screen.lg" class="fixed inset-0 bg-black-trans-75 z-[1002]" @click="setShowVibbComment(false)"></div>
</template>

<script>
import { mapState, mapActions } from "pinia";
import { useAppStore } from "@/store/app";
import { useVibbStore } from "@/store/vibb";
import BaseIcon from '@/components/icons/BaseIcon.vue'
import CommentsList from '@/components/comments/CommentsList.vue'

export default {
    components:{
        BaseIcon,
        CommentsList
    },
    data(){
        return{
            vibbCommentWidth: 1
        }
    },
    computed:{
        ...mapState(useAppStore, ['screen']),
        ...mapState(useVibbStore, ['currentVibb', 'showVibbComment']),
        transitionName() {
            if (this.screen.md) return 'slide-up';
            if (this.screen.lg) return 'p-dialog';
            return 'slide-right';
        }
    },
    unmounted() {
        this.setShowVibbComment(false);
    },
    methods:{
        ...mapActions(useVibbStore, ["setShowVibbComment"]),
        handleToggleComment(){
            this.setShowVibbComment(!this.showVibbComment)
        }
    }
}
</script>