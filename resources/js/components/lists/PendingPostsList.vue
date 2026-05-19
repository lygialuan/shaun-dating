<template>
    <div class="pb-5">
        <SkeletonPostsList v-if="loading" />
        <template v-else>
            <template v-if="postsList.length > 0">
                <TransitionGroup name="fade">
                    <div v-for="item in postsList" :key="item.id" class="feed-item">
                        <div class="feed-item-header">
                            <div class="feed-item-header-img">
                                <Avatar :user="item.post.user"/>
                            </div>
                            <div class="feed-item-header-info flex-grow ps-base-2">
                                <div class="feed-item-header-info-title flex justify-between items-start gap-base-2">
                                    <div class="whitespace-normal">
                                        <UserName :user="item.post.user" :truncate="false" />
                                    </div>
                                </div>
                                <div class="feed-item-header-info-date flex gap-base-1 items-center w-max text-sub-color text-xs dark:text-slate-400">
                                    <router-link :to="{name: 'post', params: {id: item.post.id}}" class="text-inherit">{{item.post.created_at}}</router-link>
                                </div>
                            </div>
                        </div>
                        <div class="feed-item-content">
                            <ContentHtml 
                                class="feed-item-content-message px-base-2 md:px-4 mb-base-2" 
                                :content="item.post.content" 
                                :mentions="item.post.mentions"
                                :can-translate="item.post.canContentTranslate"
                                :subject-id="item.post.id"
                                subject-type="posts"
                            />
                            <div class="activity_feed_content_item">
                                <PostContentType :post="item.post" />
                            </div>
                        </div>
                        <div class="px-base-2 pb-base-2 mt-4 md:pb-4 md:px-4">
                            <slot name="actions" :item="item"></slot>
                        </div>
                    </div>
                </TransitionGroup>
                <InfiniteLoading v-if="loadMore" @infinite="handleLoadInfinite">
                    <template #spinner>
                        <Loading />
                    </template>
                    <template #complete><span></span></template>
                </InfiniteLoading>
            </template>
            <div v-else class="main-content-section">
                <slot name="empty">
                    <div class="p-5 text-center">
                        <div class="text-base-lg font-bold mb-base-2">{{ $t('Nothing to see here yet') }}</div>
                        <div>{{ $t('Start following people and tags to see updated posts') }}</div>
                    </div>
                </slot>
            </div>
        </template>
    </div>
</template>

<script>
import SkeletonPostsList from '@/components/skeletons/SkeletonPostsList.vue'
import InfiniteLoading from 'v3-infinite-loading'
import Loading from '@/components/utilities/Loading.vue'
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'
import ContentHtml from '@/components/utilities/ContentHtml.vue'
import PostContentType from '@/components/posts/PostContentType.vue'

export default {
    components: { SkeletonPostsList, InfiniteLoading, Loading, Avatar, UserName, ContentHtml, PostContentType },
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