<template>
    <ContentWarningWrapper :content-warning-list="contentWarningSelecteds" :post="postData">
        <template v-if="postData.type === 'photo'">
            <VueperSlides class="no-shadow" :slide-ratio="0.5625" :infinite="false" :bullets="false" disable-arrows-on-edges :touchable="false" transition-speed='200' :rtl="user.rtl ? true : false">
                <VueperSlide
                    v-for="postItem in postData.items"
                    :key="postItem.subject.id"
                    :image="postItem.subject.url"
                    :style="{ backgroundColor: `${postItem.subject.params.dominant_color ? postItem.subject.params.dominant_color : '#000'}`}"
                >
                </VueperSlide>
                <template #arrow-left>
                    <div class="arrow_slider arrow_slider_left"></div>
                </template>
                <template #arrow-right>
                    <div class="arrow_slider arrow_slider_right"></div>
                </template>
            </VueperSlides>
        </template>
        <template v-if="['video', 'vibb'].includes(postData.type)">
            <div v-for="postItem in postData.items" :key="postItem.id" class="activity_content video_feed_activity_content">
                <div v-if="postItem.subject.thumb" class="activity_content_thumb w-full bg-black relative">
                    <VideoPlayer ref="videoRef" :video="postItem.subject" :class="(aspectRatioVideo(postData.items[0].subject.thumb.params) == 'horizontal') ? '' : 'max-w-[16rem] mx-auto'" />
                </div>
            </div>
        </template>
    </ContentWarningWrapper>
    <BaseSelectContentWarning v-model="contentWarningSelecteds" @put_content_warning="handlePutContentWarning" :show-content-warning-section="isShowContentWarningSection" class="my-base-2" />
    <div class="text-end">
        <BaseButton :loading="loading" @click="handleChangeContentWarning()">{{$t('Save')}}</BaseButton>
    </div>
</template>
<script>
import { mapState, mapActions } from 'pinia'
import { useAuthStore } from '@/store/auth';
import { VueperSlides, VueperSlide } from 'vueperslides'
import { usePostStore } from '@/store/post';
import Constant from '@/utility/constant'
import ContentWarningWrapper from '@/components/utilities/ContentWarningWrapper.vue';
import BaseButton from '@/components/inputs/BaseButton.vue';
import VideoPlayer from '@/components/utilities/VideoPlayer.vue';
import BaseSelectContentWarning from "@/components/inputs/BaseSelectContentWarning.vue"

export default {
    components: { ContentWarningWrapper, VueperSlides, VueperSlide, BaseButton, VideoPlayer, BaseSelectContentWarning },
    inject: ['dialogRef'],
    data(){
        return{
            postData: this.dialogRef.data.post,
			contentWarningSelecteds: this.dialogRef.data.post.content_warning_categories,
            isShowContentWarningSection: this.dialogRef.data.post.content_warning_categories.length > 0,
            loading: false
        }
    },
    computed:{
        ...mapState(useAuthStore, ['user'])
    },
    methods:{
        ...mapActions(usePostStore, ['changeContentWarningPost']),
        async handleChangeContentWarning(){
            if (this.loading) {
                return
            }
            this.loading = true
            try {
                if(this.isShowContentWarningSection && this.contentWarningSelecteds.length === 0){
                    return this.showError(this.$t('User must select at least 1 category.'))
                }
                await this.changeContentWarningPost({
                    id: this.postData.id,
                    content_warning_categories: this.contentWarningSelecteds.map(content_warning => content_warning.id)
                })
                this.dialogRef.close()
                this.showSuccess(this.$t('Edit Successfully.'))
            } catch (error) {
                if (error.error.code == Constant.RESPONSE_CODE_MEMBERSHIP_PERMISSION) {
					this.showPermissionPopup('post',error.error.message)
				} else {
					this.showError(error.error)
				}
            } finally {
                this.loading = false
            }
        },
        handlePutContentWarning(status){
            this.isShowContentWarningSection = status
        }
    }
}
</script>