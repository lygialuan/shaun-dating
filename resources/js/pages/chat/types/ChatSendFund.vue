<template>
    <div class="flex items-center gap-base-2" :class="{'flex-row-reverse': !owner}">
        <div class="flex-1"></div>
        <ChatMessageAction v-if="room_info?.enable && room_info?.status === 'accepted'" :message="message" :owner="owner"/>
        <div v-if="message" class="text-sm max-w-7/12 lg:max-w-5/12 w-full" v-tooltip="{value: message.created_at_full, showDelay: 1500}">
            <div class="flex flex-col items-center text-center bg-base-green rounded-xl overflow-hidden">
                <div class="p-base-2 text-white">
                    <BaseIcon name="check_circle" />
                </div>
                <div class="bg-gray-6 p-base-2 w-full dark:bg-dark-web-wash">
                    <div>{{ $t('Successfully sent') }}</div>
                    <div class="text-lg font-bold">{{ fundAmount }}</div>
                    <router-link :to="{name: 'wallet'}" class="underline">{{ $t('View Detail') }}</router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import ChatMessageAction from '@/pages/chat/ChatMessageAction.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue';

export default {
    components: { ChatMessageAction, BaseIcon },
    props: ['message', 'owner', 'room_info'],
    computed: {
        fundAmount(){
            return this.exchangeTokenCurrency(this.message.items[0].subject.net.startsWith('-') ? this.message.items[0].subject.net.substring(1) : this.message.items[0].subject.net)
        }
    }
}
</script>
