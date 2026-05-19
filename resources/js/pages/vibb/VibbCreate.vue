<template>
    <div class="main-content-section">
        <div class="main-content-section-header">
            <h3 class="main-content-section-header-title">{{ $t('Create New Vibb') }}</h3>
        </div>
        <ProgressBar :value="videoUploadProgress" class="mb-base-2"/>
        <ContentWarningWrapper v-if="uploadedVideo" :content-warning-list="contentWarningSelecteds" class="mb-base-2">
            <template #hide-button="{ toggle }">
                <button  
                    class="bg-black/30 text-white flex justify-center items-center w-8 h-8 rounded-md absolute top-3 end-14"
                    @click.stop="toggle"
                >
                    <BaseIcon name="eye_slash" />
                </button>
            </template>
            <div class="pb-[40%] relative">
                <span v-if="selectedSong" class="bg-black text-white px-2 py-1 text-xs rounded-lg inline-flex items-center gap-1 absolute bottom-base-2 start-base-2 z-10">
                    <BaseIcon name="music_note" size="16" />
                    {{ selectedSong.name }}
                    <button @click="handleRemoveSelectedSong" class="absolute -top-2 -end-2 bg-red-600 text-white rounded-full">
                        <BaseIcon name="close" size="16" />
                    </button>
                </span>
                <div class="flex items-center justify-center absolute inset-0 rounded-base-lg overflow-hidden" :style="{ backgroundColor: `${uploadedVideo.subject.thumb.params.dominant_color ? uploadedVideo.subject.thumb.params.dominant_color : '#000'}`}">
                    <img :src="uploadedVideo.subject.thumb.url" class="max-h-full max-w-full mx-auto">
                    <button @click="handleRemoveVideo(uploadedVideo.id)" class="bg-black/30 text-white flex justify-center items-center w-8 h-8 rounded-md absolute top-3 end-3">
                        <BaseIcon name="close" />
                    </button>
                </div>
            </div>
        </ContentWarningWrapper>
        <div v-else class="pb-[65%] md:pb-[40%] relative mb-base-2">
            <span v-if="selectedSong" class="bg-black text-white px-2 py-1 text-xs rounded-lg inline-flex items-center gap-1 absolute bottom-base-2 start-base-2 z-10">
                <BaseIcon name="music_note" size="16" />
                {{ selectedSong.name }}
                <button @click="handleRemoveSelectedSong" class="absolute -top-2 -end-2 bg-red-600 text-white rounded-full">
                    <BaseIcon name="close" size="16" />
                </button>
            </span>
            <div class="absolute inset-0">
                <UploadWrap @drop_data="dropData">
                    <UploadVideo ref="vibbUploadRef" @upload="uploadVideo" class="h-full">
                        <div class="flex flex-col items-center justify-center text-center gap-base-2 bg-light-web-wash px-10 py-16 rounded-base-lg h-full dark:bg-dark-web-wash">
                            <BaseIcon name="video_camera" />
                            <div>{{ $t('Drag or click here to upload') }}</div>
                            <BaseButton class="pointer-events-none">{{ $t('Upload video') }}</BaseButton>
                        </div>
                    </UploadVideo>
                </UploadWrap>
            </div>
        </div>
        <div class="mb-base-2">{{ $t('Add Music') }}</div>
        <Songs ref="songsRef" class="border border-divider mb-base-2" @select_song="handleSelectSong" @remove_song="handleRemoveSong" />
        <div class="mb-base-2">{{ $t('Description') }}</div>
        <Mentionable v-model="description" :placeholder="placeholderDescription" :error="error.content" maxRows="5" class="mb-base-2" />
        <BaseSelectContentWarning v-if="uploadedVideo" v-model="contentWarningSelecteds" @put_content_warning="handlePutContentWarning" class="mb-base-2" />
        <BaseSelectPrivacy v-model="commentPrivacy" :options="privaciesList" :title="$t('Who can comment?')" :description="$t('Choose who can comment to this post. Anyone mentioned can always comment.')" class="mb-base-2"/>
        <BaseButton @click="handleCreateVibb" :loading="loadingCreate" :disabled="!uploadedVideo" fluid>{{ $t('Continue') }}</BaseButton>
    </div>
</template>

<script>
import { mapState } from 'pinia'
import { useAppStore } from '@/store/app'
import { uploadVibbVideo, storeVibb } from '@/api/vibb'
import { deletePostItem } from '@/api/posts'
import Constant from '@/utility/constant'
import UploadWrap from '@/components/layout/UploadWrap.vue'
import BaseIcon from "@/components/icons/BaseIcon.vue"
import BaseButton from "@/components/inputs/BaseButton.vue"
import Songs from '@/components/vibb/Songs.vue'
import Mentionable from "@/components/utilities/Mentionable.vue"
import UploadVideo from '@/components/utilities/UploadVideo.vue'
import BaseSelectContentWarning from "@/components/inputs/BaseSelectContentWarning.vue"
import BaseSelectPrivacy from "@/components/inputs/BaseSelectPrivacy.vue"
import ContentWarningWrapper from '@/components/utilities/ContentWarningWrapper.vue'
import ProgressBar from '@/components/utilities/ProgressBar.vue'

export default {
    components: { UploadWrap, BaseIcon, BaseButton, Songs, Mentionable, UploadVideo, BaseSelectContentWarning, BaseSelectPrivacy, ContentWarningWrapper, ProgressBar },
    data(){
        return{
            uploadedVideo: null,
            description: '',
            commentPrivacy: 'everyone',
            isContentWarning: false,
            contentWarningSelecteds: [],
            videoUploadProgress: 0,
            selectedSong: null,
            error: {
                content: ''
            },
            loadingCreate: false
        }
    },
    computed:{
        ...mapState(useAppStore, ['config']),
        privaciesList() {
            return [
                { icon: 'globe', name: this.$t('Everyone'), label: this.$t('Everyone can comment'), value: 'everyone' },
                { icon: 'user_check', name: this.$t('Accounts you follow'), label: this.$t('Accounts you follow can comment'), value: 'following' },
                { icon: 'seal_check', name: this.$t('Verified accounts'), label: this.$t('Only Verified accounts can comment'), value: 'verified', isShow: this.config?.userVerifyEnable },
                { icon: 'at', name: this.$t('Only accounts you mention'), label: this.$t('Only accounts you mention can comment'), value: 'mentioned' },
            ]
        },
        placeholderDescription(){
			return this.$filters.textTranslate(this.$t("Enter vibb description ( [hashtag] hashtags [mention] mention)"), { hashtag: '#', mention: '@' })
		},
    },
    methods:{
        dropData(data){
			this.startUploadVideo(data.dataTransfer.files)
		},
        uploadVideo(event){
			this.startUploadVideo(event.target.files)	
		},
        async startUploadVideo(uploadedFiles){			
			for( var i = 0; i < uploadedFiles.length; i++ ){
				if(this.checkUploadedData(uploadedFiles[i], 'video')){
					if (this.uploadedVideo) {
						this.removeUploadedVideo(this.uploadedVideo.id)
					}
					let formData = new FormData()
					formData.append('file', uploadedFiles[i])
					const onProgress = (progressEvent) => {
						const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
						this.videoUploadProgress = percentCompleted;
					};
					try {
						const response = await uploadVibbVideo(formData, onProgress);
						this.uploadedVideo = response;
					} catch (error) {
						if(typeof(error) === 'object'){
							if (error.error.code == Constant.RESPONSE_CODE_MEMBERSHIP_PERMISSION) {
								this.showPermissionPopup('vibb',error.error.message)
							} else {
								this.showError(error.error)
							}
						} else {
							this.showError(this.$t('Upload Failed.'))
						}
						this.$refs.vibbUploadRef.reset()
					} finally {
                        this.videoUploadProgress = 0
                    }
				}
			}
			this.$refs.vibbUploadRef.reset()
		},
        handleRemoveVideo(videoId){
			try {
				deletePostItem({
					id: videoId
				});
				this.uploadedVideo = null
				this.videoUploadProgress = 0
			} catch (error) {
				this.showError(error.error)
			}
		},
        async handleCreateVibb(){
            this.loadingCreate = true
            try {
                if(this.isContentWarning && this.contentWarningSelecteds.length === 0){
                    return this.showError(this.$t('User must select at least 1 category.'))
                }
                const response = await storeVibb({
                    content: this.description,
                    item_id: this.uploadedVideo.id,
                    comment_privacy: this.commentPrivacy, 
					content_warning_categories: this.contentWarningSelecteds.map(content_warning => content_warning.id),
                    song_id: this.selectedSong?.id
                })
                this.showSuccess(this.$t('Your vibb has been created.'));
                this.$router.push({ name: 'vibb', query: {id: response.id} })
            } catch (error) {
                if (error.error.code == Constant.RESPONSE_CODE_MEMBERSHIP_PERMISSION) {
                    this.showPermissionPopup('vibb',error.error.message)
                } else {
                    this.handleApiErrors(this.error, error)
                }
            } finally {
                this.loadingCreate = false
            }
        },
        handleSelectSong(song){
			this.selectedSong = song
		},
        handleRemoveSong(){
			this.selectedSong = null
		},
        handleRemoveSelectedSong(){
            this.selectedSong = null
            this.$refs.songsRef.reset()
        },
        handlePutContentWarning(status){
            this.isContentWarning = status
        }
    }
}
</script>