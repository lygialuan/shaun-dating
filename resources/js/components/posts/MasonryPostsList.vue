<template>
    <div>
        <SkeletonMasonryPostsList v-if="loading" />
        <template v-else>
            <div v-if="postsList.length > 0" class="grid grid-cols-[repeat(auto-fill,minmax(260px,1fr))] gap-1">
                <TransitionGroup name="fade">
                    <MasonryPostListsItem v-for="post in postsList" :key="post.id" :post="post" />
                </TransitionGroup>
                <InfiniteLoading v-if="loadMore" @infinite="handleLoadInfinite">
                    <template #spinner>
                        <Loading />
                    </template>
                    <template #complete><span></span></template>
                </InfiniteLoading>
            </div>
            <template v-else>
                <slot name="empty">
                    <div class="main-content-section">
                        <div class="p-5 text-center">
                            <div class="text-base-lg font-bold">{{ $t('Nothing to see here yet') }}</div>
                        </div>
                    </div>
                </slot>
            </template>
        </template>
    </div>
</template>

<script>
import SkeletonMasonryPostsList from '@/components/skeletons/SkeletonMasonryPostsList.vue'
import MasonryPostListsItem from '@/components/posts/MasonryPostListsItem.vue'
import InfiniteLoading from 'v3-infinite-loading'
import Loading from '@/components/utilities/Loading.vue'

export default {
    components: { SkeletonMasonryPostsList, MasonryPostListsItem, InfiniteLoading, Loading },
    props:{
        loading: {
            type: Boolean,
            default: true
        },
        postsList: {
            type: Array,
            default: () => []
        },
        loadMore: {
            type: Boolean,
            default: true
        }
    },
    methods:{
        handleLoadInfinite(state){
            if(this.loadMore){
                this.$emit('load-more', state)
            }
        }
    },
    emits: ['load-more']
}
</script>