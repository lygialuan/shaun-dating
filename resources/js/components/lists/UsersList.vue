<template>
    <SkeletonUsersList v-if="loading" />
    <template v-else>
        <div v-if="usersListData.length > 0" :class="`users_list_${positionWidget}`">
            <div v-if="listStyle === 'list'" class="users-list space-y-base-2">
                <div v-for="userItem in usersListData" :key="userItem.id" class="users-list-item flex items-center gap-base-2">
                    <Avatar :user="userItem" :target="target" :activePopover="showPopover" class="users-list-item-avatar" />
                    <div class="users-list-item-content flex-1 min-w-0">
                        <UserName :user="userItem" :activePopover="showPopover" :target="target" class="users-list-item-content-title list_items_title_text_color" />
                        <slot name="sub_text" :item="userItem">
                            <p class="users-list-item-content-sub list_items_sub_text_color text-xs text-sub-color mb-1 dark:text-slate-400 truncate">{{mentionChar + userItem.user_name}}</p>
                        </slot>
                    </div>
                    <slot name="actions" :item="userItem">
                        <template v-if="(user.id !== userItem.id) && userItem.can_follow">
                            <button v-if="userItem.is_followed" class="users-list-item-button list_items_button text-xs text-primary-color font-bold dark:text-dark-primary-color" @click="unFollowUser(userItem)">{{$t('Unfollow')}}</button>
                            <button v-else class="users-list-item-button list_items_button text-xs text-primary-color font-bold dark:text-dark-primary-color" @click="followUser(userItem)">{{$t('Follow')}}</button>
                        </template>
                    </slot>
                </div>
            </div>
            <div v-else-if="listStyle === 'grid'" class="users-grid grid grid-cols-[repeat(auto-fill,minmax(260px,1fr))] gap-4">
                <div v-for="userItem in usersListData" :key="userItem.id" class="users-grid-item grid-item flex flex-col gap-1 items-start bg-white border border-divider p-5 rounded-lg dark:bg-slate-800 dark:border-white/10">
                    <Avatar :user="userItem" :target="target" :activePopover="showPopover" :size="60" class="users-grid-item-avatar" />
                    <UserName :user="userItem" :activePopover="showPopover" :target="target" class="users-grid-item-content-title grid-item-title" />
                    <slot name="sub_text" :item="userItem">
                        <p class="users-grid-item-content-sub grid-item-sub list_items_sub_text_color text-xs text-sub-color mb-1 dark:text-slate-400 truncate">{{mentionChar + userItem.user_name}}</p>
                    </slot>
                    <slot name="actions" :item="userItem">
                        <div v-if="(user.id !== userItem.id) && userItem.can_follow" class="w-full">
                            <BaseButton v-if="userItem.is_followed" @click="unFollowUser(userItem)" fluid class="users-grid-item-button">{{ $t('Unfollow') }}</BaseButton>
                            <BaseButton v-else @click="followUser(userItem)" fluid class="users-grid-item-button">{{ $t('Follow') }}</BaseButton>
                        </div>
                    </slot>
                </div>
            </div>
            <SlimScroll v-else-if="listStyle === 'slider'">
                <div v-for="userItem in usersListData" :key="userItem.id" class="users-list-item border border-divider rounded-base-lg text-center relative p-base-2 dark:border-white/10 flex-shrink-0 w-36 md:w-44">
                    <Avatar :user="userItem" :target="target" :size="screen.md ? 100 : 130" :activePopover="showPopover" class="mx-auto" />
                    <div class="users-list-item-content flex flex-col items-center my-base-2">
                        <UserName :user="userItem" :activePopover="showPopover" :target="target" class="users-list-item-content-title list_items_title_text_color" />
                        <slot name="sub_text" :item="userItem">
                            <p class="users-list-item-content-sub list_items_sub_text_color text-xs text-sub-color mb-1 dark:text-slate-400 truncate">{{mentionChar + userItem.user_name}}</p>
                        </slot>
                    </div>
                    <slot name="actions" :item="userItem">
                        <template v-if="(user.id !== userItem.id) && userItem.can_follow">
                            <BaseButton v-if="userItem.is_followed" @click="unFollowUser(userItem)" size="sm" fluid>{{$t('Unfollow')}}</BaseButton>
                            <BaseButton v-else @click="followUser(userItem)" size="sm" fluid>{{$t('Follow')}}</BaseButton>
                        </template>
                    </slot>
                </div>
            </SlimScroll>
            <template v-if="autoLoadMore">
                <InfiniteLoading v-if="hasLoadMore" @infinite="handleLoadInfinite">				
                    <template #spinner>
                        <SkeletonUsersList :item-number="4" />
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
                <div class="text-center">
                    {{ $t('No Users') }}
                </div>
            </slot>
        </template>
    </template>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { toggleFollowUser } from '@/api/follow'
import { toggleBlockUser } from '@/api/user'
import { useAuthStore } from '@/store/auth'
import { useActionStore } from '@/store/action'
import { useAppStore } from '@/store/app'
import Constant from '@/utility/constant'
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'
import SlimScroll from '@/components/utilities/SlimScroll.vue'
import SkeletonUsersList from '@/components/skeletons/SkeletonUsersList.vue'
import BaseButton from '@/components/inputs/BaseButton.vue';
import InfiniteLoading from 'v3-infinite-loading'

export default {
    components: { UserName, Avatar, SlimScroll, SkeletonUsersList, BaseButton, InfiniteLoading },
    data(){
        return{
            mentionChar: Constant.MENTION,
            usersListData:  window._.clone(this.usersList)
        }
    },
    props: {
        usersList: {
            type: Array,
            default: null
        },
        target: {
            type: String,
            default: ''
        },
        showPopover: {
            type: Boolean,
            default: true
        },
        listStyle:{
            type: String,
            default: 'list'
        },
        positionWidget: {
            type: String,
            default: ''
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
        }
    },
    computed: {
        ...mapState(useAuthStore, ["user", "authenticated"]),
        ...mapState(useActionStore, ['userAction']),
        ...mapState(useAppStore, ['screen'])
    },
    watch: {
        userAction(){
            this.usersListData.map(user => {
                if(user.id === this.userAction.userId){
                    if(this.userAction.status === 'follow'){
                        user.is_followed = true
                    }else if(this.userAction.status === 'unfollow'){
                        user.is_followed = false
                    }
                }
                return user
            })
        },
        usersList(){
            this.usersListData = window._.clone(this.usersList)
        }
    },
    methods: {
        ...mapActions(useActionStore, ['updateFollowStatus']),
        async followUser(user) {
            if(this.authenticated){
                try {
                    await toggleFollowUser({
                        id: user.id,
                        action: "follow",
                        name: user.name,
                        avatar: user.avatar
                    });
                    this.usersListData.map(userItem => {
                        if (userItem.id === user.id) {
                            userItem.is_followed = true;
                        }
                        return userItem;
                    });
                    this.$emit('follow_user', user)
                    this.updateFollowStatus({userId: user.id, status: 'follow'})
                }
                catch (error) {
                    this.showError(error.error)
                }
            }else{
                this.showRequireLogin()
            }
        },
        async unFollowUser(user) {
            try {
                await toggleFollowUser({
                    id: user.id,
                    action: "unfollow",
                    name: user.name,
                    avatar: user.avatar
                });
                this.usersListData.map(userItem => {
                    if (userItem.id === user.id) {
                        userItem.is_followed = false;
                    }
                    return userItem;
                });
                this.$emit('unfollow_user', user)
                this.updateFollowStatus({userId: user.id, status: 'unfollow'})
            }
            catch (error) {
                this.showError(error.error)
            }
        },
        async unBlock(userId){
            try {
                await toggleBlockUser({
                    id: userId,
                    action: "remove"
                });
                this.usersListData = this.usersListData.filter(user => user.id !== userId);
            } catch (error) {
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
    emits: ['follow_user', 'unfollow_user', 'load-more']
}
</script>