<template>
    <div>
        <cropper ref="cropper"
        :stencil-props="{
            aspectRatio: 1/1
        }"
        :min-width="200"
        :src="image" />
        <div class="text-end mt-base-2">
            <BaseButton :loading="loading" @click="cropAvatar">{{$t('Crop Avatar Image')}}</BaseButton>
        </div>
    </div>
</template>

<script>
import { mapState, mapActions } from 'pinia';
import { uploadAvatarProfilePicture } from '@/api/user'
import { Cropper } from 'vue-advanced-cropper';
import { useAuthStore } from '@/store/auth';
import { useProfileStore } from '@/store/profile';
import { useAppStore } from '@/store/app';
import 'vue-advanced-cropper/dist/style.css';
import BaseButton from '@/components/inputs/BaseButton.vue';
import constant from '@/utility/constant';

export default {
    components :{ Cropper, BaseButton },
    inject: {
        dialogRef: {
            default: null
        }
    },
    data(){
        return{
            image: this.dialogRef.data.imageData,
            loading: false
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
			const saveAvatar = () => {
                let { canvas }  =  this.$refs.cropper.getResult()
                if (canvas) {
                    const formData = new FormData();
                    var canvasTmp
                    if (canvas.width > constant.AVATAR_WIDTH) {
                        canvasTmp = document.createElement('canvas')
                        var ctx = canvasTmp.getContext("2d")
                        canvasTmp.width = constant.AVATAR_WIDTH;
                        canvasTmp.height = constant.AVATAR_HEIGHT;
                        ctx.drawImage(canvas,0,0,canvas.width,canvas.height,0,0,canvasTmp.width,canvasTmp.height)
                    } else {
                        canvasTmp = canvas
                    }
            
                    canvasTmp.toBlob(async blob => {
                        let file = new File([blob], "Avatar.png", { type: "image/png" })
                        formData.append('file', file);
                        if (this.loading) {
                            return
                        }
                        this.loading = true
                        try {
                            await uploadAvatarProfilePicture(formData).then((response) => {
                                this.updateUserMeInfo({
                                    ...this.user,
                                    avatar: response.avatar
                                })
                                this.setUserInfo({
                                    ...this.userInfo,
                                     avatar: response.avatar
                                })
                            })
                            this.dialogRef.close();
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
    }
}
</script>