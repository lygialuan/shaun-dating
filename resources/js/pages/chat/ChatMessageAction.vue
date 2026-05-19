<template>
    <div class="opacity-0 transition-opacity flex gap-1 group-hover:opacity-100 relative">
        <button v-if="!message.is_delete && owner" class="flex items-center justify-center w-7 h-7 rounded-full transition-colors hover:bg-gray-6 dark:hover:bg-slate-600 cursor-pointer" v-tooltip.top="{value: $t('Unsend'), showDelay: 1000}" @click="unsentMessage(message)">
            <BaseIcon name="trash" size="18" />
        </button>
        <button v-if="!message.is_delete" class="flex items-center justify-center w-7 h-7 rounded-full transition-colors hover:bg-gray-6 dark:hover:bg-slate-600 cursor-pointer" v-tooltip.top="{value: $t('Reply'), showDelay: 1000}" @click="replyMessage(message)">
            <BaseIcon name="share" size="18" />
        </button>
    </div>
</template>

<script>
import { mapActions } from 'pinia';
import BaseIcon from '@/components/icons/BaseIcon.vue';
import { useChatStore } from '@/store/chat';

export default {
    props: ['message', 'owner'],
    components: { BaseIcon },
    methods: {
        ...mapActions(useChatStore, ['unsentRoomMessage', 'setReplyMessage']),
        unsentMessage(message){
            this.$confirm.require({
                message: this.$t('Do you want to unsend this message?'),
                header: this.$t('Please confirm'),
                acceptLabel: this.$t('Ok'),
                rejectLabel: this.$t('Cancel'),
                accept: async () => {
                    try {
                        await this.unsentRoomMessage(message)
                    } catch (error) {
                        this.showError(error.error)
                    }
                }
            });
        },
        replyMessage(message){
            this.setReplyMessage(message)
        }
    }
}

</script>