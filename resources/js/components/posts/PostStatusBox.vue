<template>
	<UploadWrap @drop_data="dropData">
		<div class="status-box-main flex flex-col bg-white border border-white h-full lg:rounded-xl dark:bg-slate-800 dark:border-slate-800">
			<div class="status-box-header flex gap-base-2 items-center pt-10 md:pt-6 px-4 md:px-6 pb-base-2">
				<Avatar :user="user"/>
				<div class="flex-1">
					<UserName :user="user" :activePopover="false" :truncate="false" />
					<template v-if="isGroupPost">
						<BaseIcon name="caret_right" size="16" class="mx-base-1 h-5" />
						<BaseIcon name="user_group" size="16" class="me-base-1 h-5" />
						<GroupName :group="postSubject" :activePopover="false" class="break-word" />
					</template>
				</div>
				<button @click="handleBeforeClose()" class="leading-none -mt-20 md:mt-0">
					<BaseIcon name="close" class="post-status-close-icon text-main-color dark:text-white" />	
				</button>
			</div>
			<div class="status-box-body flex-1 overflow-y-auto px-4 md:px-6 py-base-2">
				<div class="status-box-message">
					<div class="status-box-message-input">
						<Mentionable ref="mention" v-model="post.content" :placeholder="placeholderPostStatus" autofocus @update:modelValue="inputChange" @paste="handlePasteContent" :draft-id="draftKey" class="!bg-transparent border-none !p-0" />
					</div>
					<WordCounter :max="config.post.character_max" :data="post.content" class="text-end mt-base-1"/>
				</div>
				<ProgressBar :value="videoUploadProgress" class="mt-base-2" />
				<ContentWarningWrapper :content-warning-list="contentWarningSelecteds" class="my-base-2">
					<template #hide-button="{ toggle }">
						<button 
							class="absolute top-2 flex items-center justify-center w-7 h-7 rounded-full bg-primary-color dark:bg-dark-primary-color z-10"
							:class="video_upload ? 'end-12' : 'end-2'"
							@click.stop="toggle"
						>
							<BaseIcon name="eye_slash" size="20" />
						</button>
					</template>
					<div class="status-box-items">
						<Loading v-if="loading_fetch_link"/>
						<div v-if="!loading_fetch_link && shared_link" class="relative border border-divider dark:border-slate-700">
							<LinkFetched :postItemsList="[shared_link]"/>
							<button @click="removeLinkFetched()" class="absolute top-2 end-2 fetched-link-close flex items-center justify-center w-7 h-7 rounded-full bg-primary-color dark:bg-dark-primary-color">
								<BaseIcon name="close" size="20" class="text-white" />
							</button>
						</div>
						<div class="status-box-image-upload-block">
							<div v-if="images_upload.length" class="status-box-image-upload-preview">
								<VueperSlides class="no-shadow" :slide-ratio="0.5625" :infinite="false" :bullets="false" disable-arrows-on-edges :touchable="false" transition-speed='200' ref="previewUploadImages" :rtl="user.rtl ? true : false">
									<VueperSlide
										v-for="image in images_upload"
										:key="image.subject.id"
										class="status-box-image-upload-preview-item"									
										:image="image.subject.url"
										:style="{ backgroundColor: `${image.subject.params.dominant_color ? image.subject.params.dominant_color : '#000'}`}"
									>
									</VueperSlide>
									<template #arrow-left>
										<div class="arrow_slider arrow_slider_left"></div>
									</template>
									<template #arrow-right>
										<div class="arrow_slider arrow_slider_right"></div>
									</template>
								</VueperSlides>
							</div>														
						</div>
						<div v-if="video_upload" class="relative bg-black">
							<img class="w-full" :class="(aspectRatioVideo(video_upload.subject.thumb.params) == 'horizontal') ? '' : 'max-w-[200px] mx-auto'" :src="video_upload.subject.thumb.url" />
							<button @click="removeUploadedVideo(video_upload.id)" class="absolute top-2 end-2 flex items-center justify-center w-7 h-7 rounded-full bg-primary-color dark:bg-dark-primary-color">
								<BaseIcon name="close" size="20" class="text-white" />
							</button>					
						</div>
						<div v-if="files_upload_loading.length || files_upload.length" class="flex flex-wrap gap-base-2 -mx-base-2 px-base-2 pt-base-2">
							<div v-for="index in files_upload_loading" :key="index" class="inline-block w-48 relative rounded-md border border-divider dark:border-white/10 status-box-image-upload-list-loading">
								<span class="loading-icon">
									<div class="loader"></div>
								</span>
							</div>
							<div v-for="file in files_upload" :key="file.id" class="bg-web-wash border border-divider p-base-2 rounded-md relative max-w-[200px] dark:bg-dark-web-wash dark:border-slate-700">
								<div class="flex items-center gap-2">
									<BaseIcon name="file" />
									<span class="truncate">{{ file.subject.name }}</span>
								</div>
								<button class="shadow-md inline-flex items-center justify-center absolute -top-2 -end-2 bg-white border border-divider text-main-color rounded-full w-5 h-5" @click="removePostFile(file.id)">
									<BaseIcon name="close" size="16" />
								</button>
							</div>
						</div>
					</div>
				</ContentWarningWrapper>
				<div v-if="images_upload.length || images_upload_loading.length" class="flex flex-wrap gap-2 my-base-2">
					<Draggable v-if="images_upload.length" :list="images_upload" @dragover="preventPhotosListDrag($event)" @dragend="endDraggingPhotos($event)" class="flex flex-wrap gap-2">
						<TransitionGroup type="transition">
						<div
							v-for="(image, index) in images_upload"
							:key="image.subject.id"
							class="inline-block w-20 h-20 bg-cover bg-center relative cursor-pointer border border-divider rounded-md dark:border-white/10"
							:style="{ backgroundImage: `url(${image.subject.url})`}"
							@click="$refs.previewUploadImages.goToSlide(index)"
							@touchend="$refs.previewUploadImages.goToSlide(index)"
						>
							<button class="inline-flex absolute top-1 end-1 bg-black/30 text-white w-4 h-4" @touchend.prevent="removeImage(image.id, index)" @click.stop.prevent="removeImage(image.id, index)">
								<BaseIcon name="close" size="16" />
							</button>
						</div>
						</TransitionGroup>
					</Draggable>
					<div v-for="index in images_upload_loading" :key="index" class="inline-block w-20 h-20 bg-cover bg-center relative rounded-md border border-divider dark:border-white/10 float-start status-box-image-upload-list-loading">
						<span class="loading-icon">
							<div class="loader"></div>
						</span>
					</div>
					<button v-if="images_upload.length || images_upload_loading.length" class="add-images-icon inline-flex items-center justify-center w-20 h-20 text-main-color border border-divider dark:text-white/50 dark:border-white/10 rounded-md hover:bg-hover" @click="this.$refs.imagesUploadStatus.open()">
						<BaseIcon name="photo" />
					</button>
				</div>
				<template v-if="isMediaPost">
					<BaseSelectPaidContent v-if="canCreatePaidContent && !isGroupPost" v-model="paidContentData" class="mb-base-2" />
					<BaseSelectContentWarning v-model="contentWarningSelecteds" @put_content_warning="handlePutContentWarning" class="mb-base-2" />
				</template>
				<div v-if="showPollSection" class="poll-section flex flex-col items-start gap-base-2 mb-base-2">
					<div v-for="(poll, index) in pollsList" :key="index" class="flex items-center gap-base-2 p-base-2 bg-light-web-wash w-full rounded-lg dark:bg-dark-web-wash">
						<input type="text" v-model="pollsList[index]" @change="handleUpdatePollItem" :placeholder="$t('Option') + ' ' + (index + 1)" class="bg-transparent flex-1 outline-none">
						<button v-if="pollsList.length > 1" @click="removePollItem(index)"><BaseIcon name="close" size="16" /></button>
					</div>
					<button class="font-bold text-primary-color dark:text-dark-primary-color" @click="addPollItem">{{$t('Add more option')}}</button>
					<div class="w-full">
						<div class="text-main-color text-lg font-extrabold dark:text-white mb-1">{{ $t('Poll length') }}</div>
						<div class="grid grid-cols-3 gap-base-2">
							<div>
								<label>{{ $t('Days') }}</label>
								<BaseSelect v-model="pollEndDay" :options="dayOptions" optionLabel="label" optionValue="value" />
							</div>
							<div>
								<label>{{ $t('Hours') }}</label>
								<BaseSelect v-model="pollEndHour" :options="hourOptions" optionLabel="label" optionValue="value" :disabled="reachMaxCloseDay" />
							</div>
							<div>
								<label>{{ $t('Minutes') }}</label>
								<BaseSelect v-model="pollEndMinute" :options="minuteOptions" optionLabel="label" optionValue="value" :disabled="reachMaxCloseDay" />
							</div>
						</div>
					</div>
					<BaseButton type="danger" fluid @click="handleRemovePoll">{{ $t('Remove Poll') }}</BaseButton>
				</div>
				<BaseSelectPrivacy v-if="!isGroupPost" v-model="post.comment_privacy" :options="privaciesList" :title="$t('Who can comment?')" :description="$t('Choose who can comment to this post. Anyone mentioned can always comment.')" />
			</div>
			<div class="status-box-action flex flex-wrap justify-between items-center gap-base-2 px-4 md:px-6 py-3 lg:rounded-b-xl bg-white border-t border-divider dark:border-white/10 dark:bg-slate-800 z-10">
				<div class="status-box-action-list flex items-center gap-2">
					<UploadImages v-if="show_upload_image" ref="imagesUploadStatus" @upload="uploadImages" :class="statusBoxActionClass" />
					<UploadVideo v-if="show_upload_video" ref="videoUploadStatus" @upload="uploadVideo" :class="statusBoxActionClass" />
					<UploadFiles v-if="show_upload_file" ref="fileUploadStatus" @upload="uploadFiles" :class="statusBoxActionClass" />
					<TenorGifs v-if="show_upload_image && config.post.enable_gifs" @upload="uploadGif" :class="statusBoxActionClass" />
					<EmojiPicker @emoji_click="addEmoji" :class="statusBoxActionClass" />
					<button v-if="show_create_poll" @click="handleCreatePoll" :class="statusBoxActionClass">
						<BaseIcon name="chart_pie_slice" />
					</button>
					<button v-if="showCreateVibb" @click="createVibb()" :class="statusBoxActionClass">
						<BaseIcon name="vibb" />
					</button>
				</div>
				<BaseButton :loading="loading" :disabled="!isEnablePost" @click="postStatus()">{{$t('Post')}}</BaseButton>
			</div>			
		</div>
	</UploadWrap>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { VueDraggableNext } from "vue-draggable-next";
import { VueperSlides, VueperSlide } from 'vueperslides'
import { uploadPostImages, deletePostItem, fetchLink, uploadPostVideo, uploadPostFiles } from '@/api/posts'
import BaseIcon from "@/components/icons/BaseIcon.vue"
import BaseButton from "@/components/inputs/BaseButton.vue"
import EmojiPicker from "@/components/utilities/EmojiPicker.vue"
import Mentionable from "@/components/utilities/Mentionable.vue"
import Loading from '@/components/utilities/Loading.vue'
import LinkFetched from "@/components/posts/LinkFetched.vue"
import UploadWrap from '@/components/layout/UploadWrap.vue';
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'
import TenorGifs from '@/components/utilities/TenorGifs.vue'
import { useAuthStore } from '@/store/auth';
import { useAppStore } from '@/store/app';
import { usePostStore } from '@/store/post';
import { useUtilitiesStore } from '@/store/utilities'
import { useDraftStore } from '@/store/draft'
import Constant from '@/utility/constant'
import UploadImages from '@/components/utilities/UploadImages.vue'
import UploadVideo from '@/components/utilities/UploadVideo.vue'
import UploadFiles from '@/components/utilities/UploadFiles.vue'
import ProgressBar from '@/components/utilities/ProgressBar.vue'
import BaseSelect from '@/components/inputs/BaseSelect.vue'
import WordCounter from "@/components/utilities/WordCounter.vue"
import BaseSelectPrivacy from "@/components/inputs/BaseSelectPrivacy.vue";
import ContentWarningWrapper from '@/components/utilities/ContentWarningWrapper.vue'
import BaseSelectContentWarning from "@/components/inputs/BaseSelectContentWarning.vue"
import GroupName from '@/components/group/GroupName.vue';
import BaseSelectPaidContent from '@/components/paid_content/BaseSelectPaidContent.vue';

export default {
	components: {
		BaseIcon,
		BaseButton,
		EmojiPicker,
		VueperSlides,
		VueperSlide,
		Draggable: VueDraggableNext,
		LinkFetched,
		Mentionable,
		Loading,
		UploadWrap,
		Avatar,
		UserName,
		TenorGifs,
		UploadImages,
		UploadVideo,
		UploadFiles,
		ProgressBar,
		BaseSelect,
		WordCounter,
		BaseSelectPrivacy,
		ContentWarningWrapper,
		BaseSelectContentWarning,
		GroupName,
		BaseSelectPaidContent
	},
	inject: {
        dialogRef: {
            default: null
        }
    },
	data(){
		return{
			listPosts: [],
			images_upload: [],
			images_upload_loading: [],
			shared_link: null,
			post: {
				type: 'text',
				content: '',
				items: null,
				comment_privacy: 'everyone'
			},
			loading_fetch_link: false,
			saved_link: [],
			video_upload: null,
			loading: false,
			show_upload_image: true,
			show_upload_video: false,
			show_upload_file: false,
			show_create_poll: true,
			files_upload_loading: [],
			files_upload: [],
			chosenType: this.dialogRef?.data?.chosenType,
			postFrom: this.dialogRef?.data?.postFrom,
			postSubject: this.dialogRef?.data?.postSubject,
			videoUploadProgress: 0,
			showPollSection: false,
			pollsList: ['', ''],
			pollEndDay: 1,
			pollEndHour: 0,
			pollEndMinute: 0,
			dayOptions: [],
			hourOptions: [],
			minuteOptions: [],
			maxCloseDay: 0,
			isContentWarning: false,
			contentWarningSelecteds: [],
			paidContentData: null,
			keydownHandler: null
		}
	},
	mounted(){
		this.show_upload_video = this.config.ffmegEnable,
		this.show_upload_file = this.config.post.enable_file
		this.$nextTick(() => {
			switch (this.chosenType) {
				case 'photo':
					this.$refs.imagesUploadStatus.open()
					break;
				case 'video':
					this.$refs.videoUploadStatus.open()
					break;
				case 'file':
					this.$refs.fileUploadStatus.open()
					break;
				default:
					break;
			}
		})
		this.maxCloseDay = this.user.permissions["post.max_close_day"] || 30
		this.dayOptions = this.generateDayOptions()
		this.hourOptions = this.generateHourOptions()
		this.minuteOptions = this.generateMinuteOptions()
		this.me()

		const maskEl = document.querySelector('.p-dialog-mask');
		if (maskEl) {
            maskEl.addEventListener('mousedown', this.onMaskClick, true);
        }

		this.keydownHandler = (e) => {
            if (e.key === 'Escape') {
                this.handleBeforeClose();
            }
        }
        document.addEventListener('keydown', this.keydownHandler);
	},
	unmounted(){
		this.cleanupListeners()
		this.resetPostData()
	},
	computed:{
		...mapState(useAuthStore, ['user']),
		...mapState(useAppStore, ['config', 'screen']),
		...mapState(useUtilitiesStore, ['eventDragDrop']),
		...mapState(useDraftStore, ['drafts']),
		isEnablePost(){
			return (this.post.content.trim() != '' || this.images_upload.length || this.shared_link || this.video_upload || this.files_upload.length) && this.videoUploadProgress == 0 && !this.loading
		},
		reachMaxCloseDay(){
			return this.pollEndDay == this.maxCloseDay
		},
		placeholderPostStatus(){
			return this.$filters.textTranslate(this.$t("What do you want to talk about? [hashtag] Hashtags [mention] Mention"), { hashtag: '#', mention: '@' })
		},
		statusBoxActionClass(){
			return 'status-box-action-list-item p-1 rounded-md hover:bg-web-wash dark:hover:bg-dark-web-wash'
        },
		privaciesList() {
            return [
                { icon: 'globe', name: this.$t('Everyone'), label: this.$t('Everyone can comment'), value: 'everyone' },
                { icon: 'user_check', name: this.$t('Accounts you follow'), label: this.$t('Accounts you follow can comment'), value: 'following' },
                { icon: 'seal_check', name: this.$t('Verified accounts'), label: this.$t('Only Verified accounts can comment'), value: 'verified', isShow: this.config?.userVerifyEnable },
                { icon: 'at', name: this.$t('Only accounts you mention'), label: this.$t('Only accounts you mention can comment'), value: 'mentioned' },
            ]
        },
		canCreatePaidContent(){
			return this.user.can_show_creator_dashboard
		},
		isMediaPost(){
			return this.images_upload.length || this.video_upload
		},
		isGroupPost(){
			return this.postFrom === 'groups'
		},
		draftKey(){
			return 'post_status'
		},
		showCreateVibb(){
			return this.config.ffmegEnable && this.config.vibb.enable
		}
	},
	watch: {
		reachMaxCloseDay(newValue) {
			if (newValue) {
				this.pollEndHour = 0;
				this.pollEndMinute = 0;
			}
		}
	},
	methods: {
		...mapActions(usePostStore, ['postNewFeed']),
		...mapActions(useUtilitiesStore, ['setEventDragDrop']),
		...mapActions(useAuthStore, ['me']),
		...mapActions(useDraftStore, ['removeDraft']),
		async postStatus(){
			try {
				if (this.loading) return;

				// Check if post content is valid
				const hasContent = this.post.content.trim() || this.images_upload.length || this.shared_link || this.video_upload || this.files_upload.length;
				if (!hasContent) {
					this.showError(this.$t('The content is required.'));
					return;
				}

				this.loading = true;

				const { type, content } = this.post;

				// Collect idItems based on available uploads
				const idItems = this.images_upload.length ? this.images_upload.map(x => x.id) :
							this.shared_link ? [this.shared_link.id] :
							this.video_upload ? [this.video_upload.id] :
							this.files_upload.length ? this.files_upload.map(x => x.id) :
							type == 'poll' ? this.pollsList : null

				const postPayload = { 
					type, 
					content, 
					items: idItems, 
					comment_privacy: this.post.comment_privacy, 
					content_warning_categories: this.contentWarningSelecteds.map(content_warning => content_warning.id),
					...this.paidContentData
				};
				if (type === 'poll') {
					postPayload.close_minute = this.pollEndDay * 24 * 60 + this.pollEndHour*60 + this.pollEndMinute;
				}

				if(this.isGroupPost){
					postPayload.source_type = this.postFrom
					postPayload.source_id = this.postSubject.id
				}

				if(this.isContentWarning && this.contentWarningSelecteds.length === 0){
                    return this.showError(this.$t('User must select at least 1 category.'))
                }

				const response = await this.postNewFeed(postPayload, this.postFrom);

				// Reset post state
				this.resetPostData();

				// Show success message
				const message = response.pending
					? this.$t("Your post is pending for approval.")
					: response.queue
						? this.$t('The video in your post is being processed. We will send you a notification when it is done.')
						: this.$t('Your post has been shared.');

				this.showSuccess(message);
				this.closeStatusBox();
			} catch (error) {
				if (error.error.code == Constant.RESPONSE_CODE_MEMBERSHIP_PERMISSION) {
					this.showPermissionPopup('post', error.error.message);
				} else {
					this.showError(error.error);
				}
			} finally {
				this.loading = false;
			}
		},
		uploadImages(event){
			this.startUploadImages(event.target.files)
		},
		handlePasteContent(event){
			const clipboardData = event.clipboardData;
			const items = clipboardData.items;
			for (let i = 0; i < items.length; i++) {
				if (items[i].type.indexOf("image") !== -1) {
					this.startUploadImages(items, true)
				}
			}
		},
		dropImage(event){
			if (this.post.type == 'video') {				
				this.startUploadVideo(event.dataTransfer.files)				
			} else {
				this.startUploadImages(event.dataTransfer.files)
			}		
		},
		async startUploadImages(uploadedFiles, clipboard){
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

					var fileContent = await this.convertImage(blob);
                    formData.append('file', fileContent, blob.name)
					this.images_upload_loading.unshift(i)
					try {
						const response = await uploadPostImages(formData);
						this.images_upload.push(response);
						this.checkType()
						this.images_upload_loading.shift()
					} catch (error) {
						this.showError(error.error)
						this.images_upload_loading.shift()
						this.$refs.imagesUploadStatus.reset()
					}	
				}
			}
			this.$refs.imagesUploadStatus.reset()
		},
		async removeImage(imageId, index){
			try {
				await deletePostItem({
					id: imageId
				});
				this.images_upload = this.images_upload.filter(image => image.id !== imageId);
				this.checkType()
				if(index === 0){
					setTimeout(() => {
						if (this.$refs.previewUploadImages) {
							this.$refs.previewUploadImages.goToSlide(0)
						}						
					}, 25);
				}
				this.$refs.imagesUploadStatus.reset()
			} catch (error) {
				this.showError(error.error)
			}
		},
		addEmoji(emoji){		
			this.$refs.mention.addContent(emoji)
		},
		async fetchLink(content){
			let fetch_url = this.getUrlFromText(content)
			if(this.post.type != 'text'){
				return
			}
			if (fetch_url && (fetch_url.substring(0, 7) == 'http://' || fetch_url.substring(0, 8) == 'https://' || (fetch_url.substring(0, 4) == 'www.'))){
				this.loading_fetch_link = true
				try {
					const response = await fetchLink({
						url: fetch_url
					});
					this.shared_link = response
					this.loading_fetch_link = false
					this.checkType()
				} catch (error) {
					//this.showError(error.error)
					this.loading_fetch_link = false
				}  
			}
		},
		removeLinkFetched(){
			this.saved_link.push(this.shared_link.subject.url)
			try {
				deletePostItem({
					id: this.shared_link.id
				});
				this.shared_link = null
				this.checkType()
			} catch (error) {
				this.showError(error.error)
			}			
		},
		getUrlFromText(text) {
			let links = text.match(Constant.LINK_REGEX);
			if (links)
			{
				let unique_link = links.filter(link => !this.saved_link.includes(link))
				if(unique_link.length > 0){
					return unique_link[0].charAt(0).toLowerCase() + unique_link[0].slice(1);
				}
			}
		},
		closeStatusBox(){
			this.dialogRef.close();
			this.removeDraft(this.draftKey)
		},
		inputChange(content){
			var shareLinkTypingTimer = null;
			clearTimeout(shareLinkTypingTimer);
			shareLinkTypingTimer = setTimeout(() => this.fetchLink(content), 1000);
        },
		checkType() {
			const hasImages = this.images_upload.length > 0;
			const hasVideo = !!this.video_upload;
			const hasLink = !!this.shared_link;
			const hasFiles = this.files_upload.length > 0;
			const hasPolls = this.showPollSection;

			this.post.type = hasImages ? 'photo' :
							hasVideo ? 'video' :
							hasLink ? 'link' :
							hasFiles ? 'file' :
							hasPolls ? 'poll' : 'text';

			this.show_upload_video = this.config.ffmegEnable && this.post.type === 'text';
			this.show_upload_file = this.config.post.enable_file && ['text', 'file'].includes(this.post.type);
			this.show_upload_image = !['file', 'video', 'link', 'poll'].includes(this.post.type);
			this.show_create_poll = this.post.type === 'text';
		},
		uploadVideo(event){
			this.startUploadVideo(event.target.files)	
		},
		dropVideo(event){
			if (this.post.type == 'photo') {
				this.startUploadImages(event.dataTransfer.files)
			} else {
				this.startUploadVideo(event.dataTransfer.files)
			}
		},
		async startUploadVideo(uploadedFiles){			
			for( var i = 0; i < uploadedFiles.length; i++ ){
				if(this.checkUploadedData(uploadedFiles[i], 'video')){
					if (this.video_upload) {
						this.removeUploadedVideo(this.video_upload.id)
					}
					let formData = new FormData()
					formData.append('file', uploadedFiles[i])
					const onProgress = (progressEvent) => {
						const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
						this.videoUploadProgress = percentCompleted;
					};
					try {
						const response = await uploadPostVideo(formData, onProgress);
						this.video_upload = response;
						this.checkType()
						this.videoUploadProgress = 0
					} catch (error) {
						console.log(error);
						if(typeof(error) === 'object'){
							if (error.error.code == Constant.RESPONSE_CODE_MEMBERSHIP_PERMISSION) {
								this.showPermissionPopup('post',error.error.message)
							} else {
								this.showError(error.error)
							}
						} else {
							this.showError(this.$t('Upload Failed.'))
						}
						this.$refs.videoUploadStatus.reset()
						this.videoUploadProgress = 0;
					}
				}
			}
			this.$refs.videoUploadStatus.reset()
		},
		removeUploadedVideo(videoId){
			try {
				deletePostItem({
					id: videoId
				});
				this.video_upload = null
				this.checkType()
				this.videoUploadProgress = 0
			} catch (error) {
				this.showError(error.error)
			}
		},
		dropData(data){
			const fileExtension = data.dataTransfer.files[0].name.split('.').pop().toLowerCase()

			if(data.dataTransfer.files[0].type.split('/')[0] === 'image'){
				this.dropImage(data)
			} else if(this.config.videoExtensionSupport.split(',').includes(fileExtension) && this.config.ffmegEnable){
				this.dropVideo(data)
			} else{
				this.dropImage(data)
			}
		},
		preventPhotosListDrag(e){
			e.preventDefault()
			e.stopPropagation()
		},
		endDraggingPhotos(e){
			this.setEventDragDrop(e)
		},
		uploadFiles(event){
			this.startUploadFile(event.target.files)
		},
		async startUploadFile(uploadedFiles){			
			for( var i = 0; i < uploadedFiles.length; i++ ){
				if(this.checkUploadedData(uploadedFiles[i], 'post')){
					let formData = new FormData()
					formData.append('file', uploadedFiles[i])
					this.files_upload_loading.unshift(i)
					try {
						const response = await uploadPostFiles(formData);
						this.files_upload.push(response);
						this.checkType()
						this.files_upload_loading.shift()
					} catch (error) {
						this.showError(error.error)
						this.files_upload_loading.shift()
						this.$refs.fileUploadStatus.reset()
					}	
				}
			}
			this.$refs.fileUploadStatus.reset()
		},
		async removePostFile(fileId){
			try {
				await deletePostItem({
					id: fileId
				});
				this.files_upload = this.files_upload.filter(file => file.id !== fileId);
				this.checkType()
				this.$refs.fileUploadStatus.reset()
			} catch (error) {
				this.showError(error.error)
			}
		},
		uploadGif(file) {
            this.startUploadImages([file]);
        },
		handleCreatePoll(){
			let permission = 'post.allow_create_poll'
			if(this.checkPermission(permission)){
				this.showPollSection = true
				this.checkType()
				this.$refs.mention.addContent('')
			}
		},
		handleRemovePoll(){
			this.pollsList = ['','']
			this.showPollSection = false
			this.checkType()
			this.$refs.mention.addContent('')
		},
		addPollItem(){
			this.pollsList.push('')
		},
		removePollItem(id){
			this.pollsList.splice(id, 1);
		},
		handleUpdatePollItem(){
			this.checkType()
		},
		generateDayOptions() {
			const dayOptions = [];
			for (let i = 0; i <= this.maxCloseDay; i++) {
				dayOptions.push({ label: `${i}`, value: i });
			}
			return dayOptions;
		},
		generateHourOptions() {
			const hourOptions = [];
			for (let i = 0; i < 24; i++) {
				hourOptions.push({ label: `${i}`, value: i });
			}
			return hourOptions;
		},
		generateMinuteOptions() {
			const minuteOptions = [];
			for (let i = 0; i < 60; i++) {
				minuteOptions.push({ label: `${i}`, value: i });
			}
			return minuteOptions;
		},
		handlePutContentWarning(status){
            this.isContentWarning = status
        },
		async handleBeforeClose(){
            const draftKey = this.draftKey;
            const hasDraft = this.drafts && this.drafts[draftKey] && this.drafts[draftKey].trim() !== '';
            if (hasDraft) {
                const shouldLeave = await this.showHasDraftPopup(this.$t("You haven't finished your post yet. Do you want to leave without finishing?"));
                if (!shouldLeave) return;
            }
			this.dialogRef.close();
        },
		onMaskClick(e) {
            if (e.target.classList.contains('p-dialog-mask')) {
                this.handleBeforeClose();
            }
        },
		cleanupListeners() {
            const maskEl = document.querySelector('.p-dialog-mask');
            if (maskEl) {
                maskEl.removeEventListener('mousedown', this.onMaskClick, true);
            }
            if (this.keydownHandler) {
                document.removeEventListener('keydown', this.keydownHandler);
                this.keydownHandler = null;
            }
        },
		resetPostData(){
			this.post = { type: 'text', content: '', items: null };
			this.images_upload = [];
			this.files_upload = [];
			this.pollsList = [];
			this.shared_link = null;
			this.video_upload = null;
		}
	}
}
</script>