<template>
    <div>
        <div class="flex gap-base-2 border-b border-divider pb-base-2 mb-base-2 dark:border-white/10">
            <Avatar :user="user" :router="false" :activePopover="false"/>
            <div class="flex-1">
                <Mentionable v-model="content" :placeholder="isRecommend ? ($t('What do you recommend about') + ' ' + page.name + '?') : ($t('Leave your feedback for') + ' ' + page.name)" rows="1" ref="recommendContent" autofocus class="border-none !p-0" maxRows="8" />
            </div>
            <EmojiPicker @emoji_click="addEmoji"/>
        </div>
        <div class="text-end">
            <BaseButton @click="handleClickRecommend()">{{ $t('Send') }}</BaseButton>
        </div>
    </div>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import Avatar from '@/components/user/Avatar.vue'
import Mentionable from "@/components/utilities/Mentionable.vue"
import EmojiPicker from "@/components/utilities/EmojiPicker.vue"
import BaseButton from "@/components/inputs/BaseButton.vue"
import { useAuthStore } from '@/store/auth';
import { usePostStore } from '@/store/post';

export default {
    components: { Avatar, Mentionable, EmojiPicker, BaseButton },
    inject: ['dialogRef'],
    data(){
        return{
            content: '',
            isRecommend: this.dialogRef.data.isRecommend,
            page: this.dialogRef.data.page
        }
    },
    computed: {
		...mapState(useAuthStore, ['user'])
	},
    methods: {
        ...mapActions(usePostStore, ['postNewReviewFeed']),
        addEmoji(emoji){		
			this.$refs.recommendContent.addContent(emoji)
		},
        async handleClickRecommend(){
            try {
                const response = await this.postNewReviewFeed({
                    content: this.content,
                    is_recommend: this.isRecommend,
                    page_id: this.page.id
                })
                this.dialogRef.close({review: response})
                this.showSuccess(this.$t('Your review have been sent.'))
            } catch (error) {
                this.showError(error.error)
            }
        }
    }
}
</script>