<template>
    <Mentionable v-model="content" ref="mention" autofocus maxRows="8" class="mb-base-2">     
        <div class="flex justify-between items-center gap-base-2 mt-1">
            <EmojiPicker @emoji_click="addEmoji"/>
            <WordCounter :max="config.post.character_max" :data="content" />
        </div>   
    </Mentionable>
    <div class="text-end">
        <BaseButton :loading="loading" @click="editItem()">{{$t('Edit')}}</BaseButton>
    </div>
</template>
<script>
import { mapState, mapActions } from "pinia";
import { decodeHtml } from '@/utility/index'
import { usePostStore } from '@/store/post';
import { useCommentStore } from '@/store/comment';
import { useAppStore } from '@/store/app';
import Constant from '@/utility/constant'
import EmojiPicker from "@/components/utilities/EmojiPicker.vue";
import BaseButton from '@/components/inputs/BaseButton.vue';
import Mentionable from "@/components/utilities/Mentionable.vue";
import WordCounter from "@/components/utilities/WordCounter.vue";

export default {
    components: { EmojiPicker, BaseButton, Mentionable, WordCounter },
    inject: ['dialogRef'],
    data(){
        return{
            id: this.dialogRef.data.id,
            content: decodeHtml(this.dialogRef.data.content),
            loading: false
        }
    },
    computed:{
		...mapState(useAppStore, ['config'])
	},
    methods:{
        ...mapActions(usePostStore, ['editPost']),
        ...mapActions(useCommentStore, ['editComment', 'editReply']),
        addEmoji(emoji){		
			this.$refs.mention.addContent(emoji)
		},
        async editItem(){
            this.loading = true
            try {                    
                switch(this.dialogRef.data.type){
                    case 'posts':
                        await this.editPost({
                            id: this.id,
                            content: this.content
                        })
                        break
                    case 'comments':
                        await this.editComment({
                            id: this.id,
                            content: this.content
                        })
                        break
                    case 'comment_replies':
                        await this.editReply({
                            id: this.id,
                            content: this.content,
                            comment_id: this.dialogRef.data.comment_id
                        })
                        break
                }
                this.dialogRef.close()
                this.showSuccess(this.$t('Edit Successfully.'))
            } catch (error) {
                if (error.error?.code == Constant.RESPONSE_CODE_MEMBERSHIP_PERMISSION) {
					this.showPermissionPopup('post',error.error.message)
				} else {
					this.showError(error.error)
				}
            } finally {
                this.loading = false
            }
        }
    }
}
</script>