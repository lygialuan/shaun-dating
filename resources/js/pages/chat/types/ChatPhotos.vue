<template>
    <div v-if="message.content" class="whitespace-pre-wrap break-word text-sm border p-base-2 rounded-xl mb-2" :class="chatPhotosClass" v-tooltip="{value: message.created_at_full, showDelay: 1500}">
        <ChatMessageContent :content="message.content" :can-translate="message.canContentTranslate" :subject-id="message.id" />
    </div>
    <div class="flex items-center gap-base-2" :class="{'flex-row-reverse': !owner}">
        <div class="flex-1"></div>
        <ChatMessageAction v-if="room_info?.enable && room_info?.status === 'accepted'" :message="message" :owner="owner"/>
        <template v-if="message.items.length">
            <div v-if="message.items.length > 1" class="flex max-w-2/3 w-full relative">
                <div class="flex flex-wrap gap-base-1 w-full" v-tooltip="{value: message.created_at_full, showDelay: 1500}">
                    <div v-for="(chatItem, index) in message.items" :key="chatItem?.subject?.id" @click="clickPhotoImage(index)" class="cursor-pointer" :class="(message.items.length > 2) ? 'flex-[0_0_calc(33.3%-3.33px)]' : 'flex-1'">
                        <div class="pb-[100%] bg-cover bg-center rounded-xl" :style="{ backgroundImage: `url(${chatItem?.subject?.url})`}"></div>
                    </div>
                </div>
            </div>
            <div v-else class="relative" :class="{'justify-end': owner}">
                <div v-for="(chatItem, index) in message.items" :key="chatItem?.subject?.id" @click="clickPhotoImage(index)" class="cursor-pointer">
                    <div :style="{width: `${photoWidth}px`, paddingBottom: `${photoWrapperPadding}%`, backgroundImage: `url(${chatItem?.subject?.url})`}" class="bg-center bg-cover bg-no-repeat rounded-xl" v-tooltip="{value: message.created_at_full, showDelay: 1500}"></div>
                </div>
            </div>
        </template>
    </div>
    <PhotoTheater ref="photoTheater" :photos="message.items"/>
</template>

<script>
import PhotoTheater from '@/components/modals/PhotoTheater.vue';
import ChatMessageContent from '@/pages/chat/ChatMessageContent.vue'
import ChatMessageAction from '@/pages/chat/ChatMessageAction.vue'

export default {
    components: {
		ChatMessageContent, PhotoTheater, ChatMessageAction
	},
	props: ['message', 'owner', 'room_info'],
	data() {
		return {
			displayPhotosTheater: false,
            photoWidth: 0,
            photoWrapperPadding: 0
		}
	},
    mounted() {
        this.calculatePhotoWidth();
        window.addEventListener("resize", () => {
			this.calculatePhotoWidth();
		})
    },
    computed: {
        chatPhotosClass() {
            return {
                'owner-message-item bg-primary-color border-primary-color text-white dark:bg-dark-primary-color dark:border-dark-primary-color max-w-3/4 w-max ms-auto': this.owner,
                'message-item bg-chat-incoming-background-color border-chat-incoming-border-color text-main-color dark:bg-dark-message dark:border-dark-message dark:text-white max-w-3/4 w-max': !this.owner
            }
        }
    },
    methods:{
        clickPhotoImage(photoIndex) {
			this.$refs.photoTheater.openPhotosTheater(photoIndex)
		},
        calculatePhotoWidth(){
            var containerWidth = document.getElementsByClassName('messages-list')[0]?.offsetWidth,
                photo = this.message.items[0].subject?.params
            if(photo){
                if(photo.width === 0 || photo.height === 0){
                    this.photoWidth = containerWidth * 0.3
                    this.photoWrapperPadding = 100
                } else {
                    this.photoWrapperPadding = photo.height / photo.width * 100
                    if(192 * photo.width / photo.height > containerWidth * 0.5){
                        this.photoWidth = containerWidth * 0.5
                    } else {
                        this.photoWidth = 192 * photo.width / photo.height
                    }
                }
            }
        }
    }
}
</script>