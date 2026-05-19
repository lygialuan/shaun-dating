<template>
    <SkeletonPreviewVibbsList v-if="loading" class="pb-5"/>
    <template v-else>
        <div v-if="vibbsList.length > 0" class="grid grid-cols-[repeat(auto-fill,minmax(180px,1fr))] gap-base-2 pb-5">
            <TransitionGroup name="fade">
                <VibbPreviewItem v-for="vibb in vibbsList" :key="vibb.id" :item="vibb" :subject-id="subjectId" :subject-type="subjectType" />
            </TransitionGroup>
            <InfiniteLoading @infinite="handleLoadInfinite">
                <template #spinner>
                    <Loading />
                </template>
                <template #complete><span></span></template>
            </InfiniteLoading>
        </div>
        <div v-else>
            <slot name="empty">
                <div class="main-content-section">
                    <div class="p-5 text-center">
                        <div class="text-base-lg font-bold mb-base-2">{{ $t('Nothing to see here yet') }}</div>
                        <div>{{ $t('Start following people and tags to see updated posts') }}</div>
                    </div>
                </div>
            </slot>
        </div>
    </template>
</template>

<script>
import SkeletonPreviewVibbsList from '@/components/skeletons/SkeletonPreviewVibbsList.vue';
import VibbPreviewItem from '@/components/vibb/VibbPreviewItem.vue'
import InfiniteLoading from 'v3-infinite-loading'
import Loading from '@/components/utilities/Loading.vue'

export default {
    components: { SkeletonPreviewVibbsList, VibbPreviewItem, InfiniteLoading, Loading },
    props:{
        loading: {
            type: Boolean,
            default: true
        },
        vibbsList: {
            type: Array,
            default: () => []
        },
        subjectId: {
            type: Number,
            default: 0
        },
        subjectType: {
            type: String,
            default: ''
        }
    },
    methods:{
        handleLoadInfinite(state){
            this.$emit('load-more', state)
        }
    },
    emits: ['load-more']
}
</script>
