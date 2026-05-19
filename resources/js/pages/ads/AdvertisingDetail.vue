<template>
    <FullColumn :keep-right-column="true">
        <template v-slot:center>
            <div class="main-content-section">
                <Loading v-if="loadingAdsInfo"/>
                <div v-else class="flex gap-base-2 justify-between items-start mb-base-2">
                    <div class="flex-1">
                        <div class="flex gap-base-2">
                            <span class="font-bold">{{ adsInfo.name }}</span>
                            <router-link :to="{name: 'advertising_create', params: {id: adsInfo.id}}" v-if="adsInfo.canEdit" class="h-6 text-inherit flex items-center self-start">
                                <BaseIcon name="pencil" size="16" />
                            </router-link>
                        </div>
                        <div class="feed-item-header-info-date text-xs text-sub-color dark:text-slate-400">{{ adsInfo.created_at }}</div>
                    </div>
                    <BaseSelectStatus v-model="adsInfo.status" :item="adsInfo" />
                </div>
                <div class="relative overflow-x-auto border border-b-0 border-table-border-color md:rounded-md dark:border-white/10">
                    <table class="s-table w-full text-sm whitespace-nowrap text-center">
                        <thead class="s-table-header text-xs uppercase bg-table-header-color dark:bg-dark-web-wash">
                            <tr>
                                <th scope="col" class="p-3 text-start">{{$t('Date')}}</th>
                                <th scope="col" class="p-3 w-10">{{$t('Views')}}</th>
                                <th scope="col" class="p-3 w-10">{{$t('Clicks')}}</th>
                                <th scope="col" class="p-3 w-10">{{$t('Total spent')}}</th>
                            </tr>
                        </thead>
                        <tbody class="s-table-body">
                            <tr v-if="loadingAdvertisingReport">
                                <td colspan="4"><Loading class="mt-base-2"/></td>
                            </tr>
                            <template v-else>
                                <template v-if="advertisementReportsList.length">                 
                                    <tr v-for="advertisementReportItem in advertisementReportsList" :key="advertisementReportItem.id" class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b border-table-border-color dark:border-gray-700">
                                        <td class="p-3">
                                            <div class="flex gap-4 justify-between items-center text-start">
                                                <div>
                                                    <div class="font-medium">{{ advertisementReportItem.date }} </div>
                                                    <div class="text-base-xs text-sub-color dark:text-slate-400">{{ advertisementReportItem.created_at }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="p-3">{{ advertisementReportItem.view_count }}</td>
                                        <td class="p-3">{{ advertisementReportItem.click_count }}</td>
                                        <td class="p-3">{{ advertisementReportItem.total_amount }}</td>
                                    </tr>
                                </template>
                                <tr v-else class="border-b border-table-border-color dark:border-gray-700">
                                    <td colspan="5" class="p-3">{{$t('No Reports')}}</td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <div v-if="loadmoreAdvertisingReport" class="text-center my-base-2">
                    <BaseButton @click="getAdsReport(id, advertisementReportsPage)" :loading="loadingViewMoreReport">{{$t('View more')}}</BaseButton>
                </div>
            </div>
        </template>
        <template v-if="adsInfo?.post" v-slot:right>
            <div class="main-content-section">
                <div class="main-content-section-header">
                    <h3 class="main-content-section-header-title">{{ $t('Ad Summary') }}</h3>
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
                                <router-link :to="{name: 'post', params: {id: adsInfo.post.id}}">
                                    <BaseIcon name="more_horiz_outlined" size="20" class="feed-item-dropdown text-sub-color dark:text-slate-400"/>
                                </router-link>
                            </div>
                            <span class="feed-item-header-info-date text-sub-color text-xs dark:text-slate-400">
                                <router-link :to="{name: 'post', params: {id: adsInfo.post.id}}" class="text-inherit">{{ adsInfo.created_at }}</router-link>
                            </span>
                        </div>
                    </div>
                    <div class="feed-item-content mb-base-2">
                        <ContentHtml 
                            :content="adsContent" 
                            :mentions="adsInfo.post.mentions"
                            :can-translate="adsInfo.post.canContentTranslate"
                            :subject-id="adsInfo.post.id"
                            subject-type="posts"
                        />       
                        <VueperSlides v-if="adsMedias.length" :slide-ratio="aspectRatioImage(adsMedias[0].subject.params)" :infinite="false" :arrows="true" :bullets="false" disable-arrows-on-edges
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
						<div v-if="adsSharedLink" class="relative border border-divider dark:border-slate-700">
							<LinkFetched :postItemsList="[adsSharedLink]"/>
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
                    <div class="flex flex-col gap-base-2 dark:text-white">
                        <div class="feed-item-like">
                            <router-link :to="{name: 'post', params: {id: adsInfo.post.id}}" class="feed-item-like-text inline-block text-inherit dark:text-white">{{ $filters.numberShortener(adsInfo.post.like_count, $t('[number] like'), $t('[number] likes')) }}</router-link> &middot;
                            <router-link :to="{name: 'post', params: {id: adsInfo.post.id}}" class="feed-item-like-text inline-block text-inherit dark:text-white">{{ $filters.numberShortener(adsInfo.post.comment_count, $t('[number] comment'), $t('[number] comments')) }}</router-link> &middot;
                            <router-link :to="{name: 'post', params: {id: adsInfo.post.id}}" class="feed-item-like-text inline-block text-inherit dark:text-white">{{ $filters.numberShortener(adsInfo.post.view_count, $t('[number] view'), $t('[number] views')) }}</router-link>
                        </div>
                        <div class="feed-main-action flex justify-between border-t border-divider pt-base-2 dark:border-white/10">
                            <router-link :to="{name: 'post', params: {id: adsInfo.post.id}}" class="text-inherit">
                                <BaseIcon name="heart"/>
                            </router-link>
                            <router-link :to="{name: 'post', params: {id: adsInfo.post.id}}" class="text-inherit">
                                <BaseIcon name="comment"/>
                            </router-link>
                            <router-link :to="{name: 'post', params: {id: adsInfo.post.id}}" class="text-inherit">
                                <BaseIcon name="share"/>
                            </router-link>
                            <router-link :to="{name: 'post', params: {id: adsInfo.post.id}}" class="text-inherit">
                                <BaseIcon name="bookmarks" />
                            </router-link>
                        </div>
                    </div>
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
                    <div class="border-t border-divider pt-base-2 mt-base-2 dark:border-white/10">
                        <div class="font-semibold mb-base-2">{{ $t('Demographics & Targeting features') }}</div>
                        <div class="flex gap-base-2 justify-between mb-base-2">
                            <div>{{ $t('Gender') }}</div>
                            <div class="text-end">{{ genderText }}</div>
                        </div>
                        <div class="flex gap-base-2 justify-between mb-base-2">
                            <div>{{ $t('Age') }}</div>
                            <div class="text-end">
                                <span v-if="selectedAge == ageTypeAny">{{ $t('Any') }}</span>
                                <span v-if="selectedAge == ageTypeRange">{{ adsAgeFrom + '-' + adsAgeTo }}</span>
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
                        <div class="flex gap-base-2 justify-between mb-base-2">
                            <div>{{ $t('Daily budget') }}</div>
                            <div class="text-end break-word">{{ adsInfo.daily_amount }}</div>
                        </div>
                    </div>
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
                        <div class="flex gap-base-2 justify-between font-semibold">
                            <div>{{ $t('Available Amount') }}</div>
                            <div class="text-end">{{ adsInfo.total_available_amount }}</div>
                        </div>
                        <div class="flex gap-base-2 justify-between">
                            <div>{{$t('VAT')}} ({{adsInfo.vat}}%)</div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </FullColumn>
    
</template>

<script>
import { mapState } from 'pinia'
import { getAdvertisingDetail, getAdvertisingReport } from '@/api/ads'
import { useAppStore } from '@/store/app'
import { useAuthStore } from '@/store/auth'
import { VueperSlides, VueperSlide } from 'vueperslides'
import FullColumn from '@/components/columns/FullColumn.vue'
import Loading from '@/components/utilities/Loading.vue'
import BaseSelectStatus from '@/components/inputs/BaseSelectStatus.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import LinkFetched from "@/components/posts/LinkFetched.vue"
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import ContentHtml from '@/components/utilities/ContentHtml.vue'
import Constant from '@/utility/constant'

export default {
    props: ['id'],
    components: { FullColumn, Loading, BaseSelectStatus, BaseIcon, LinkFetched, Avatar, UserName, VueperSlides, VueperSlide, BaseButton, ContentHtml },
    data(){
        return {
            loadingAdsInfo: true,
            adsInfo: null,
            loadingViewMoreReport: false,
            loadingAdvertisingReport: false,
            loadmoreAdvertisingReport: false,
            advertisementReportsList: [],
            advertisementReportsPage: 1,
            adsType: 'text',
            adsContent: null,
            adsMedias: [],
            adsSharedLink: null,
			adsUploadedVideo: null,
			adsUploadedFiles: [],
            genderText: '', 
            genderId: 0,
            selectedAge: Constant.AGE_TYPE.ANY,
            adsAgeFrom: null,
            adsAgeTo: null,
            hashtags: [],
            location: {
				country_id: null,
				state_id: null,
				city_id: null,
				zip_code: null,
				address: null,
                address_full: null
			},
            adsStartDate: null,
            adsEndDate: null,
            ageTypeAny: Constant.AGE_TYPE.ANY,
            ageTypeRange: Constant.AGE_TYPE.RANGE
        }
    },
    mounted(){
        if (! this.config.advertising.enable) {
            return this.$router.push({ 'name': 'permission' })
        }
        this.getAdsDetail(this.id)
        this.getAdsReport(this.id, this.advertisementReportsPage)
    },
    computed:{
        ...mapState(useAuthStore, ['user']),
        ...mapState(useAppStore, ['config'])
    },
    methods:{
        async getAdsDetail(adsId){
            try {
                const response = await getAdvertisingDetail(adsId)
                this.adsInfo = response
                this.adsType = this.adsInfo.post.type
                this.adsContent = this.adsInfo.post.content
                this.genderId = this.adsInfo.gender
                this.genderText = this.adsInfo.gender_text
                this.selectedAge = (this.adsInfo.age_from == 0 && this.adsInfo.age_to == 0) ? this.ageTypeAny : this.ageTypeRange
                this.adsAgeFrom = this.adsInfo.age_from
                this.adsAgeTo = this.adsInfo.age_to
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
                        this.adsMedias = this.adsInfo.post.items
                        break;
                    case 'link':
                        this.adsSharedLink = this.adsInfo.post.items[0]
                        break;
                    case 'video':
                        this.adsUploadedVideo = this.adsInfo.post.items[0]
                        break;
                    case 'file':
                        this.adsUploadedFiles = this.adsInfo.post.items
                        break;
                }
                this.loadingAdsInfo = false
            } catch (error) {
                this.loadingAdsInfo = false
            }
        },
        async getAdsReport(adsId, page){
            this.loadingViewMoreReport = true
            try {
                const response = await getAdvertisingReport(adsId, page)
                if(page === 1){
                    this.advertisementReportsList = response.items
                }else{
                    this.advertisementReportsList = window._.concat(this.advertisementReportsList, response.items);
                }
                if(response.has_next_page){
                    this.loadmoreAdvertisingReport = true
                    this.advertisementReportsPage++;
                }else{
                    this.loadmoreAdvertisingReport = false
                }
                this.loadingAdvertisingReport = false
                this.loadingViewMoreReport = false
            } catch (error) {
                this.loadingAdvertisingReport = false
                this.loadingViewMoreReport = false
            }
        }
    }
}
</script>