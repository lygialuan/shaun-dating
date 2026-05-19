<template>
    <SkeletonGroupsList v-if="loading" />
    <template v-else>
        <template v-if="groupsList.length">
            <div v-if="listStyle === 'list'" class="groups-list space-y-base-2">
                <div v-for="group in groupsList" :key="group.id" class="groups-list-item flex items-center gap-base-2">
                    <router-link :to="{name: 'group_profile', params: { id: group.id, slug: group.slug }}" class="w-10 h-10 rounded-full border border-divider dark:border-slate-700">
                        <img :src="group.cover" :alt="group.name" class="rounded-full w-full h-full object-cover">
                    </router-link>
                    <div class="flex-1 min-w-0">
                        <GroupName :group="group" class="!block truncate" />
                        <div class="list_items_sub_text_color flex items-center text-xs text-sub-color dark:text-slate-400">{{ $filters.numberShortener(group.member_count, $t('[number] member'), $t('[number] members')) }}</div> 
                    </div>
                </div>
            </div>
            <div v-else-if="listStyle === 'grid'" class="groups-grid grid grid-cols-[repeat(auto-fill,minmax(260px,1fr))] gap-base-2">
                <GroupListItem v-for="group in groupsList" :key="group.id" :item="group" :show-button="showButton" :show-badge="showBadge" />
            </div>
            <SlimScroll v-else-if="listStyle === 'slider'">
                <GroupListItem v-for="group in groupsList" :key="group.id" :item="group" :show-button="showButton" :show-badge="showBadge" class="flex-shrink-0 w-48 md:w-72" />
            </SlimScroll>
            <template v-if="autoLoadMore">
                <InfiniteLoading v-if="hasLoadMore" @infinite="handleLoadInfinite">				
                    <template #spinner>
                        <Loading />
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
        <template v-else>
            <slot name="empty">
                <div class="bg-white rounded-none md:rounded-base-lg p-5 text-center dark:bg-dark-form-base">{{ $t('No groups are found') }}</div>
            </slot>
        </template>
    </template>
</template>

<script>
import SkeletonGroupsList from '@/components/skeletons/SkeletonGroupsList.vue'
import GroupListItem from '@/components/group/GroupListItem.vue';
import InfiniteLoading from 'v3-infinite-loading'
import Loading from '@/components/utilities/Loading.vue'
import GroupName from '@/components/group/GroupName.vue'
import BaseButton from '@/components/inputs/BaseButton.vue';
import SlimScroll from '@/components/utilities/SlimScroll.vue'

export default {
    props:{
        loading: {
            type: Boolean,
            default: true
        },
        groupsList: {
            type: Array,
            default: () => []
        },
        showButton: {
            type: Boolean,
            default: true
        },
        showBadge: {
            type: Boolean,
            default: false
        },
        listStyle:{
            type: String,
            default: 'grid'
        },
        hasLoadMore: {
            type: Boolean,
            default: true
        },
        autoLoadMore: {
            type: Boolean,
            default: true
        }
    },
    components: { SkeletonGroupsList, InfiniteLoading, GroupListItem, Loading, GroupName, BaseButton, SlimScroll },
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
        }
    },
    emits: ['load-more']
} 
</script>