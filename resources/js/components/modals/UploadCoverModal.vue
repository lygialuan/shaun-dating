<template>
    <div>
        <cropper ref="cropper"
        :stencil-props="{
            aspectRatio: 5.2/2
        }"
        :min-height="200"
        :src="imageData.file" />
        <div class="text-end mt-base-2">
            <BaseButton :loading="loading" @click="cropCover">{{$t('Crop Cover Image')}}</BaseButton>
        </div>
    </div>
</template>

<script>
import { mapState, mapActions } from 'pinia';
import { uploadCoverProfilePicture } from '@/api/user'
import { uploadGroupCover } from '@/api/group'
import { useAuthStore } from '@/store/auth';
import { useProfileStore } from '@/store/profile';
import { useGroupStore } from '@/store/group';
import { Cropper } from 'vue-advanced-cropper';
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
            imageData: this.dialogRef.data.imageData,
            loading: false
        }
    },
    computed: {
		...mapState(useAuthStore, ['user']),
        ...mapState(useProfileStore, ['userInfo']),
        ...mapState(useGroupStore, ['groupInfo']),
	},
    methods:{
        ...mapActions(useAuthStore, ['updateUserMeInfo']),
        ...mapActions(useProfileStore, ['setUserInfo']),
        ...mapActions(useGroupStore, ['setGroupInfo']),
        async cropCover() {
			let { canvas }  =  this.$refs.cropper.getResult()
			if (canvas) {
				const formData = new FormData();
                var canvasTmp
                if (canvas.width > constant.COVER_WIDTH) {
                    canvasTmp = document.createElement('canvas')
                    var ctx = canvasTmp.getContext("2d")
                    canvasTmp.width = constant.COVER_WIDTH;
                    canvasTmp.height = constant.COVER_HEIGHT;
                    ctx.drawImage(canvas,0,0,canvas.width,canvas.height,0,0,canvasTmp.width,canvasTmp.height)
                } else {
                    canvasTmp = canvas
                }

				canvasTmp.toBlob(async blob => {
					let file = new File([blob], "Cover.png", { type: "image/png" })
					formData.append('file', file);
                    if(this.imageData.subject_type === 'group'){
                        formData.append('id', this.imageData.subject_id);
                    }
                    if (this.loading) {
                        return
                    }
                    this.loading = true
					try {
                        switch (this.imageData.subject_type) {
                            case 'group':
                                await uploadGroupCover(formData).then((response) => {
                                    this.setGroupInfo({
                                        ...this.groupInfo,
                                        cover: response.cover
                                    })
                                });
                                break;
                            default:
                                await uploadCoverProfilePicture(formData).then((response) => {
                                    this.updateUserMeInfo({
                                        ...this.user,
                                        cover: response.cover
                                    })
                                    this.setUserInfo({
                                        ...this.userInfo,
                                        cover: response.cover
                                    })
                                });
                                break;
                        }
                        this.dialogRef.close();
					} catch (error) {
						this.showError(error.error)
					} finally {
                        this.loading = false
                    }
				});
			}
        }
    }
}
</script>