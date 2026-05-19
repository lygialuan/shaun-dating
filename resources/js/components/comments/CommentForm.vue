<template>
    <div v-if="item.stop_comment" class="flex items-center justify-center h-20 bg-light-web-wash p-base-2 rounded-md dark:bg-dark-web-wash">
        {{ $t('Commenting for this post has been turned off') }}
    </div>
    <div v-else class="feed-comment-info-comment-holder flex flex-col gap-base-2">
        <div v-if="replyCommentData.user_name" class="reply-status-item flex items-center justify-between bg-web-wash p-base-2 rounded-none absolute bottom-full start-0 end-0 md:rounded-t-md dark:bg-dark-web-wash">
            {{$t('Replying to')}} {{ replyCommentData.user_name }}
            <button @click="handleCancelReply" class="reply-status-item-icon">
                <BaseIcon name="close" size="16" />
            </button>
        </div>
        <div v-if="['following', 'verified', 'mentioned'].includes(item.comment_privacy)" class="flex gap-base-2 bg-light-web-wash p-base-2 rounded-md dark:bg-dark-web-wash">
            <div class="flex items-center justify-center w-8 h-8 bg-primary-color text-white rounded-full flex-shrink-0">
                <BaseIcon :name="iconPrivacy(item.comment_privacy)" size="20"/>
            </div>
            <div>
                <div class="font-semibold">{{ item.canComment ? $t('You can comment') : $t('Who can comment?') }}</div>
                <div class="text-sub-color text-xs dark:text-slate-400">{{ textPrivacy(item.comment_privacy) }}</div>
            </div>
        </div>
        <div v-if="item.canComment" class="flex flex-col gap-base-2">
            <div class="comment-form bg-light-web-wash text-base-none p-base-2 rounded-md dark:bg-dark-web-wash">
                <Mentionable v-model="content" :placeholder="$t('Add comment')" rows="1" ref="mention" :autofocus="isMobile ? false : true" :draft-id="draftKey" @enter="handlePostComment()" @paste="handlePasteImage" class="!bg-transparent border-none !p-0" />
                <div class="flex gap-base-2 pt-base-2">
                    <div class="comment-form-action flex gap-2 flex-1">
                        <EmojiPicker class="comment-form-action-item" ref="emojiPickerRef" @emoji_click="addEmoji" size="20"/>
                        <TenorGifs v-if="config.post.enable_gifs" class="comment-form-action-item" ref="tenorGifsRef" @upload="uploadCommentGif" size="20" appendTo="self" />
                        <UploadImages class="comment-form-action-item" ref="uploadCommentImages" @upload="uploadCommentImages" size="20" />
                    </div>
                    <button :disabled="!enableCommentButton" class="feed-comment-info-comment-holder-btn text-primary-color font-bold text-xs uppercase disabled:opacity-50 dark:text-dark-primary-color" @click="handlePostComment()">{{$t('Post')}}</button>
                </div>
            </div>
            <div v-if="commentImagesUpload.length || commentImagesUploadLoading.length" class="flex flex-wrap gap-base-2">
                <div v-for="image in commentImagesUpload" :key="image.subject.id" class="flex-shrink-0 border border-divider inline-block w-16 h-16 bg-cover bg-center relative rounded-md dark:border-white/10" :style="{ backgroundImage: `url(${image.subject.url})`}">
                    <button class="shadow-md inline-flex items-center justify-center absolute -top-2 -end-2 bg-white border border-divider text-main-color rounded-full w-5 h-5" @click="handleRemoveCommentImage(image.id)">
                        <BaseIcon name="close" size="16" />
                    </button>
                </div>
                <div v-for="index in commentImagesUploadLoading" :key="index" class="inline-block w-16 h-16 bg-cover bg-center relative rounded-md border border-divider dark:border-white/10 float-start status-box-image-upload-list-loading">
                    <span class="loading-icon">
                        <div class="loader"></div>
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { uploadCommentImages, deleteCommentItem, uploadReplyImages, deleteReplyItem } from '@/api/comment';
import { useAuthStore } from '@/store/auth'
import { useCommentStore } from '@/store/comment'
import { usePostStore } from '@/store/post'
import { useAppStore } from '@/store/app'
import { useDraftStore } from '@/store/draft'
import Mentionable from '@/components/utilities/Mentionable.vue'
import EmojiPicker from '@/components/utilities/EmojiPicker.vue'
import TenorGifs from '@/components/utilities/TenorGifs.vue'
import UploadImages from '@/components/utilities/UploadImages.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue';
import Constant from '@/utility/constant'

export default {
    components: {
        Mentionable,
        EmojiPicker,
        TenorGifs,
        UploadImages,
        BaseIcon
    },
    data(){
        return{
            content: '',
            commentImagesUpload: [],
            commentImagesUploadLoading: [],
            commentType: 'text',
            loadingSendButton: false
        }
    },
    props: {
        item: {
            type: Object,
            default: null
        },
        type: {
            type: String,
            default: 'posts'
        },
        replyCommentData: {
            type: Object,
            default: null
        }
    },
    computed:{
        ...mapState(useAppStore, ['isMobile', 'config']),
        ...mapState(useAuthStore, ['authenticated']),
        enableCommentButton(){
            return (this.content.trim() != '' || this.commentImagesUpload.length) && !this.loadingSendButton
        },
        draftKey(){
            return `comment_draft_${this.type}_${this.item?.id}`;
        }
    },
    methods:{
        ...mapActions(useCommentStore, ['postComment', 'postReply']),
        ...mapActions(usePostStore, ['increaseCommentCount']),
        ...mapActions(useDraftStore, ['removeDraft']),
        addEmoji(content) {
            this.$refs.mention.addContent(content)
        },
        async handlePostComment() {
            if(this.authenticated){
                const idItems = this.commentImagesUpload.length ? this.commentImagesUpload.map(x => x.id) :
							this.commentSharedLink ? [this.commentSharedLink.id] : null

                if (this.enableCommentButton) {
                    this.loadingSendButton = true
                    if(this.replyCommentData.comment_id){
                        try {
                            await this.postReply({
                                comment_id: this.replyCommentData.comment_id,
                                content: this.content,
                                items: idItems,
                                type: this.commentType
                            })
                            this.increaseCommentCount(this.item.id)
                            this.$emit('post_reply')
                        } catch (error) {
                            if (error.error.code == Constant.RESPONSE_CODE_MEMBERSHIP_PERMISSION) {
                                this.showPermissionPopup('post', error.error.message);
                            } else {
                                this.showError(error.error);
                            }
                        } finally {
                            this.loadingSendButton = false
                        }
                    } else {
                        try {
                            await this.postComment({                 
                                subject_type: this.type,
                                subject_id: this.item.id,
                                content: this.content,
                                replies: [],
                                items: idItems,
                                type: this.commentType
                            })
                            this.increaseCommentCount(this.item.id)
                            this.$emit('post_comment')
                        } catch (error) {
                            if (error.error.code == Constant.RESPONSE_CODE_MEMBERSHIP_PERMISSION) {
                                this.showPermissionPopup('post', error.error.message);
                            } else {
                                this.showError(error.error);
                            }
                        } finally {
                            this.loadingSendButton = false
                        }
                    }             
                    this.setContent('')
                    this.commentImagesUpload = []
                    this.commentType = 'text'
                    this.$refs.emojiPickerRef.close()
                    this.$refs.tenorGifsRef.close()
                    this.removeDraft(this.draftKey)
                }
            }else{
                this.showRequireLogin()
            }
        },
        setContent(content) {
            this.content = content;
            this.$refs.mention?.setContent(content)
        },
        setItems(items = []){
            this.commentImagesUpload = items
        },
        checkType() {
			if(this.commentImagesUpload.length){					
				this.commentType = 'photo'
			} else {
				this.commentType = 'text'
			}
		},
        async startUploadCommentImages(uploadedFiles, clipboard){
            if (typeof clipboard === 'undefined') {
                clipboard = false
            }
			for( var i = 0; i < uploadedFiles.length; i++ ){
                if (clipboard) {
					// Skip content if not image
					if (uploadedFiles[i].type.indexOf("image") == -1) continue;
				}
				var checkUpload = true
				if (! clipboard) {
					checkUpload = this.checkUploadedData(uploadedFiles[i])
            }
				if(checkUpload){
					let formData = new FormData()
                    var blob = uploadedFiles[i]
                    if (clipboard) {
                        blob = uploadedFiles[i].getAsFile();
                    }
                    formData.append('file', blob)
                    this.commentImagesUploadLoading.unshift(i)
                    try {
                        const response = this.replyCommentData.comment_id ? await uploadReplyImages(formData) : await uploadCommentImages(formData);
                        this.commentImagesUpload.push(response);
                        this.checkType()
                        this.$refs.mention.focus()
                    } catch (error) {
                        this.showError(error.error)
                        this.$refs.uploadCommentImages.reset()
                    } finally {
                        this.commentImagesUploadLoading.shift()
                    }
                }
			}
            this.$refs.uploadCommentImages.reset()
		},
        async handleRemoveCommentImage(imageId){
			try {
				this.replyCommentData.comment_id ? await deleteReplyItem(imageId) : await deleteCommentItem(imageId);
				this.commentImagesUpload = this.commentImagesUpload.filter(file => file.id !== imageId);
				this.checkType()
                this.$refs.uploadCommentImages.reset()
                this.$refs.mention.focus()
			} catch (error) {
				this.showError(error.error)
			}
		},
        handlePasteImage(event){
			this.startUploadCommentImages(event.clipboardData.items, true)
		},
        uploadCommentImages(event){
			this.startUploadCommentImages(event.target.files)
		},
        uploadCommentGif(file) {
            this.startUploadCommentImages([file]);
        },
        iconPrivacy(privacy){
            switch (privacy) {
                case 'following':
                    return 'user_check'
                case 'verified':
                    return 'seal_check'
                case 'mentioned':
                    return 'at'
                default:
                    break;
            }
        },
        textPrivacy(privary){
            switch (privary) {
                case 'following':
                    return this.$filters.textTranslate(this.$t("Accounts [name] follows or mentioned can comment"), { name: this.item.user.name });
                case 'verified':
                    return this.$filters.textTranslate(this.$t("Verified accounts or accounts mentioned by [name] can comment"), { name: this.item.user.name });
                case 'mentioned':
                    return this.$filters.textTranslate(this.$t("Accounts [name] mentioned can comment"), { name: this.item.user.name });
                default:
                    break;
            }
        },
        handleCancelReply(){
            this.$emit('cancel_reply')
        }
    },
    emits: ['post_comment', 'post_reply', 'cancel_reply']
}
</script>