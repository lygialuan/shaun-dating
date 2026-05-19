<template>
    <div ref="postsListRef" class="pb-5 scroll-mt-[100px]">
        <div v-if="isShowNotifications" class="sticky top-20 inset-x-0 h-0 text-center z-[999] translate-y-4">
            <button @click="handleShowNewPosts" class="post-notifications inline-block bg-primary-color shadow-lg text-white dark:bg-dark-primary-color px-3 py-2 rounded-1000">
                <div class="flex gap-x-2">
                    <BaseIcon name="arrow_clockwise" />
                    {{ $t('Show new Posts') }}
                </div>
            </button>
        </div>
        <SkeletonPostsList v-if="loading" />
        <template v-else>
            <template v-if="postsList.length > 0">
                <TransitionGroup name="fade">
                    <PostListsItem v-for="post in postsList" :key="post.id" :post="post" :shadow="shadow" />
                </TransitionGroup>
                <InfiniteLoading v-if="loadMore" @infinite="handleLoadInfinite">
                    <template #spinner>
                        <Loading />
                    </template>
                    <template #complete><span></span></template>
                </InfiniteLoading>
            </template>
            <template v-else >
                <slot name="empty">
                    <div class="main-content-section">
                        <div class="p-5 text-center">
                            <div class="text-base-lg font-bold mb-base-2">{{ $t('Nothing to see here yet') }}</div>
                            <div>{{ $t('Start following people and tags to see updated posts') }}</div>
                        </div>
                    </div>
                </slot>
            </template>
        </template>
    </div>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { useAppStore } from '@/store/app'
import { useActionStore } from '@/store/action'
import { getPostNotifications } from '@/api/posts'
import SkeletonPostsList from '@/components/skeletons/SkeletonPostsList.vue'
import PostListsItem from '@/components/posts/PostListsItem.vue'
import InfiniteLoading from 'v3-infinite-loading'
import Loading from '@/components/utilities/Loading.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'

export default {
    components: { SkeletonPostsList, PostListsItem, InfiniteLoading, Loading, BaseIcon },
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
        },
        shadow: {
            type: Boolean,
            default: false
        },
        showNotifications: {
            type: Boolean,
            default: false
        }
    },
    data(){
        return{
            hasNotifications: false
        }
    },
    computed: {
        ...mapState(useAppStore, ['config']),
        latestPostId() {
            if (this.postsList.length) {
                return this.postsList[0].id
            }
            return 0
        },
        isShowNotifications() {
            return this.showNotifications && this.hasNotifications
        }
    },
    mounted() {
        if(this.showNotifications){
            this._postNotifyInterval = setInterval(() => {
                this.handleGetPostNotifications()
            }, this.config.notificationInterval)
        }
    },
    beforeUnmount() {
        if(this.showNotifications){
            if (this._postNotifyInterval) {
                clearInterval(this._postNotifyInterval)
                this._postNotifyInterval = null
            }
        }
    },
    methods:{
        ...mapActions(useActionStore, ['doSamePage']),
        async handleGetPostNotifications(){
            try {
                const response = await getPostNotifications(this.latestPostId)
                this.hasNotifications = response.new_post
            } catch (error) {
                this.showError(error.error)
            }
        },
        handleShowNewPosts(){
            this.hasNotifications = false
            this.doSamePage({status: true})
            
            this.$nextTick(() => {
                this.$refs.postsListRef.scrollIntoView({ behavior: 'smooth' });
            });
        },
        handleLoadInfinite(state){
            if(this.loadMore){
                this.$emit('load-more', state)
            }
        }
    },
    emits: ['load-more']
}
</script>