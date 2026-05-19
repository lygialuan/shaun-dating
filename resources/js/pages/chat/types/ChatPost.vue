<template>
    <div class="flex items-center gap-base-2" :class="{'flex-row-reverse': !owner}">
        <div class="flex-1"></div>
        <ChatMessageAction v-if="room_info?.enable && room_info?.status === 'accepted'" :message="message" :owner="owner"/>
        <div class="flex flex-col">
            <router-link v-if="post" :to="{name: 'post', params: {id: post.id}, query: {ref_code: post.ref_code}}" class="w-52 sm:w-64 text-main-color dark:text-white" :class="{'ms-auto': owner}">
                <div v-if="post" class="bg-white border border-divider p-4 w-full rounded-base-lg dark:bg-dark-web-wash dark:border-white/10">
                    <div class="flex items-center gap-base-2 mb-base-2">
                        <Avatar :user="post.user" />
                        <UserName :user="post.user" :truncate="false" />
                    </div>
                    <div class="relative overflow-hidden">
                        <div class="block pb-[75%] bg-cover bg-center bg-no-repeat" :style="{ backgroundImage: `url(${post.og_image || post.user.avatar})`}" :class="{'blur-2xl': post.content_warning_categories.length}"></div>
                        <div v-if="post.content_warning_categories.length" class="flex justify-center items-center p-6 absolute inset-0 z-30 bg-black-trans-5">
                            <div class="text-center space-y-2 text-white">
                                <BaseIcon name="eye_slash" />
                                <div class="text-base-xl font-semibold truncate-3">{{ $t('Content warning') }}</div>
                            </div>
                        </div>
                    </div>
                    <div v-if="post.content" class="truncate-3 mt-2">{{ post.content }}</div>
                </div>
            </router-link>
            <div v-else class="inline-block bg-web-wash text-xs px-2 py-1 rounded-md dark:bg-dark-web-wash w-max" :class="{'ms-auto': owner}">{{ $t('This content is no longer available') }}</div>
            <div v-if="message" class="relative border whitespace-pre-wrap break-word text-sm p-base-2 -mt-base-1 rounded-xl max-w-max" :class="chatTextClass" v-tooltip="{value: message.created_at_full, showDelay: 1500}">
                <ChatMessageContent :content="message.content" :can-translate="message.canContentTranslate" :subject-id="message.id" />
            </div>
        </div>
    </div>
</template>

<script>
import ChatMessageContent from '@/pages/chat/ChatMessageContent.vue'
import ChatMessageAction from '@/pages/chat/ChatMessageAction.vue'
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'

export default {
    components: { ChatMessageContent, ChatMessageAction, Avatar, UserName, BaseIcon },
    props: ['message', 'owner', 'room_info'],
    computed: {
        chatTextClass() {
            return {
                'bg-white border-divider dark:bg-slate-800 dark:border-slate-700': this.message.is_delete,
                'owner-message-item bg-primary-color border-primary-color text-white dark:bg-dark-primary-color dark:border-dark-primary-color ms-auto': !this.message.is_delete && this.owner,
                'message-item bg-chat-incoming-background-color border-chat-incoming-border-color text-main-color dark:bg-dark-message dark:border-dark-message dark:text-white': !this.message.is_delete && !this.owner
            }
        },
        post(){
            return this.message.items[0].subject
        }
    }
}
</script>
