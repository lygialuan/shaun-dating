<template>
	<div class="h-full">
		<div class="flex h-full">
			<div class="flex-1 relative" >
				<span class="absolute top-3 left-3 right-3 z-10">
					<div class="flex gap-2 justify-between items-center">
						<div class="flex gap-2">
							<button class="flex justify-center items-center bg-white shadow-md h-8 w-8 rounded-full dark:bg-slate-800 rtl:rotate-180" @click="closeCreateStoryModal()">
								<BaseIcon name="arrow_left" size="20" />
							</button>
							<button class="flex justify-center items-center bg-white shadow-md h-8 w-8 rounded-full dark:bg-slate-800" @click="toggleSongsList()">
								<BaseIcon name="music_notes" size="20" />
							</button>
						</div>
						<BaseButton :loading="loading" @click="postStory()">{{ $t('Post Story') }}</BaseButton>
					</div>
					<span v-if="selected_song" class="bg-black text-white px-2 py-1 text-xs rounded-lg inline-flex items-center gap-1 relative mt-4 z-10">
						<BaseIcon name="music_note" size="16" />
						{{ selected_song.name }}
						<button @click="removeSong" class="absolute -top-2 -end-2 bg-red-600 text-white rounded-full">
							<BaseIcon name="close" size="16" />
						</button>
					</span>
				</span>
				<Songs v-if="showSongsList" class="absolute top-14 left-3 right-3 z-10" v-click-outside="toggleSongsList" @select_song="selectSong"/>
				<div 
					v-if="uploadedVideo" 
					class="absolute inset-0 bg-contain bg-no-repeat lg:rounded-base-lg bg-center"
					:style="{ backgroundImage: `url(${uploadedVideo.video.thumb.url})`, backgroundColor: `${uploadedVideo.video.thumb.params.dominant_color}`}"
				>
				</div>
				<div v-else class="bg-black/50 lg:rounded-base-lg flex items-center justify-center absolute p-5 inset-0">
					<div class="text-center space-y-3 w-full">
						<div class="flex items-center justify-center w-16 h-16 rounded-full mx-auto bg-gray-6 text-main-color dark:bg-slate-600 dark:text-slate-300 dark:border-slate-600 cursor-pointer" @click="triggerUploadVideo">
							<BaseIcon name="video_camera" />
						</div>
						<BaseButton @click="triggerUploadVideo">{{ $t('Upload video') }}</BaseButton>
						<ProgressBar :value="videoUploadProgress" class="w-full"/>
					</div>
				</div>
			</div>
		</div>
		<input type="file" ref="uploadVideoStoryRef" @change="uploadVideo($event)" accept="video/*" class="hidden" />
    </div>
	<CloseButton @click="closeCreateStoryModal()" />
</template>

<script>
import { uploadStoryVideo, storeStory } from '@/api/stories'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import BaseButton from "@/components/inputs/BaseButton.vue"
import Songs from '@/components/stories/Songs.vue'
import CloseButton from '@/components/utilities/CloseButton.vue'
import constant from '@/utility/constant'
import ProgressBar from '@/components/utilities/ProgressBar.vue'

export default {
	components: { BaseIcon, BaseButton, Songs, CloseButton, ProgressBar },
	inject: ['dialogRef'],
	data(){
		return{
			type: 'video',
			selected_song: null,
			showSongsList: false,
			showBackgroundsList: false,
			showAddTextarea: true,
			loading: false,
			uploadedVideo: null,
			videoUploadProgress: 0
		}
	},
	watch: {
        '$route'(){
            this.dialogRef.close()
        }
    },
	mounted(){
		this.triggerUploadVideo()
	},
	methods: {
		uploadVideo(event){
			this.startUploadVideo(event.target.files)
		},
        async startUploadVideo(uploadedFiles){
			for( var i = 0; i < uploadedFiles.length; i++ ){
				if(this.checkUploadedData(uploadedFiles[i], 'video')){
					let formData = new FormData()
					formData.append('file', uploadedFiles[i])
					const onProgress = (progressEvent) => {
						const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
						this.videoUploadProgress = percentCompleted;
					};
					try {
						const response = await uploadStoryVideo(formData, onProgress);
						this.uploadedVideo = response;
					} catch (error) {
						if (error.error.code == constant.RESPONSE_CODE_MEMBERSHIP_PERMISSION) {
							this.showPermissionPopup('story', error.error.message);
						} else {
							this.showError(error.error);
						}
						this.$refs.uploadVideoStoryRef.value = null
					} finally {
						this.videoUploadProgress = 0;
                    }
				}
			}
			this.$refs.uploadVideoStoryRef.value = null
		},
		toggleSongsList(){
			this.showSongsList = !this.showSongsList;
		},
		selectSong(song){
			this.selected_song = song
			this.toggleSongsList()
		},
		removeSong(){
			this.selected_song = null
		},
		async postStory(){
			this.loading = true
			try {
				await storeStory({
					type: this.type,
					song_id: this.selected_song?.id || 0,
					item_id: this.uploadedVideo?.id
				})
				this.showSuccess(this.$t('Your story has been posted.'))
				this.closeCreateStoryModal()
				this.$router.push({'name' : 'home'})
			} catch (error) {
				if (error.error.code == constant.RESPONSE_CODE_MEMBERSHIP_PERMISSION) {
					this.showPermissionPopup('story', error.error.message);
				} else {
					this.showError(error.error)
				}
			} finally {
				this.loading = false
			}
		},
		closeCreateStoryModal(){
            this.dialogRef.close()
        },
		triggerUploadVideo(){
			this.$refs.uploadVideoStoryRef.click()
		}
	}
}
</script>