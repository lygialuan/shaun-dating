<template>
    <div class="mb-base-2">{{$t('Message')}}</div>
    <Mentionable ref="mention" v-model="share_content" autofocus maxRows="8" class="mb-base-2">
        <EmojiPicker @emoji_click="addEmoji" class="mt-1" />
    </Mentionable>
    <div v-if="type === 'posts'" class="feed-item border-divider dark:border-white/10">
        <div v-if="subject.parent" class="feed-entry-wrap">
            <PostContent :post="subject.parent" :showCommentAction="false" :showMenuAction="false"/>
        </div>
        <div v-else class="feed-entry-wrap">
            <PostContent :post="subject" :showCommentAction="false" :showMenuAction="false"/>
        </div>
    </div>
    <div class="text-end">
        <BaseButton @click="shareToProfile()" :loading="loading">{{$t('Share')}}</BaseButton>
    </div>
</template>

<script>
import { mapActions } from 'pinia'
import { usePostStore } from '@/store/post';
import EmojiPicker from "@/components/utilities/EmojiPicker.vue";
import BaseButton from '@/components/inputs/BaseButton.vue';
import Mentionable from "@/components/utilities/Mentionable.vue";
import PostContent from '@/components/posts/PostContent.vue';
import Constant from '@/utility/constant'

export default {
    components: { EmojiPicker, BaseButton, PostContent, Mentionable },
    inject: {
        dialogRef: {
            default: null
        }
    },
    data(){
        return{
            loading: false,
            type: this.dialogRef.data.type,
            subject: this.dialogRef.data.subject,
            share_content: ''
        }
    },
    mounted(){
		this.$refs.mention.addContent('') // Focus Textarea when open
	},
    methods:{
        ...mapActions(usePostStore, ['postNewFeed']),
        addEmoji(emoji){		
			this.$refs.mention.addContent(emoji)
		},
        async shareToProfile(){
            this.loading = true
            try {
                let sharePayload
                if(this.type === 'posts'){
                    sharePayload = {
                        type: 'share',
                        content: this.share_content,
                        parent_id: this.subject.parent ? this.subject.parent.id: this.subject.id
                    }
                } else {
                    sharePayload = {
                        type: 'share_item',
                        subject_type: this.type,
                        subject_id: this.subject.id,
                        content: this.share_content
                    }
                }
                await this.postNewFeed(sharePayload)
                this.dialogRef.close()
                this.showSuccess(this.$t('Shared Successfully.'))
                this.loading = false
            } catch (error) {
                if (error.error.code == Constant.RESPONSE_CODE_MEMBERSHIP_PERMISSION) {
					this.showPermissionPopup('post',error.error.message)
				} else {
					this.showError(error.error)
				}
                this.loading = false
            }
        }
    }
}
</script>