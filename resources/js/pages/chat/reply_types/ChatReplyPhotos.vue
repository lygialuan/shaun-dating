<template>
    <div class="flex flex-col w-full" :class="{'items-end': owner}">
        <div v-if="message.content" class="whitespace-pre-wrap break-word text-xs border p-base-2 rounded-xl w-[fit-content] mb-2" :class="replyPhotosClass" v-tooltip="{value: message.created_at_full, showDelay: 1500}">
            <ChatMessageContent :content="message.content" :can-translate="message.canContentTranslate" :subject-id="message.id" />
        </div>
        <template v-if="message.items">
            <div class="flex opacity-50 max-w-2/3 lg:max-w-1/2 w-full">
                <div class="w-1/3 -mb-4" :class="owner ? 'ms-auto' : ''">
                    <div @click="clickPhotoImage()" class="cursor-pointer">
                        <div class="pb-[100%] bg-cover bg-center rounded-xl bg-reply-color dark:bg-slate-700" :style="{ backgroundImage: `url(${message.items[0].subject.url})`}"></div>
                    </div>
                </div>
            </div>
        </template>
    </div>
    <PhotoTheater ref="photoTheater" :photos="message.items"/>
</template>

<script>
import PhotoTheater from '@/components/modals/PhotoTheater.vue';
import ChatMessageContent from '../ChatMessageContent.vue'

export default {
    components: {
		ChatMessageContent, PhotoTheater
	},
	props: ['message', 'owner'],
	data() {
		return {
			displayPhotosTheater: false,
		}
	},
    methods:{
        clickPhotoImage() {
			this.$refs.photoTheater.openPhotosTheater()
		}
    },
    computed: {
        replyPhotosClass() {
            return {
                'owner-message-item bg-reply-color border-reply-color text-main-color dark:bg-slate-700 dark:border-white/10 dark:text-white max-w-3/4': this.owner,
                'message-item bg-chat-incoming-background-color border-chat-incoming-border-color text-main-color dark:bg-dark-message dark:border-dark-message dark:text-white max-w-[calc(75%+37.5px)]': !this.owner
            }
        }
    }
}
</script>