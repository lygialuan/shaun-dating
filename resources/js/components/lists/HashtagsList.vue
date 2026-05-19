<template>
    <SkeletonUsersList v-if="loading" />
    <template v-else>
        <div v-if="hashtagsList.length" class="space-y-base-2">
            <div v-for="hashtagListItem in hashtagsList" :key="hashtagListItem.name" class="users-list-item flex items-center gap-base-2">
                <router-link :to="{name: 'search', params: {search_type: 'hashtag', type: 'post'}, query: {q: hashtagListItem.name}}" :target="target" class="text-base-none text-main-color dark:text-white">
                    <div class="flex items-center justify-center w-10 h-10 border border-divider rounded-full">
                        <BaseIcon name="hashtag" />
                    </div>
                </router-link>
                <router-link :to="{name: 'search', params: {search_type: 'hashtag', type: 'post'}, query: {q: hashtagListItem.name}}" :target="target" class="flex-1 text-inherit">
                    <div class="list_items_title_text_color text-base-sm font-semibold break-word truncate-2">{{hashtagListItem.name}}</div>
                    <div class="list_items_sub_text_color text-xs text-sub-color dark:text-slate-400">{{ $filters.numberShortener(hashtagListItem.post_count, $t('[number] post'), $t('[number] posts')) }}</div>
                </router-link>
                <button v-if="hashtagListItem.is_followed" class="list_items_button text-xs text-primary-color cursor-pointer font-bold dark:text-dark-primary-color" @click="unFollowHashtag(hashtagListItem.name)">{{$t('Unfollow')}}</button>
                <button v-else class="list_items_button text-xs text-primary-color cursor-pointer font-bold dark:text-dark-primary-color" @click="followHashtag(hashtagListItem.name)">{{$t('Follow')}}</button>
            </div>
            <template v-if="autoLoadMore">
                <InfiniteLoading v-if="hasLoadMore" @infinite="handleLoadInfinite">				
                    <template #spinner>
                        <SkeletonUsersList :item-number="2" />
                    </template>
                    <template #complete><span></span></template>
                </InfiniteLoading>
            </template>
            <template v-else>
                <div v-if="hasLoadMore" class="text-center mt-base-2">
                    <BaseButton @click="handleLoadmore">{{ $t('View more') }}</BaseButton>
                </div>
            </template>
        </div>
        <template v-else>
            <slot name="empty">
                <div class="text-center">{{ $t('No Tags') }}</div>
            </slot>
        </template>
    </template>
</template>
<script>
import { toggleFollowHashtag } from '@/api/follow';
import BaseIcon from '@/components/icons/BaseIcon.vue';
import SkeletonUsersList from '@/components/skeletons/SkeletonUsersList.vue'
import BaseButton from '@/components/inputs/BaseButton.vue';
import InfiniteLoading from 'v3-infinite-loading'

export default {
	components:{ 
        BaseIcon,
        SkeletonUsersList,
        BaseButton,
        InfiniteLoading
    },
    props: {
        hashtagsList: {
            type: Array,
            default: null
        },
        target: {
            type: String,
            default: ''
        },
        loading: {
            type: Boolean,
            default: false
        },
        hasLoadMore: {
            type: Boolean,
            default: false
        },
        autoLoadMore: {
            type: Boolean,
            default: false
        }
    },
	methods: {
        async followHashtag(hashtagName){
            try {
                await toggleFollowHashtag({name: hashtagName, action: 'follow'});
                this.hashtagsList.map(hashtagItem => {
                    if (hashtagItem.name === hashtagName) {
                        hashtagItem.is_followed = true
                    }
                    return hashtagItem
                });
                this.$emit('follow_hashtag', hashtagName)
            }
            catch (error) {
                this.showError(error.error)
            }
        },
        async unFollowHashtag(hashtagName){
            try {
                await toggleFollowHashtag({name: hashtagName, action: 'unfollow'});
                this.hashtagsList.map(hashtagItem => {
                    if (hashtagItem.name === hashtagName) {
                        hashtagItem.is_followed = false
                    }
                    return hashtagItem
                });
                this.$emit('unfollow_hashtag', hashtagName)
            }
            catch (error) {
                this.showError(error.error)
            }
        },
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
    emits: ['follow_hashtag', 'unfollow_hashtag', 'load-more']
}
</script>