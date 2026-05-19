<template>
    <cropper
        v-if="!photoId"
        ref="cropper"
        :src="image"
        :stencil-props="{
            aspectRatio: 4/5
        }"
        :min-width="100"
    />
    <div v-else class="w-full h-[400px] bg-black flex items-center justify-center">
        <img :src="image" class="max-w-full max-h-full object-contain"/>
    </div>
    <div class="flex items-center justify-center mt-4" v-if="!photoId">
        <div class="mb-base-2 space-x-4">
            <BaseButton type="transparent" icon="arrow_clockwise" @click="rotateRight" class="rounded-full bg-gray-200 border border-gray-300 text-black dark:border-dark-border-color-icon dark:bg-dark-form-surface dark:text-white"/>
            <BaseButton type="transparent" icon="arrow_counter_clockwise" @click="rotateLeft" class="rounded-full bg-gray-200 border border-gray-300 text-black dark:border-dark-border-color-icon dark:bg-dark-form-surface dark:text-white"/>
            <BaseButton type="transparent" icon="magnifying_glass_plus" @click="zoomIn" class="rounded-full bg-gray-200 border border-gray-300 text-black dark:border-dark-border-color-icon dark:bg-dark-form-surface dark:text-white"/>
            <BaseButton type="transparent" icon="magnifying_glass_minus" @click="zoomOut" class="rounded-full bg-gray-200 border border-gray-300 text-black dark:border-dark-border-color-icon dark:bg-dark-form-surface dark:text-white"/>
            <BaseButton type="transparent" icon="bounding_box" @click="resetZoom" class="rounded-full bg-gray-200 border border-gray-300 text-black dark:border-dark-border-color-icon dark:bg-dark-form-surface dark:text-white"/>
        </div>
    </div>
    <div class="space-x-2 mb-4 mt-4">
        <BaseCheckbox v-model="isMain" :inputId="`contentWarningSection_${key}`" :binary="true" />
        <span>{{ $t("Main photo") }}</span>
    </div>
    <div class="mt-base-2 max-w-full space-y-2">
        <BaseButton :loading="loading" class="w-full" @click="cropAvatar">
            {{ $t('Choose') }}
        </BaseButton>
    </div>
    <div class="mt-base-2 max-w-full">
        <BaseButton class="w-full" type="transparent" @click="cancelCropAvatar">
            {{ $t('Cancel') }}
        </BaseButton>
    </div>
</template>

<script>
import { mapState, mapActions } from 'pinia';
import { uploadPhotosVerify } from '@/api/user'
import { Cropper } from 'vue-advanced-cropper';
import { useAuthStore } from '@/store/auth';
import { useProfileStore } from '@/store/profile';
import { useAppStore } from '@/store/app';
import 'vue-advanced-cropper/dist/style.css';
import BaseButton from '@/components/inputs/BaseButton.vue';
import constant from '@/utility/constant';
import BaseCheckbox from '@/components/inputs/BaseCheckbox.vue'
import { changeMainPhoto } from '@/api/user'

export default {
    components :{ Cropper, BaseButton, BaseCheckbox },
    inject: {
        dialogRef: {
            default: null
        }
    },
    data(){
        return{
            image: this.dialogRef.data.imageData,
            loading: false,
            position: this.dialogRef.data.position,
            photoId: this.dialogRef.data.photoId ?? 0,
            zoomLevel: 1,
            rotateDeg: 0,
            isMain: this.dialogRef.data.isMain, 
        }
    },
    computed: {
		...mapState(useAuthStore, ['user']),
        ...mapState(useProfileStore, ['userInfo']),
        ...mapState(useAppStore, ['config']),
	},
    methods:{
        ...mapActions(useAuthStore, ['updateUserMeInfo']),
        ...mapActions(useProfileStore, ['setUserInfo']),
        async cropAvatar() {
            if(this.photoId){
                this.handleChangeMainPhoto({photoId: this.photoId, isMain: this.isMain})
            }else{
                const saveAvatar = () => {
                    let { canvas }  =  this.$refs.cropper.getResult()
                    if (canvas) {
                        const formData = new FormData();
                        var canvasTmp
                        if (canvas.width > constant.AVATAR_WIDTH) {
                            const scale = constant.AVATAR_WIDTH / canvas.width
                            canvasTmp = document.createElement('canvas')
                            const ctx = canvasTmp.getContext('2d')
                            canvasTmp.width = constant.AVATAR_WIDTH
                            canvasTmp.height = Math.round(canvas.height * scale)
                            ctx.drawImage(canvas, 0, 0, canvasTmp.width, canvasTmp.height)
                        } else {
                            canvasTmp = canvas
                        }
                
                        canvasTmp.toBlob(async blob => {
                            let file = new File([blob], "Avatar.png", { type: "image/png" })
                            formData.append('file', file);
                            formData.append('position', this.position);
                            formData.append('isMain', this.isMain);
                            
                            if (this.loading) {
                                return
                            }
                            this.loading = true
                            try {
                                const result = await uploadPhotosVerify(formData)
                                this.dialogRef.close(result);
                            } catch (error) {
                                this.showError(error.error)
                            } finally {
                                this.loading = false
                            }
                        })
                    }
                }
                if(this.user.is_verify && this.config.userVerifyLostWhen == '2'){
                    this.$confirm.require({
                        message: this.$t('You will lose your profile verification badge if you change your photo. Do you want to continue?'),
                        header: this.$t('Please confirm'),
                        acceptLabel: this.$t('Ok'),
                        rejectLabel: this.$t('Cancel'),
                        accept: () => {
                            saveAvatar()
                            this.updateUserMeInfo({
                                ...this.user,
                                is_verify: false
                            })
                            this.setUserInfo({
                                ...this.userInfo,
                                is_verify: false
                            })
                        }
                    });
                } else {
                    saveAvatar()
                }
            }
        },
        zoomIn() {
            if (this.zoomLevel < 2) {
                this.zoomLevel *= 1.2
                this.$refs.cropper.zoom(1.2)
            }
        },
        zoomOut() {
            if (this.zoomLevel > 0.5) {
                this.zoomLevel *= 0.8
                this.$refs.cropper.zoom(0.8)
            }
        },
        resetZoom() {
            this.zoomLevel = 1
            this.$refs.cropper.reset()
        },
        rotateRight() {
            this.rotateDeg = (this.rotateDeg + 90) % 360
            this.$refs.cropper.rotate(90)
        },
        rotateLeft() {
            this.rotateDeg = (this.rotateDeg - 90 + 360) % 360
            this.$refs.cropper.rotate(-90)
        },
        cancelCropAvatar(){
            this.dialogRef.close();
        },
        async handleChangeMainPhoto(data){
			try {
				const result = await changeMainPhoto(data)
                this.dialogRef.close(result);
			} catch (error) {
				this.showError(error.error)
			}
		},
    }
}
</script>