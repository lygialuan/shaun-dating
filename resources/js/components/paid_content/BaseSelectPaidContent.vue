<template>
    <div>
        <div class="flex items-center gap-base-2 font-medium">
            <BaseCheckbox v-model="isShowPaidContent" :inputId="`paidContent_${key}`" :binary="true" />
            <label :for="`paidContent_${key}`">{{ $t('Paid content') }}</label>
        </div>
        <div v-if="isShowPaidContent" class="bg-light-web-wash text-main-color dark:text-white p-4 rounded-base-lg font-medium mt-base-2 dark:bg-dark-web-wash">
            <h5 class="text-base-lg font-semibold mb-base-1">{{ $t('Create a paid post') }}</h5>
            <div v-if="user.can_create_post_paid_content" class="flex flex-col gap-base-2">
                <div>
                    <label class="block mb-1">{{ $t('Price') }}</label>
                    <BaseSelect
                        v-model="paidType"
                        :options="paidTypesList"
                        optionLabel="name"
                        optionValue="value"
                        class="max-w-[240px]"
                    />
                </div>
                <div v-if="paidType === 'pay_per_view'">
                    <label class="block mb-1">{{ $t('Amount') }} ({{ config.wallet.tokenName }})</label>
                    <BaseInputNumber v-model="paidContentAmount" class="max-w-[240px]" :error="error?.content_amount"/>
                </div>
                <div>
                    <label class="block mb-1">{{ $t('Thumbnail') }}</label>
                    <UploadImages ref="thumbUploadStatus" @upload="handleUploadThumb" :multiple="false">
                        <div 
                            class="flex flex-col items-center justify-center text-center gap-base-2 bg-gray-300 border min-h-96 p-5 rounded-base-lg relative overflow-hidden dark:bg-slate-600"
                            :class="error?.thumb_file_id ? 'border-invalid-color' : 'border-gray-300 dark:border-white/10'"
                        >
                            <div v-if="previewThumb" class="flex items-center justify-center absolute inset-0">
                                <img :src="previewThumb" class="max-w-full max-h-full mx-auto">
                                <div @click.stop.prevent="removeThumb" class="bg-black/30 text-white flex justify-center items-center w-8 h-8 rounded-md absolute top-3 end-3">
                                    <BaseIcon name="close" />
                                </div>
                            </div>
                            <div v-else class="space-y-base-2">
                                <BaseIcon name="upload_simple" size="44"/>
                                <div>{{ $t('Thumbnail is not a required field, if not uploaded, default blurred thumbnail will be automatically used') }}</div>
                            </div>
                        </div>
                    </UploadImages>
                    <small v-if="error?.thumb_file_id" class="p-error">{{ error?.thumb_file_id }}</small>
                </div>
            </div>
            <p v-else>{{ $t('Complete your') }}&nbsp;<router-link :to="{name: 'creator_setting_steps'}">{{ $t('creator settings') }}</router-link>&nbsp;{{ $t('to start earning.') }}</p>
        </div>
    </div>
</template>

<script>
import { mapState } from 'pinia'
import { useAuthStore } from '@/store/auth';
import { uuidv4 } from '@/utility/index'
import { uploadPaidContentThumb } from '@/api/posts'
import BaseCheckbox from '@/components/inputs/BaseCheckbox.vue'
import BaseInputNumber from '@/components/inputs/BaseInputNumber.vue'
import BaseSelect from '@/components/inputs/BaseSelect.vue'
import UploadImages from '@/components/utilities/UploadImages.vue'
import BaseIcon from "@/components/icons/BaseIcon.vue"
import { useAppStore } from '@/store/app'

export default {
    components: { 
        BaseCheckbox,
        BaseInputNumber,
        BaseSelect,
        UploadImages,
        BaseIcon
    },
    props: {
        modelValue: {
			type: Object,
			default: null
		},
        error: {
            type: Object,
            default: null
        }
    },
    data(){
        return{
            key: uuidv4(),
            isShowPaidContent: this.modelValue ? this.modelValue.is_paid : false,
            paidType: this.modelValue ? this.modelValue.paid_type : 'subscriber',
            paidContentAmount: this.modelValue ? this.modelValue.content_amount : null,
            previewThumb: this.modelValue ? this.modelValue.thumb : null,
            paidTypesList: [
                { name: this.$t('For Subscribers'), value: 'subscriber' },
                { name: this.$t('Pay Per View'), value: 'pay_per_view' },
            ],
            uploadedThumb: null,
            uploadedThumbLoading: false,
            thumbDelete: false
        }
    },
    computed:{
        ...mapState(useAuthStore, ['user']),
        ...mapState(useAppStore, ['config']),
        paidContent(){
            return {
                is_paid: this.isShowPaidContent,
                content_amount: this.paidContentAmount,
                paid_type: this.paidType,
                thumb_file_id: this.uploadedThumb?.id,
                thumb_delete: this.thumbDelete
            }
        }
    },
    watch:{
        paidContent(newVal){
            this.$emit('update:modelValue', newVal);
        }
    },
    methods:{
        handleUploadThumb(event){
			this.startUploadThumb(event.target.files)
		},
		async startUploadThumb(uploadedThumbs){
            for( var i = 0; i < uploadedThumbs.length; i++ ){
                let formData = new FormData()
                formData.append('file', uploadedThumbs[i])
                this.uploadedThumbLoading = true
                try {
                    const response = await uploadPaidContentThumb(formData);
                    this.uploadedThumb = response;
                    this.previewThumb = response.url
                    this.thumbDelete = false
                } catch (error) {
                    this.showError(error.error)
                } finally {
                    this.uploadedThumbLoading = false
                    this.$refs.thumbUploadStatus.reset()
                }
			}
		},
		removeThumb(){
			this.uploadedThumb = null;
			this.previewThumb = null;
			this.$refs.thumbUploadStatus.reset();
            this.thumbDelete = true
		}
    },
    emits: ['update:modelValue']
}
</script>