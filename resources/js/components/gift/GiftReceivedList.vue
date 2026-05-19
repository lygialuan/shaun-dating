<template>
    <div>
        <SkeletonGiftReceivedList v-if="loading" />
        <template v-else>
            <div v-if="giftsList.length > 0" class="grid grid-cols-[repeat(auto-fill,minmax(260px,1fr))] gap-1">
                <TransitionGroup name="fade">
                    <div class="w-full overflow-x-auto">
                        <table class="min-w-full rounded-lg overflow-hidden">
                            <thead class="bg-gray-100 dark:bg-gray-600">
                                <tr>
                                    <th class="px-2 py-3 text-left">{{ $t('Gift') }}</th>
                                    <th class="px-2 py-3 text-left">{{ $t('Sender') }}</th>
                                    <th class="px-2 py-3 text-left">{{ $t('Date') }}</th>
                                </tr>
                            </thead>
                            <GiftReceivedItem v-for="gift in giftsList" :key="gift.id" :gift="gift" />
                        </table>
                    </div>
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
import SkeletonGiftReceivedList from '@/components/skeletons/SkeletonGiftReceivedList.vue'
import GiftReceivedItem from '@/components/gift/GiftReceivedItem.vue'
import InfiniteLoading from 'v3-infinite-loading'
import Loading from '@/components/utilities/Loading.vue'

export default {
    components: { SkeletonGiftReceivedList, GiftReceivedItem, InfiniteLoading, Loading },
    props:{
        loading: {
            type: Boolean,
            default: true
        },
        giftsList: {
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