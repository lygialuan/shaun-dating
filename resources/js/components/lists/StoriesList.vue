<template>
    <SkeletonPreviewVibbsList v-if="loading"/>
    <template v-else>
        <div v-if="list.length" class="grid grid-cols-[repeat(auto-fill,minmax(180px,1fr))] gap-base-2">
            <div v-for="story in list" :key="story.id" class="relative pb-[162.5%] overflow-hidden rounded-lg cursor-pointer" @click="showStoryDetail(story.id)">
                <StoryContentPreview :story="story" class="text-lg" />
            </div>
        </div>
        <div v-else class="text-center">
            <slot name="empty">
                <div>{{ $t('No Stories') }}</div>
            </slot>
        </div>
    </template>
    <template v-if="autoLoadMore">
        <InfiniteLoading v-if="hasLoadMore" @infinite="handleLoadInfinite">				
            <template #spinner>
                <SkeletonPreviewVibbsList />
            </template>
            <template #complete><span></span></template>
        </InfiniteLoading>
    </template>
    <template v-else>
        <div v-if="hasLoadMore" class="text-center mt-base-2">
            <BaseButton @click="handleLoadmore">{{ $t('View more') }}</BaseButton>
        </div>
    </template>
</template>

<script>
import { checkPopupBodyClass, changeUrl } from '@/utility/index'
import SkeletonPreviewVibbsList from '@/components/skeletons/SkeletonPreviewVibbsList.vue';
import InfiniteLoading from 'v3-infinite-loading'
import BaseButton from '@/components/inputs/BaseButton.vue';
import StoryContentPreview from '@/components/stories/StoryContentPreview.vue';
import StoryItemDetailModal from '@/components/stories/StoryItemDetailModal.vue'

export default {
    components: { InfiniteLoading, SkeletonPreviewVibbsList, BaseButton, StoryContentPreview },
    props:{
        loading: {
            type: Boolean,
            default: true
        },
        list: {
            type: Array,
            default: () => []
        },
        hasLoadMore: {
            type: Boolean,
            default: true
        },
        autoLoadMore: {
            type: Boolean,
            default: false
        }
    },
    methods: {
        handleLoadmore(){
            if(this.hasLoadMore){
                this.$emit('load-more')
            }
        },
        handleLoadInfinite(state){
            if(this.hasLoadMore && this.autoLoadMore){
                this.$emit('load-more', state)
            }
        },
        showStoryDetail(storyItemId){
            let storyUrl = this.$router.resolve({
                name: 'story_view_item',
                params: { 'storyItemId': storyItemId }
            });
            changeUrl(storyUrl.fullPath)
            this.$dialog.open(StoryItemDetailModal, {
                data: {
                    storyItemId: storyItemId
                },
                props:{
                    class: 'p-dialog-story p-dialog-story-detail p-dialog-no-header-title',
                    modal: true,
                    showHeader: false,
                    draggable: false
                },
                onClose: () => {
                    changeUrl(this.$router.currentRoute.value.fullPath)
                    checkPopupBodyClass();
                }
            });
        }
    },
    emits: ['load-more']
}
</script>