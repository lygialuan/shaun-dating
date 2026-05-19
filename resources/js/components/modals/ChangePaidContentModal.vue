<template>
    <template v-if="post.items.length">
        <div v-if="post.type === 'photo'" class="status-box-image-upload-preview mb-base-2">
            <VueperSlides class="no-shadow" :slide-ratio="0.5625" :infinite="false" :bullets="false" disable-arrows-on-edges :touchable="false" transition-speed='200' :rtl="user.rtl ? true : false">
                <VueperSlide
                    v-for="item in post.items"
                    :key="item.id"
                    class="status-box-image-upload-preview-item"									
                    :image="item.subject.url"
                    :style="{ backgroundColor: `${item.subject.params.dominant_color ? item.subject.params.dominant_color : '#000'}`}"
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
        <div v-else-if="post.type === 'video'" class="bg-black mb-base-2">
            <img class="w-full" :class="(aspectRatioVideo(post.items[0].subject.thumb.params) == 'horizontal') ? '' : 'max-w-[200px] mx-auto'" :src="post.items[0].subject.thumb.url" />				
        </div>
    </template>
    <BaseSelectPaidContent v-model="paidContentData" :error="error" />
    <div class="text-end mt-base-2">
        <BaseButton :loading="loadingEdit" @click="handleEditPaidPost()">{{$t('Edit')}}</BaseButton>
    </div>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { useAuthStore } from '@/store/auth';
import { usePostStore } from '@/store/post'
import { VueperSlides, VueperSlide } from 'vueperslides'
import BaseSelectPaidContent from '@/components/paid_content/BaseSelectPaidContent.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'

export default {
    inject: ['dialogRef'],
    components:{
        VueperSlides,
		VueperSlide,
        BaseSelectPaidContent,
        BaseButton
    },
    data(){
        return{
            post: this.dialogRef.data.post,
            paidContentData: {
                is_paid: this.dialogRef.data.post.is_paid,
                content_amount: this.dialogRef.data.post.content_amount,
                paid_type: this.dialogRef.data.post.paid_type,
                thumb: this.dialogRef.data.post?.thumb
            },
            error: {
                content_amount: null,
                thumb_file_id: null
            },
            loadingEdit: false
        }
    },
    computed:{
        ...mapState(useAuthStore, ['user'])
    },
    methods:{
        ...mapActions(usePostStore, ['editPaidPost']),
        async handleEditPaidPost(){
            this.loadingEdit = true
            try {
                await this.editPaidPost({
                    id: this.post.id,
                    ...this.paidContentData
                })
                this.dialogRef.close()
                this.showSuccess(this.$t('Edit Successfully.'))
            } catch (error) {
                this.handleApiErrors(this.error, error)
            } finally {
                this.loadingEdit = false
            }
        }
    }
}
</script>