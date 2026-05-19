<template>
    <FullColumn :keep-right-column="true">
        <template v-slot:center>
            <div v-if="adsInfoRendered" class="main-content-section">
                <div class="main-content-section-header">
                    <h3 class="main-content-section-header-title">{{ id ? $t('Edit Ads') : $t('Create Ads') }}</h3>
                </div>
                <div class="mb-base-2">
                    <label class="block mb-base-1">{{ $t('Ads Name') }}</label>
                    <BaseInputText v-model="adsName" :error="error.name"/>
                </div>
                <div class="mb-base-2">
                    <label class="block mb-base-1">{{ $t('Ads Text') }}</label>
                    <Mentionable ref="mention" v-model="adsContent" :error="error.content" maxRows="5" @update:modelValue="inputChange">
                        <div class="flex justify-between items-center gap-base-2">
                            <EmojiPicker @emoji_click="addEmoji" />
                            <WordCounter :max="config.post.character_max" :data="adsContent" />
                        </div>
                    </Mentionable>
                </div>
                <ProgressBar :value="videoUploadProgress" class="mb-base-2"/>
                <div v-if="showUploadImage || showUploadVideo || showUploadFile">
                    <label class="block mb-base-1">{{ $filters.textTranslate( $t('Select your media (Can upload ONE [duration] seconds video)'), { duration: config.videoDurationConvertNow }) }}</label>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-base-2">
                        <UploadImages v-if="showUploadImage" ref="imagesUploadStatus" @upload="uploadImages">
                            <div class="flex items-center gap-base-1 font-medium">
                                <BaseIcon name="camera" />
                                {{ $t('Photos') }}
                            </div>
                        </UploadImages>
                        <UploadVideo v-if="showUploadVideo" ref="videoUploadStatus" @upload="uploadVideo">
                            <div class="flex items-center gap-base-1 font-medium">
                                <BaseIcon name="video_camera" />
                                {{ $t('Video') }}
                            </div>
                        </UploadVideo>
                        <UploadFiles v-if="showUploadFile" ref="fileUploadStatus" @upload="uploadFiles">
                            <div class="flex items-center gap-base-1 font-medium">
                                <BaseIcon name="paperclip" />
                                {{ $t('Attachment') }}
                            </div>
                        </UploadFiles>
                    </div>
                </div>
                <div v-if="adsMedias.length" class="flex flex-wrap gap-2 mt-base-2 rounded-md border" :class="error.items ? 'border-invalid-color p-2' : 'border-transparent'">
                    <Draggable :list="adsMedias" @dragover="preventPhotosListDrag($event)" @dragend="endDraggingPhotos($event)" class="flex flex-wrap gap-2">
                        <TransitionGroup type="transition">
                        <div
                            v-for="image in adsMedias"
                            :key="image.subject.id"
                            class="inline-block w-20 h-20 bg-cover bg-center relative cursor-pointer border border-divider rounded-md dark:border-white/10"
                            :style="{ backgroundImage: `url(${image.subject.url})`}"
                        >
                            <button class="inline-flex absolute top-1 end-1 bg-black/30 text-white w-4 h-4" @touchend.prevent="removeImage(image)" @click.stop.prevent="removeImage(image)">
                                <BaseIcon name="close" size="16" />
                            </button>
                        </div>
                        </TransitionGroup>
                    </Draggable>
                    <button class="add-images-icon inline-flex items-center justify-center w-20 h-20 bg-cover bg-center text-main-color border border-divider dark:text-white/50 dark:border-white/10 rounded-md hover:bg-hover" @click="this.$refs.imagesUploadStatus.open()">
                        <BaseIcon name="photo" />
                    </button>
                </div>
                <div v-if="adsLoadingUploadFiles.length || adsUploadedFiles.length" class="flex flex-wrap gap-base-2 mt-base-2">
                    <div v-for="index in adsLoadingUploadFiles" :key="index" class="inline-block w-20 relative rounded-md border border-divider dark:border-white/10 status-box-image-upload-list-loading">
                        <span class="loading-icon">
                            <div class="loader"></div>
                        </span>
                    </div>
                    <div v-for="file in adsUploadedFiles" :key="file.id" class="bg-web-wash border border-divider p-base-2 rounded-md relative max-w-[200px] dark:bg-dark-web-wash dark:border-slate-700">
                        <div class="flex items-center gap-2">
                            <BaseIcon name="file" />
                            <span class="truncate">{{ file.subject.name }}</span>
                        </div>
                        <button class="shadow-md inline-flex items-center justify-center absolute -top-2 -end-2 bg-white border border-divider text-main-color rounded-full w-5 h-5" @click="removeUploadedFile(file)">
                            <BaseIcon name="close" size="16" />
                        </button>
                    </div>
                </div>
                <div v-if="adsUploadedVideo">
                    <div class="relative bg-black">
                        <img class="w-full" :class="(aspectRatioVideo(adsUploadedVideo.subject.thumb.params) == 'horizontal') ? '' : 'max-w-[200px] mx-auto'" :src="adsUploadedVideo.subject.thumb.url" />
                        <button @click="removeUploadedVideo(adsUploadedVideo)" class="absolute top-2 end-2 flex items-center justify-center w-7 h-7 rounded-full bg-primary-color dark:bg-dark-primary-color">
                            <BaseIcon name="close" size="20" class="text-white" />
                        </button>					
                    </div>
                </div>
                <small v-if="error.items" class="p-error">{{error.items}}</small>
            </div>
            <div v-if="adsInfoRendered" class="main-content-section">
                <div class="main-content-section-header">
                    <h3 class="main-content-section-header-title">{{ $t('Demographics & Targeting features') }}</h3>
                </div>
                <div class="mb-base-2">{{ $t('Target your audience based on hashtags they use, followers of similar accounts.') }}</div>
                <div v-if="adsConfig" class="mb-base-2">
                    <label class="block mb-base-1">{{ $t('Gender') }}</label>
                    <div class="flex flex-wrap gap-x-5 gap-y-base-2">
                        <div class="flex items-center gap-base-2">
                            <BaseRadio :value="0" v-model="genderId" inputId="all" name="genders" />
                            <label for="all">{{ $t('All') }}</label>
                        </div>
                        <div v-for="gender in adsConfig.genders" :key="gender.id" class="flex items-center gap-base-2">
                            <BaseRadio :value="gender.id" v-model="genderId" :inputId="`${gender.name}_${gender.id}`" name="genders" />
                            <label :for="`${gender.name}_${gender.id}`">{{ gender.name }}</label>
                        </div>
                    </div>
                </div>
                <div v-if="adsConfig" class="mb-base-2">
                    <label class="block mb-base-1">{{ $t('Age') }}</label>
                    <div class="flex items-center gap-base-2 mb-2">
                        <BaseRadio :value="ageTypeAny" v-model="selectedAge" inputId="any" name="age" />
                        <label for="any">{{ $t('Any') }}</label>
                    </div>
                    <div class="flex items-center gap-base-2 mb-2">
                        <BaseRadio :value="ageTypeRange" v-model="selectedAge" inputId="any_range" name="age" />
                        <label for="any_range">{{ $t('Age Range') }}</label>
                    </div>
                    <div v-if="selectedAge === ageTypeRange" class="flex gap-base-2">
                        <BaseInputText v-model="adsAgeFrom" :placeholder="$t('From')" :error="error.age_from"/>
                        <BaseInputText v-model="adsAgeTo" :placeholder="$t('To')" :error="error.age_to" />
                    </div>
                </div>
                <div class="mb-base-2">
                    <label class="block mb-base-1">{{ $t('Target hashtags') }} <InfoTooltip :content="getHelpTextHashTag()"/></label>
                    <BaseSelectHashtags v-model="hashtags" :error="error.hashtags" />
                </div>
                <div class="mb-base-2">
                    <BaseSelectLocation v-model="location" :show-address="false" :error="error">
                        <label class="block mb-base-1">{{ $t('Location') }} <InfoTooltip :content="getHelpTextLocation()"/></label>
                    </BaseSelectLocation>
                </div>           
            </div>
            <div v-if="!id" class="main-content-section">
                <div class="main-content-section-header">
                    <h3 class="main-content-section-header-title">{{ $t('Budget & Scheduling') }}</h3>
                </div>
                <div class="mb-base-2">{{ $t('Set a budget that fits your needs and a date range to take more control of your spend.') }}</div>
                <div class="mb-base-2">
                    <label class="block mb-base-1">{{ $t('Daily budget') }}</label>
                    <BaseInputText v-model="adsAmount" :error="error.daily_amount"/>
                </div>
                <div class="mb-base-2">
                    <label class="block mb-base-1">{{ $t('Date Range') }} <InfoTooltip :content="getHelpTextDateRange()"/></label>
                    <div class="flex flex-col sm:flex-row gap-base-2 mb-1">
                        <BaseCalendar v-model="adsStartDate" :placeholder="$t('Start Date')" :error="error.start" />
                        <BaseCalendar v-model="adsEndDate" :placeholder="$t('End Date')" :error="error.end" />
                    </div>
                    <p class="text-base-xs text-sub-color dark:text-slate-400">{{ $t('Ads that run for at least 4 days tend to get better results.') }}</p>
                </div>
            </div>
            <div class="main-content-section">
                <div class="main-content-section-header">
                    <h3 class="main-content-section-header-title">{{ $t('Payment method') }}</h3>
                </div>
                <div class="mb-base-2">
                    <span v-if="config.wallet.enable">{{ $t('Ewallet') + ' ( ' + exchangeTokenCurrency(user.wallet_balance) }} ~ {{ exchangeCurrency(user.wallet_balance) + ')' }}</span>
                    <span v-else>{{ $t('The eWallet system is not available at this time.') }}</span>
                </div>
                <BaseButton v-if="config.wallet.enable" :href="baseUrl + '/wallet'" target="_blank">{{ $t('Add funds') }}</BaseButton>
            </div>
        </template>
        <template v-slot:right>
            <div v-if="adsInfoRendered" class="main-content-section">
                <div class="main-content-section-header">
                    <h3 class="main-content-section-header-title">{{ $t('Ads Preview') }}</h3>
                </div>
                <div>
                    <div class="feed-item-header flex mb-base-2">
                        <div class="feed-item-header-img">
                            <Avatar :user="user" :activePopover="false" :router="false"/>
                        </div>
                        <div class="feed-item-header-info flex-grow ps-base-2">
                            <div class="feed-item-header-info-title flex justify-between items-start gap-base-2">
                                <div class="whitespace-normal">
                                    <UserName :user="user" :truncate="false" :activePopover="false" :router="false" />
                                </div>
                                <div>
                                    <BaseIcon size="20" name="more_horiz_outlined" class="feed-item-dropdown text-sub-color dark:text-slate-400"/>
                                </div>
                            </div>
                            <div class="feed-item-header-info-date block w-max text-sub-color text-xs dark:text-slate-400">{{ adsInfo?.created_at ? adsInfo?.created_at : $t('Just now') }}</div>
                        </div>
                    </div>
                    <div class="feed-item-content">                  
                        <div class="whitespace-pre-wrap break-word feed-item-content-message mb-base-2">{{ adsContent }}</div>
                        <VueperSlides v-if="adsMedias.length" ref="photosFeed" :slide-ratio="aspectRatioImage(adsMedias[0].subject.params)" :infinite="false" :arrows="true" :bullets="false" disable-arrows-on-edges
                            :rtl="user.rtl ? true : false" :touchable="false" :gap="5" class="activity_content_photos_list no-shadow">
                            <VueperSlide v-for="(adsMedia, index) in adsMedias" :key="adsMedia.id" class="cursor-pointer bg-no-repeat"
                                :style="{ backgroundColor: `${adsMedia.subject.params.dominant_color ? adsMedia.subject.params.dominant_color : '#000'}`}"
                                :image="adsMedia.subject.url" @click="clickPhotoImage(index)"></VueperSlide>
                            <template #arrow-left>
                                <div class="arrow_slider arrow_slider_left"></div>
                            </template>
                            <template #arrow-right>
                                <div class="arrow_slider arrow_slider_right"></div>
                            </template>
                        </VueperSlides>
                        <Loading v-if="adsLoadingFetchLink"/>
						<div v-if="!adsLoadingFetchLink && adsSharedLink" class="relative border border-divider dark:border-slate-700">
							<LinkFetched :postItemsList="[adsSharedLink]"/>
							<button @click="removeLinkFetched()" class="absolute top-2 end-2 fetched-link-close flex items-center justify-center w-7 h-7 rounded-full bg-primary-color dark:bg-dark-primary-color">
								<BaseIcon name="close" size="20" class="text-white" />
							</button>
						</div>
						<div v-if="adsUploadedVideo">
							<div class="relative bg-black">
								<img class="w-full" :class="(aspectRatioVideo(adsUploadedVideo.subject.thumb.params) == 'horizontal') ? '' : 'max-w-[200px] mx-auto'" :src="adsUploadedVideo.subject.thumb.url" />				
							</div>
						</div>
                        <div v-if="adsUploadedFiles.length" class="flex flex-wrap gap-base-2 mb-base-2">
							<div v-for="file in adsUploadedFiles" :key="file.id" class="bg-white text-inherit border border-divider px-base-2 py-2 rounded-1000 relative max-w-[200px] dark:bg-dark-web-wash dark:border-slate-700">
								<div class="flex items-center gap-2">
									<BaseIcon name="file" />
									<span class="truncate">{{ file.subject.name }}</span>
								</div>
							</div>
						</div>
                    </div>
                    <div class="mt-3 mb-base-2 dark:text-white">
                        <div class="feed-main-action flex justify-between border-t border-divider pt-base-2 dark:border-white/10 mb-base-2">
                            <div>
                                <BaseIcon name="heart"/>
                            </div>
                            <div>
                                <BaseIcon name="comment"/>
                            </div>
                            <div>
                                <BaseIcon name="share"/>
                            </div>
                            <div>
                                <BaseIcon name="bookmarks" />
                            </div>
                        </div>
                    </div>
                    <template v-if="adsInfo">
                        <div class="border-t border-divider pt-base-2 mt-base-2 dark:border-white/10">
                            <div class="font-semibold mb-base-2">{{ $t('Date') }}</div>
                            <div class="flex gap-base-2 justify-between mb-base-2">
                                <div>{{ $t('Start Date') }}</div>
                                <div class="text-end">{{ adsStartDate }}</div>
                            </div>
                            <div class="flex gap-base-2 justify-between mb-base-2">
                                <div>{{ $t('End Date') }}</div>
                                <div class="text-end">{{ adsEndDate }}</div>
                            </div>
                        </div>
                    </template>
                    <div class="border-t border-divider pt-base-2 mt-base-2 dark:border-white/10">
                        <div class="font-semibold mb-base-2">{{ $t('Demographics & Targeting features') }}</div>
                        <div class="flex gap-base-2 justify-between mb-base-2">
                            <div>{{ $t('Gender') }}</div>
                            <div class="text-end">{{ genderName }}</div>
                        </div>
                        <div class="flex gap-base-2 justify-between mb-base-2">
                            <div>{{ $t('Age') }}</div>
                            <div class="text-end">
                                <span v-if="selectedAge == ageTypeAny">{{ $t('Any') }}</span>
                                <span v-if="selectedAge == ageTypeRange">{{ (adsAgeFrom || 0) + '-' + (adsAgeTo || 0) }}</span>
                            </div>
                        </div>
                        <div class="flex gap-base-2 justify-between mb-base-2">
                            <div>{{ $t('Hashtags') }}</div>
                            <div class="text-end break-word">
                                <span v-for="(hashtag, index) in hashtags" :key="hashtag.name">
                                    {{ '#' + hashtag.name + (index == (hashtags.length - 1) ? '' : ', ') }}
                                </span>
                            </div>
                        </div>
                        <div v-if="location.address_full" class="flex gap-base-2 justify-between mb-base-2">
                            <div>{{ $t('Locations') }}</div>
                            <div class="text-end break-word">{{ location.address_full }}</div>
                        </div>

                        <template v-if="adsInfo">
                            <div class="flex gap-base-2 justify-between mb-base-2">
                                <div>{{ $t('Daily budget') }}</div>
                                <div class="text-end break-word">{{ adsInfo.daily_amount }}</div>
                            </div>
                        </template>
                    </div>
                    <template v-if="adsInfo">
                        <div class="border-t border-divider pt-base-2 mt-base-2 dark:border-white/10">
                            <div class="font-semibold mb-base-2">{{ $t('Payment summary') }}</div>
                            <div class="flex gap-base-2 justify-between mb-base-2">
                                <div>{{ $t('Total budget') }}</div>
                                <div class="text-end">{{ adsInfo.total_amount }}</div>
                            </div>
                            <div class="flex gap-base-2 justify-between mb-base-2">
                                <div>{{ $t('Spent Amount') }}</div>
                                <div class="text-end">{{ adsInfo.total_delivery_amount }}</div>
                            </div>
                        </div>
                        <div class="border-t border-divider pt-base-2 mt-base-2 dark:border-white/10">
                            <div class="flex gap-base-2 justify-between mb-base-2 font-semibold">
                                <div>{{ $t('Available Amount') }}</div>
                                <div class="text-end">{{ adsInfo.total_available_amount }}</div>
                            </div>
                            <div class="flex gap-base-2 justify-between mb-base-2">
                                <div>{{$t('VAT')}} ({{adsInfo.vat}}%)</div>
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <div class="border-t border-divider pt-base-2 mt-base-2 dark:border-white/10">
                            <div class="font-semibold mb-base-2">{{ $t('Payment summary') }}</div>
                            <div class="mb-base-2">{{ $filters.numberShortener(runningDate(adsStartDate, adsEndDate), $t('Your ads will run for [number] day'), $t('Your ads will run for [number] days')) }}</div>
                            <div class="flex gap-base-2 justify-between mb-base-2">
                                <div>{{ $t('Total budget') }}</div>
                                <div class="text-end">{{ exchangeTokenCurrency(adsAmount * runningDate(adsStartDate, adsEndDate)) }}</div>
                            </div>
                            <div class="flex gap-base-2 justify-between mb-base-2">
                                <div>{{ $t('Estimated VAT') }} ({{config.advertising.vat}}%)</div>
                                <div class="text-end">{{ exchangeTokenCurrency(adsAmount * runningDate(adsStartDate, adsEndDate) * Number(config.advertising.vat) / 100) }}</div>
                            </div>
                        </div>
                        <div class="border-t border-divider pt-base-2 mt-base-2 dark:border-white/10">
                            <div class="flex gap-base-2 justify-between mb-base-2 font-semibold">
                                <div>{{ $t('Total amount') }}</div>
                                <div class="text-end">{{ exchangeTokenCurrency(adsAmount * runningDate(adsStartDate, adsEndDate) * (100 + Number(config.advertising.vat)) / 100) }}</div>
                            </div>
                        </div>
                    </template>
                    <div class="grid gap-base-2">
                        <BaseButton @click="id ? editAds() : createAds()">{{ id ? $t('Save Campaign') : $t('Launch Campaign') }}</BaseButton>
                        <BaseButton v-if="id > 0" :to="{name: 'advertisings'}" type="outlined">{{ $t('Cancel') }}</BaseButton>
                    </div>
                </div>
            </div>
        </template>
    </FullColumn>
</template>

<script>
import { mapState ,mapActions} from 'pinia';
import { getAdvertisingConfig, storeAdvertising, getAdvertisingDetail, storeEditAdvertising, storeValidateAdvertising } from '@/api/ads'
import { uploadPostImages, deletePostItem, fetchLink, uploadPostVideo, uploadPostFiles } from '@/api/posts'
import { useAuthStore } from '@/store/auth'
import { useAppStore } from '@/store/app';
import { VueDraggableNext } from "vue-draggable-next";
import { VueperSlides, VueperSlide } from 'vueperslides'
import { decodeHtml } from '@/utility/index'
import Constant from '@/utility/constant'
import FullColumn from '@/components/columns/FullColumn.vue'
import Mentionable from "@/components/utilities/Mentionable.vue"
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import BaseCalendar from '@/components/inputs/BaseCalendar.vue'
import BaseSelectLocation from '@/components/inputs/BaseSelectLocation.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'
import InfoTooltip from '@/components/utilities/InfoTooltip.vue';
import BaseButton from '@/components/inputs/BaseButton.vue'
import PasswordModal from '@/components/modals/PasswordModal.vue'
import BaseRadio from '@/components/inputs/BaseRadio.vue'
import LinkFetched from "@/components/posts/LinkFetched.vue"
import Loading from '@/components/utilities/Loading.vue'
import EmojiPicker from "@/components/utilities/EmojiPicker.vue"
import ProgressBar from '@/components/utilities/ProgressBar.vue'
import WordCounter from "@/components/utilities/WordCounter.vue";
import UploadImages from '@/components/utilities/UploadImages.vue'
import UploadVideo from '@/components/utilities/UploadVideo.vue'
import UploadFiles from '@/components/utilities/UploadFiles.vue'
import BaseSelectHashtags from '@/components/inputs/BaseSelectHashtags.vue'

export default {
    props: ['id'],
    components: { 
        Draggable: VueDraggableNext,
        FullColumn,
        Mentionable,
        BaseInputText,
        BaseCalendar,
        BaseSelectLocation,
        BaseIcon,
        Avatar,
        UserName,
        InfoTooltip,
        BaseButton,
        BaseRadio,
        LinkFetched,
        Loading,
        VueperSlides,
        VueperSlide,
        EmojiPicker,
        ProgressBar,
        WordCounter,
        UploadImages,
        UploadVideo,
        UploadFiles,
        BaseSelectHashtags
     },
    data(){
        return{
            ageTypeAny: Constant.AGE_TYPE.ANY,
            ageTypeRange: Constant.AGE_TYPE.RANGE,
            adsConfig: null,
            adsInfo: null,
            adsType: 'text',
            adsName: '',
            adsContent: '',
            adsMedias: [],
			adsSavedLink: [],
            adsLoadingFetchLink: false,
            adsSharedLink: null,
			adsUploadedVideo: null,
            adsLoadingUploadFiles: [],
			adsUploadedFiles: [],
            genderId: 0,
            selectedAge: Constant.AGE_TYPE.ANY,
            adsAgeFrom: null,
            adsAgeTo: null,
            hashtags: [],
            hashtagsList: [],
            location: {
				country_id: null,
				state_id: null,
				city_id: null,
				zip_code: null,
				address: null,
                address_full: null
			},
            error: {
                name: null,
                content: null,
                daily_amount: null,
                start: null,
                end: null,
                age_from: null,
                age_to: null,
                items: null,
                country_id: null,
				state_id: null,
				city_id: null,
				zip_code: null,
                address: null,
                address_full: null,
                hashtags: null
            },
            adsStartDate: 0,
            adsEndDate: 0,
            adsAmount: 0,
            showUploadImage: true,
			showUploadVideo: false,
			showUploadFile: false,
            videoUploadProgress: 0
        }
    },
    computed:{
        ...mapState(useAuthStore, ['user']),
        ...mapState(useAppStore, ['config']),
        adsInfoRendered(){
            return !this.id || (this.id && this.adsInfo)
        },
        baseUrl(){
            return window.siteConfig.siteUrl
        },
        genderName() {
            const gender = this.adsConfig?.genders.find(gender => gender.id === this.genderId);
            return gender ? gender.name : this.$t('All');
        }
    },
    watch:{
        selectedAge(){
            switch(this.selectedAge){
                case this.ageTypeAny:
                    this.adsAgeFrom = this.adsAgeTo = null
                    break;
            }
        }
    },
    mounted(){
        if (! this.config.advertising.enable) {
            return this.$router.push({ 'name': 'permission' })
        }

        this.getAdvertisingConfig()
        if(this.id){
            this.getAdsDetailEdit(this.id).then(() => {
                this.checkType()
            });
        }
        
        this.showUploadVideo = this.config.ffmegEnable,
		this.showUploadFile = this.config.post.enable_file
    },
    methods: {
        ...mapActions(useAppStore, ['setErrorLayout']),
        async getAdsDetailEdit(adsId){
            try {
                const response = await getAdvertisingDetail(adsId);
                this.adsInfo = response,
                this.adsType = this.adsInfo.post.type
                this.adsName = this.adsInfo.name
                this.adsContent = decodeHtml(this.adsInfo.post.content)
                this.genderId = this.adsInfo.gender
                this.selectedAge = (this.adsInfo.age_from == 0 && this.adsInfo.age_to == 0) ? this.ageTypeAny : this.ageTypeRange
                this.adsAgeFrom = this.adsInfo.age_from ? this.adsInfo.age_from : 0
                this.adsAgeTo = this.adsInfo.age_to ? this.adsInfo.age_to : 0
                this.hashtags = this.adsInfo.hashtags,
                this.location.country_id = this.adsInfo.country_id
                this.location.state_id = this.adsInfo.state_id
                this.location.city_id = this.adsInfo.city_id
                this.location.zip_code = this.adsInfo.zip_code
                this.location.address = this.adsInfo.address
                this.location.address_full = this.adsInfo.address_full
                this.adsStartDate = this.adsInfo.start
                this.adsEndDate = this.adsInfo.end
                switch(this.adsType){
                    case 'photo':
                        this.adsMedias = response.post.items
                        break;
                    case 'link':
                        this.adsSharedLink = response.post.items[0]
                        break;
                    case 'video':
                        this.adsUploadedVideo = response.post.items[0]
                        break;
                    case 'file':
                        this.adsUploadedFiles = response.post.items
                        break;
                }
            } catch (error) {
                //this.showError(error.error)
                this.setErrorLayout(true)
            }
        },
        async getAdvertisingConfig(){
            try {
                const response = await getAdvertisingConfig()
                this.adsConfig = response
            } catch (error) {
                this.showError(error.error)
            }
        },
        checkType() {
			if(this.adsMedias.length){					
				this.adsType = 'photo'
			}else if(this.adsUploadedVideo){					
				this.adsType = 'video'
			}else if (this.adsSharedLink) {
				this.adsType = 'link'
			}else if (this.adsUploadedFiles.length) {
				this.adsType = 'file'
			}else{
				this.adsType = 'text'
			}

			this.showUploadVideo = false
			if (window._.includes(['text'],this.adsType) && this.config.ffmegEnable) {
				this.showUploadVideo = true
			}

            this.showUploadFile = false
			if (window._.includes(['text', 'file'],this.adsType) && this.config.post.enable_file) {
				this.showUploadFile = true
			}

			this.showUploadImage = false
			if (!window._.includes(['file', 'video', 'link'],this.adsType)) {
				this.showUploadImage = true
			}
		},
        async uploadImages(event){
			this.startUploadImages(event.target.files)
		},
		async pasteImage(event){
			this.startUploadImages(event.clipboardData.items, true)
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
					try {
						const response = await uploadPostImages(formData);
						this.adsMedias.push(response);
						this.checkType()             
					} catch (error) {
						this.showError(error.error)
                        this.$refs.imagesUploadStatus.reset()
					}	
				}
			}
            this.$refs.imagesUploadStatus.reset()
		},
        async removeImage(image) {
            try {
                if (image.post_id == 0) {
                    await deletePostItem({
                        id: image.id
                    });
                }
                this.adsMedias = this.adsMedias.filter(imageTmp => imageTmp.id !== image.id);
                this.checkType()
                this.$refs.imagesUploadStatus.reset()
            } catch (error) {
                this.showError(error.error)
            }
        },
        uploadVideo(event){
			this.startUploadVideo(event.target.files)	
		},
        async startUploadVideo(uploadedFiles){			
			for( var i = 0; i < uploadedFiles.length; i++ ){
				if(this.checkUploadedData(uploadedFiles[i], 'video')){
					if (this.adsUploadedVideo) {
						this.removeUploadedVideo(this.adsUploadedVideo)
					}
					let formData = new FormData()
					formData.append('file', uploadedFiles[i])
                    formData.append('is_converted', 1)
                    const onProgress = (progressEvent) => {
						const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
						this.videoUploadProgress = percentCompleted;
					};
					try {
						const response = await uploadPostVideo(formData, onProgress);
						this.adsUploadedVideo = response;
						this.checkType()
                        this.videoUploadProgress = 0      
					} catch (error) {
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
        async removeUploadedVideo(video){
			try {
                if (video.post_id == 0) {
                    await deletePostItem({
                        id: video.id
                    });
                }
				this.adsUploadedVideo = null
				this.checkType()
                this.videoUploadProgress = 0
			} catch (error) {
				this.showError(error.error)
			}
		},
        uploadFiles(event){
			this.startUploadFile(event.target.files)
		},
		async startUploadFile(uploadedFiles){			
			for( var i = 0; i < uploadedFiles.length; i++ ){
				if(this.checkUploadedData(uploadedFiles[i], 'post')){
					let formData = new FormData()
					formData.append('file', uploadedFiles[i])
					this.adsLoadingUploadFiles.unshift(i)
					try {
						const response = await uploadPostFiles(formData);
						this.adsUploadedFiles.push(response);
						this.checkType()
						this.adsLoadingUploadFiles.shift()
					} catch (error) {
						this.showError(error.error)
						this.adsLoadingUploadFiles.shift()
                        this.$refs.fileUploadStatus.reset()
					}	
				}
			}
            this.$refs.fileUploadStatus.reset()
		},
        async removeUploadedFile(file){
			try {
                if (file.post_id == 0) {
                    await deletePostItem({
                        id: file.id
                    });
                }
				
				this.adsUploadedFiles = this.adsUploadedFiles.filter(fileTmp => fileTmp.id !== file.id);
				this.checkType()
                this.$refs.fileUploadStatus.reset()
			} catch (error) {
				this.showError(error.error)
			}
		},
        preventPhotosListDrag(e){
			e.preventDefault()
			e.stopPropagation()
		},
		endDraggingPhotos(e){
			this.setEventDragDrop(e)
		},
        getUrlFromText(text) {
			let links = text.match(Constant.LINK_REGEX);
			if (links)
			{
				let unique_link = links.filter(link => !this.adsSavedLink.includes(link))
				if(unique_link.length > 0){
					return unique_link[0].charAt(0).toLowerCase() + unique_link[0].slice(1);
				}
			}
		},
        async fetchLink(content){
			let fetch_url = this.getUrlFromText(content)
			if(this.adsType != 'text'){
				return
			}
			if (fetch_url && (fetch_url.substring(0, 7) == 'http://' || fetch_url.substring(0, 8) == 'https://' || (fetch_url.substring(0, 4) == 'www.'))){
				this.adsLoadingFetchLink = true
				try {
					const response = await fetchLink({
						url: fetch_url
					});
					this.adsSharedLink = response
					this.adsLoadingFetchLink = false
					this.checkType()
				} catch (error) {
					//this.showError(error.error)
					this.adsLoadingFetchLink = false
				}  
			}
		},
		async removeLinkFetched(runCheck){
			if (typeof runCheck === 'undefined') {
				runCheck = true
			}
			this.adsSavedLink.push(this.adsSharedLink.subject.url)
			try {
                if (this.adsSharedLink.post_id == 0) {
                    await deletePostItem({
                        id: this.adsSharedLink.id
                    });
                }

				this.adsSharedLink = null
				if (runCheck) {
					this.checkType()
				}
			} catch (error) {
				this.showError(error.error)
			}			
		},
        inputChange(content){
            var shareLinkTypingTimer = null;
			clearTimeout(shareLinkTypingTimer);
			shareLinkTypingTimer = setTimeout(() => this.fetchLink(content), 1000);
        },
        async createAds(){
            let idItems = null;
            if(this.adsMedias.length > 0){
                idItems = this.adsMedias.map(x => x.id); 
            }else if(this.adsSharedLink){
                idItems = [this.adsSharedLink.id]
            }else if(this.adsUploadedVideo){
                idItems = [this.adsUploadedVideo.id]
            }else if(this.adsUploadedFiles.length){
                idItems = this.adsUploadedFiles.map(x => x.id); 
            }
            let adsData = {
                type: this.adsType,
                name: this.adsName,
                content: this.adsContent,
                items: idItems,
                gender_id: this.genderId,
                age_type: this.selectedAge,
                age_from: this.adsAgeFrom || 0,
                age_to: this.adsAgeTo || 0,
                hashtags: this.hashtags.map(hashtag => hashtag.name),
                country_id: this.location?.country_id,
                state_id: this.location?.state_id,
                city_id: this.location?.city_id,
                zip_code: this.location?.zip_code,
                address: this.location?.address,
                daily_amount: Number(this.adsAmount),
                start: this.formatDateTime(this.adsStartDate),
                end: this.formatDateTime(this.adsEndDate)
            }
            try {
                await storeValidateAdvertising(adsData)
                this.resetErrors(this.error)
                const passwordDialog = this.$dialog.open(PasswordModal, {
                    props: {
                        header: this.$t('Enter Password'),
                        class: 'password-modal',
                        modal: true,
                        dismissableMask: true,
                        draggable: false
                    },
                    emits: {
                        onConfirm: async (data) => {
                            if (data.password) {
                                try {
                                    adsData.password = data.password
                                    await storeAdvertising(adsData)
                                    this.showSuccess(this.$t('Thank you, your payment has been processed and your ad is scheduled to run now. You can manage all your ads in the ads section.'))
                                    this.$router.push({ name: 'advertisings' })
                                    passwordDialog.close()
                                } catch (error) {
                                    if (error.error.code == Constant.RESPONSE_CODE_MEMBERSHIP_PERMISSION) {
                                        this.showPermissionPopup('post',error.error.message)
                                    } else {
                                        this.handleApiErrors(this.error, error)
                                    }
                                    passwordDialog.close()
                                }
                            }
                        }
                    }
                })
            } catch (error) {
                if (error.error.code == Constant.RESPONSE_CODE_MEMBERSHIP_PERMISSION) {
                    this.showPermissionPopup('post',error.error.message)
                } else {
                    this.handleApiErrors(this.error, error)
                }
            }
        },
        async editAds(){
            try {
                let idItems = null;
                if(this.adsMedias.length > 0){
                    idItems = this.adsMedias.map(x => x.id); 
                }else if(this.adsSharedLink){
                    idItems = [this.adsSharedLink.id]
                }else if(this.adsUploadedVideo){
                    idItems = [this.adsUploadedVideo.id]
                }else if(this.adsUploadedFiles.length){
                    idItems = this.adsUploadedFiles.map(x => x.id); 
                }
                await storeEditAdvertising({
                    id: this.id,
                    type: this.adsType,
                    name: this.adsName,
                    content: this.adsContent,
                    items: idItems,
                    gender_id: this.genderId,
                    age_type: this.selectedAge,
                    age_from: this.adsAgeFrom || 0,
                    age_to: this.adsAgeTo || 0,
                    hashtags: this.hashtags.map(hashtag => hashtag.name),
                    country_id: this.location.country_id,
                    state_id: this.location.state_id,
                    city_id: this.location.city_id,
                    zip_code: this.location.zip_code,
                    address: this.location.address
                })
                this.showSuccess(this.$t('Updated Successfully'))
                this.resetErrors(this.error)
                this.$router.push({ name: 'advertisings' })
            } catch (error) {
                if (error.error.code == Constant.RESPONSE_CODE_MEMBERSHIP_PERMISSION) {
                    this.showPermissionPopup('post',error.error.message)
                } else {
                    this.handleApiErrors(this.error, error)
                }
            }
        },
        addEmoji(emoji){		
			this.$refs.mention.addContent(emoji)
		},
        getHelpTextHashTag() {
            return this.$filters.textTranslate(this.$t('Keyword targeting allows you to reach people on [siteName] based on keywords in their search queries, recent posts, and posts they recently engaged with. This targeting option puts you in the best position to reach the most relevant people, drive engagements, and increase conversions.'), { siteName: this.config.siteName});
        },
        getHelpTextLocation() {
            return this.$filters.textTranslate(this.$t("Advertisers can target their campaigns to specific geographies: countries, regions, metros, cities, postal codes. [siteName] geo targeting is based on location info inside member's profile."), { siteName: this.config.siteName});
        },
        getHelpTextDateRange() {
            return this.$t("Choose how long you want your campaign to run by setting your date range from the calendar. The start date defaults to today's date, but you can change it to any date in the future.");
        }
    }
}
</script>