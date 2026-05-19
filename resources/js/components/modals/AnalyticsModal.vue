<template> 
    <div class="border border-divider p-base-2 md:p-4 rounded-base-lg mb-3 dark:border-gray-700">
        <div class="feed-item-header flex mb-base-2">
            <div class="feed-item-header-img">
                <Avatar :user="post.user"/>
            </div>
            <div class="feed-item-header-info flex-grow ps-base-2">
                <div class="feed-item-header-info-title flex justify-between items-start gap-base-2">
                    <div class="whitespace-normal">
                        <UserName :user="post.user" :truncate="false" />
                    </div>
                </div>
                <div class="feed-item-header-info-date flex flex-wrap gap-x-base-1 gap-y-1 items-center text-sub-color text-xs dark:text-slate-400">
                    <span v-if="post.type_label">{{ post.type_label }} - </span>
                    <router-link :to="{name: 'post', params: {id: post.id}}" class="text-inherit">{{post.created_at}}</router-link>
                    <span v-if="post.is_ads" class="feed-item-header-info-ads-badge bg-web-wash text-main-color leading-none px-base-1 py-1 rounded-md dark:bg-dark-web-wash dark:text-slate-400">{{ $t('Sponsored') }}</span>
                    <span v-if="post.type_box_label" class="feed-item-header-info-label-badge bg-badge-color text-primary-color leading-none px-base-1 py-1 rounded-md dark:bg-dark-web-wash dark:text-white">{{ post.type_box_label }}</span>
                    <span v-if="post.is_pin" class="feed-item-header-info-pinned-badge bg-badge-color text-main-color leading-none px-base-1 py-1 rounded-md dark:bg-dark-web-wash dark:text-white">{{ $t('Pinned') }}</span>
                </div>
            </div>
        </div>
        <div class="feed-item-content">
            <ContentHtml 
                class="feed-item-content-message mb-base-2" 
                :content="post.content" 
                :mentions="post.mentions"
                :can-translate="post.canContentTranslate"
                :subject-id="post.id"
                subject-type="posts"
            />
            <div class="activity_feed_content_item -mx-base-2 md:-mx-4">
                <PostContentType :post="post" />
            </div>
        </div>
    </div>
    <div class="flex flex-wrap gap-4">
        <div class="flex-1 flex flex-col gap-1">
            <span class="text-sub-color dark:text-slate-400 flex gap-1">{{ $t('Impressions') }}<InfoTooltip :content="$t('Times this post was seen on site.')"/></span>
            <span class="text-4xl font-bold"> {{ post.view_count }}</span>
        </div>
        <div class="flex-1 flex flex-col gap-1">
            <span class="text-sub-color dark:text-slate-400 flex gap-1">{{ $t('Engagements') }}<InfoTooltip :content="$t('Total number of times a user has interacted with a post. This includes all clicks anywhere on the post (including likes and comments).')"/></span>
            <span class="text-4xl font-bold"> {{ post.engagement_count }}</span>
        </div>
        <div v-if="post.content_amount > 0" class="flex-1 flex flex-col gap-1">
            <span class="text-sub-color dark:text-slate-400 flex gap-1">{{ $t('Earning') }}<InfoTooltip :content="$filters.textTranslate(this.$t('Total amount in [token_name] you have earned from this post so far.'), { token_name: config.wallet.tokenName })"/></span>
            <span class="text-4xl font-bold"> {{ post.earn_amount }}</span>
        </div>
    </div>
</template>

<script>
import { mapState } from 'pinia'
import { useAppStore } from '@/store/app'
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'
import InfoTooltip from '@/components/utilities/InfoTooltip.vue';
import ContentHtml from '@/components/utilities/ContentHtml.vue'
import PostContentType from '@/components/posts/PostContentType.vue'

export default {
    components: { Avatar, UserName, InfoTooltip, ContentHtml, PostContentType },
    inject: {
        dialogRef: {
            default: null
        }
    },
    data(){
        return{
            post: this.dialogRef.data.post
        }
    },
    computed:{
        ...mapState(useAppStore, ['config'])
    }
}
</script>