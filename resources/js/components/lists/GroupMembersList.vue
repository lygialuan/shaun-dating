<template>
    <SkeletonUsersList v-if="loading" />
    <template v-else>
        <div v-if="membersList.length" class="users-list space-y-base-2">
            <div v-for="member in membersList" :key="member.user.id" class="users-list-item flex items-center gap-base-2">
                <Avatar :user="member.user" />
                <div class="flex-1 min-w-0">
                    <UserName :user="member.user" class="list_items_title_text_color" />
                    <div v-if="member.user.show_follower" class="list_items_sub_text_color flex items-center text-xs text-sub-color dark:text-slate-400">{{ $filters.numberShortener(member.user.follower_count, $t('[number] follower'), $t('[number] followers')) }}</div> 
                </div>
                <div>
                    <slot name="actions" :member="member"></slot>
                </div>
            </div>
            <div v-if="hasLoadMore" class="text-center">
                <BaseButton @click="handleLoadmore">{{$t('View more')}}</BaseButton>
            </div>
        </div>
        <template v-else>
            <slot name="empty">
                <div class="text-center">
                    {{ $t('No members') }}
                </div>
            </slot>
        </template>
    </template>
</template>

<script>
import SkeletonUsersList from '@/components/skeletons/SkeletonUsersList.vue'
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'

export default {
    components: { SkeletonUsersList, UserName, Avatar, BaseButton },
    props: {
        loading: {
            type: Boolean,
            default: true
        },
        membersList: {
            type: Array,
            default: () => []
        },
        hasLoadMore: {
            type: Boolean,
            default: false
        }
    },
    methods:{
        handleLoadmore(){
            if(this.hasLoadMore){
                this.$emit('load-more')
            }
        }
    },
    emits: ['load-more']
}
</script>