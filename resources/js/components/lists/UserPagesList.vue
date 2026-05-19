<template>
    <SkeletonPagesList v-if="loading" />
    <template v-else>
        <template v-if="pagesListData.length">
            <div v-if="listStyle === 'list'" class="users-list space-y-base-2">
                <div v-for="page in pagesListData" :key="page.id" class="users-list-item flex items-center gap-base-2">
                    <Avatar :user="page" :target="target" :activePopover="showPopover" class="users-list-item-avatar" />
                    <div class="users-list-item-content flex-1 min-w-0">
                        <UserName :user="page" :activePopover="showPopover" :target="target" class="users-list-item-content-title list_items_title_text_color" />
                        <div v-if="page.categories.length" class="users-list-item-content-sub list_items_sub_text_color text-xs leading-5 truncate text-sub-color dark:text-slate-400">
                            <span v-for="(category, index) in page.categories" :key="category.id">
                                <router-link :to="{name: 'user_pages', query: {category_id: category.id}}" class="text-inherit">{{ category.name }}</router-link>
                                {{ (index === page.categories.length - 1) ? '' : ' · '}}
                            </span>
                        </div>
                        <div v-if="page.show_follower" class="users-list-item-content-sub list_items_sub_text_color text-xs text-sub-color dark:text-slate-400">{{ $filters.numberShortener(page.follower_count, $t('[number] Follower'), $t('[number] Followers')) }}</div>
                    </div>
                    <div v-if="page.can_follow">
                        <button v-if="page.is_followed" class="list_items_button text-xs text-primary-color cursor-pointer font-bold dark:text-dark-primary-color" @click="unFollowPage(page)">{{$t('Unfollow')}}</button>
                        <button v-else class="list_items_button text-xs text-primary-color cursor-pointer font-bold dark:text-dark-primary-color" @click="followPage(page)">{{$t('Follow')}}</button>
                    </div>
                </div>
            </div>
            <div v-else-if="listStyle === 'grid'" class="users-grid grid grid-cols-[repeat(auto-fill,minmax(260px,1fr))] gap-base-2">     
                <div v-for="page in pagesListData" :key="page.id" class="grid-item rounded-lg border border-divider dark:border-white/10 h-full">
                    <router-link :to="{name: 'profile', params: {user_name: page.user_name}}" class="block pb-[35%] bg-cover bg-center bg-no-repeat rounded-t-lg" :style="{ backgroundImage: `url(${page.cover})`}"></router-link>
                    <div class="p-base-2">
                        <div class="flex gap-base-2">
                            <Avatar :user="page" :size="50" class="users-list-item-avatar" />
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap gap-2 items-center mb-1"> 
                                    <UserName :user="page" class="grid-item-title" />
                                </div>
                                <div v-if="page.categories.length" class="grid-item-sub text-xs mb-1 truncate text-sub-color dark:text-slate-400">
                                    <span v-for="(category, index) in page.categories" :key="category.id">
                                        <router-link :to="{name: 'user_pages', query: {category_id: category.id}}" class="text-inherit">{{ category.name }}</router-link>
                                        {{ (index === page.categories.length - 1) ? '' : ' · '}}
                                    </span>
                                </div>
                                <div v-if="page.show_follower" class="grid-item-sub text-xs text-sub-color dark:text-slate-400">{{ $filters.numberShortener(page.follower_count, $t('[number] Follower'), $t('[number] Followers')) }}</div>
                            </div>
                        </div>
                        <div v-if="page.can_follow" class="mt-base-2">
                            <BaseButton v-if="page.is_followed" @click="unFollowPage(page)" type="outlined" fluid>{{$t('Unfollow')}}</BaseButton>
                            <BaseButton v-else @click="followPage(page)" type="outlined" fluid>{{$t('Follow')}}</BaseButton>
                        </div>
                    </div>
                </div>
            </div>
            <SlimScroll v-else-if="listStyle === 'slider'">
                <div v-for="page in pagesListData" :key="page.id" class="users-list-item rounded-lg border border-divider dark:border-white/10 h-full flex-shrink-0 w-72 md:w-[326px]">
                    <router-link :to="{name: 'profile', params: {user_name: page.user_name}}" class="block pb-[35%] bg-cover bg-center bg-no-repeat rounded-t-lg" :style="{ backgroundImage: `url(${page.cover})`}"></router-link>
                    <div class="p-base-2">
                        <div class="flex gap-base-2">
                            <Avatar :user="page" :activePopover="false" :size="50"/>
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap gap-2 items-center mb-1"> 
                                    <UserName :user="page" class="grid-item-title" />
                                </div>
                                <div v-if="page.categories.length" class="grid-item-sub text-xs mb-1 truncate text-sub-color dark:text-slate-400">
                                    <span v-for="(category, index) in page.categories" :key="category.id">
                                        <router-link :to="{name: 'user_pages', query: {category_id: category.id}}" class="text-inherit">{{ category.name }}</router-link>
                                        {{ (index === page.categories.length - 1) ? '' : ' · '}}
                                    </span>
                                </div>
                                <div v-if="page.show_follower" class="grid-item-sub text-xs text-sub-color dark:text-slate-400">{{ $filters.numberShortener(page.follower_count, $t('[number] Follower'), $t('[number] Followers')) }}</div>
                            </div>
                        </div>
                        <div v-if="page.can_follow" class="mt-base-2">
                            <BaseButton v-if="page.is_followed" @click="unFollowPage(page)" type="outlined" fluid>{{$t('Unfollow')}}</BaseButton>
                            <BaseButton v-else @click="followPage(page)" type="outlined" fluid>{{$t('Follow')}}</BaseButton>
                        </div>
                        <div v-else class="h-base-9 mt-base-2"></div>
                    </div>
                </div>
            </SlimScroll>
            <template v-if="autoLoadMore">
                <InfiniteLoading v-if="hasLoadMore" @infinite="handleLoadInfinite">				
                    <template #spinner>
                        <SkeletonPagesList />
                    </template>
                    <template #complete><span></span></template>
                </InfiniteLoading>
            </template>
            <template v-else>
                <div v-if="hasLoadMore" class="text-center mt-4">
                    <BaseButton @click="handleLoadmore">{{ $t('View more') }}</BaseButton>
                </div>
            </template>
        </template>
        <template v-else>
            <slot name="empty">
                <div class="text-center">
                    <div>{{ $t('No Pages') }}</div>
                </div>
            </slot>
        </template>
    </template>
</template>
<script>
import { mapState, mapActions } from 'pinia'
import { toggleFollowUser } from '@/api/follow'
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import { useAuthStore } from '@/store/auth'
import { useActionStore } from '@/store/action'
import SkeletonPagesList from '@/components/skeletons/SkeletonPagesList.vue'
import InfiniteLoading from 'v3-infinite-loading'
import SlimScroll from '@/components/utilities/SlimScroll.vue'

export default {
    components: { Avatar, UserName, BaseButton, SkeletonPagesList, InfiniteLoading, SlimScroll },
    props: {
        pagesList: {
            type: Array,
            default: null
        },
        loading: {
            type: Boolean,
            default: true
        },
        hasLoadMore: {
            type: Boolean,
            default: false
        },
        autoLoadMore: {
            type: Boolean,
            default: false
        },
        listStyle:{
            type: String,
            default: 'grid'
        },
        positionWidget: {
            type: String,
            default: ''
        }
    },
    data(){
        return{
            pagesListData:  window._.clone(this.pagesList)
        }
    },
    computed: {
        ...mapState(useAuthStore, ["authenticated"]),
        ...mapState(useActionStore, ['userAction'])
    },
    watch: {
        userAction(){
            this.pagesListData.map(page => {
                if(page.id === this.userAction.pageId){
                    if(this.userAction.status === 'follow'){
                        page.is_followed = true
                    }else if(this.userAction.status === 'unfollow'){
                        page.is_followed = false
                    }
                }
                return page
            })
        },
        pagesList(){
            this.pagesListData = window._.clone(this.pagesList)
        }
    },
    methods: {
        ...mapActions(useActionStore, ['updateFollowStatus']),
        async followPage(page) {
            if(this.authenticated){
                try {
                    await toggleFollowUser({
                        id: page.id,
                        action: "follow"
                    });
                    this.pagesListData.map(pageItem => {
                        if (pageItem.id === page.id) {
                            pageItem.is_followed = true;
                        }
                        return pageItem;
                    });
                    this.updateFollowStatus({pageId: page.id, status: 'follow'})
                    this.$emit('follow_page', page)
                }
                catch (error) {
                    this.showError(error.error)
                }
            }else{
                this.showRequireLogin()
            }
        },
        async unFollowPage(page) {
            try {
                await toggleFollowUser({
                    id: page.id,
                    action: "unfollow"
                });
                this.pagesListData.map(pageItem => {
                    if (pageItem.id === page.id) {
                        pageItem.is_followed = false;
                    }
                    return pageItem;
                });
                this.updateFollowStatus({pageId: page.id, status: 'unfollow'})
                this.$emit('unfollow_page', page)
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
    emits: ['follow_page', 'unfollow_page', 'load-more']
} 
</script>