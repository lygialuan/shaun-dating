<template>
	<div v-if="photo" class="aspect-[3/4] w-full max-w-[160px] mx-auto relative">
		<img :src="photo.photo" class="w-full h-full object-cover rounded-2xl cursor-pointer" @click="updatePhoto(photo)"/>
		<BaseIcon size="14" name="close" @click="removePhoto(photo.id)" class="absolute top-1 right-1 w-5 h-5 rounded-full bg-red-500 text-white flex items-center justify-center shadow cursor-pointer"/>
		<span v-if="photo.status != 'approve'" class="capitalize absolute bottom-2 left-2 px-2 py-1 text-xs font-semibold rounded-md text-white"
			:class="{
				'bg-red-500': photo.status === 'reject',
				'bg-gray-600': !['approve','reject'].includes(photo.status)
			}"
		>
			{{ photo.status }}
		</span>
	</div>
	<div v-else class="relative aspect-[3/4] w-full max-w-[160px] mx-auto rounded-2xl bg-gray-100 dark:bg-dark-form-surface flex items-center justify-center">
		<BaseIcon name="plus" class="absolute w-6 h-6 rounded-full text-black dark:text-white cursor-pointer" @click="$refs.avatar.click()"/>
		<input ref="avatar" type="file" accept="image/*" class="hidden" @change="uploadAvatar" @click="onInputClick"/>
	</div>
</template>


<script>
import { mapState } from 'pinia'
import { checkPopupBodyClass } from '@/utility'
import { defineAsyncComponent } from 'vue'
import { useProfileStore } from '@/store/profile'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import { removePhotoVerify } from '@/api/user'

export default {
	props:{
		dataPhoto: {
			type: Object,
			default: null
		},
		position: {
			type: Number,
			default: 0
		},
	},
	emits: ['uploadPhoto'],
    components: { BaseIcon },
    data(){
        return{
            photo: this.dataPhoto,
        }
    },
    computed: {
        ...mapState(useProfileStore, ['userInfo'])
    },
    methods:{
        uploadAvatar(event){
			var input = event.target;
			if (input.files && input.files[0]) {
				var reader = new FileReader();
				reader.onload = e => {
					const UploadPhotosModal = defineAsyncComponent(() =>
						import('@/components/modals/UploadPhotosModal.vue')
					)
					this.$dialog.open(UploadPhotosModal, {
						data: {
							imageData: e.target.result,
							position: this.position
						},
						props:{
							header: this.$t('Adjust Photo'),
							class: 'crop-avatar-modal p-dialog-md',
							modal: true
						},
						onClose: (data) => {
							if(data.data){
								this.photo = data.data
								this.$emit('uploadPhoto', data.data)
							}
							checkPopupBodyClass();
						}
					})
				};
				reader.readAsDataURL(input.files[0]);
			}
		},
        onInputClick(event){
			event.target.value = null
		},
		async removePhoto(id){
			try {
				await removePhotoVerify({id})
				this.$emit('removePhoto', this.photo)
				this.photo = null
			} catch (error) {
				this.showError(error.error)
			}
		},
		updatePhoto(photo) {
			const UploadPhotosModal = defineAsyncComponent(() =>
				import('@/components/modals/UploadPhotosModal.vue')
			)

			this.$dialog.open(UploadPhotosModal, {
				data: {
					imageData: photo.photo,   
					photoId: photo.id,      
					position: this.position, 
					isMain: photo.is_thumbnail, 
				},
				props: {
					header: this.$t('Adjust Photo'),
					class: 'crop-avatar-modal p-dialog-md',
					modal: true
				},
				onClose: (data) => {
					if(data.data){
						this.photo = data.data
						this.$emit('uploadPhoto', data.data)
					}
					checkPopupBodyClass()
				}
			})
		}
    }
}
</script>