<template>
	<div class="bg-white rounded-base w-full p-7 mx-auto mb-base-2 dark:bg-dark-form-base dark:text-white">
		<div>
			<h3 class="text-base-lg font-bold mb-4">{{$t('Add Photos')}}</h3>
			<div class="mb-6">{{ $t("Profiles with more than 3 photos are 43% more likely to get a match. You can change these later.") }}</div>
			<div class="grid grid-cols-3 sm:grid-cols-4 gap-6">
				<BaseUploadPhotosVerify
					v-for="(photo, index) in photosVerifySlots"
					:key="photo ? `${photo.id}-${photo.is_thumbnail}` : `empty-${index}`"
					:dataPhoto="photo"
					:position="index"
					@uploadPhoto="handleUploadPhoto"
					@removePhoto="handleRemovePhoto"
				/>
			</div>
			<div v-if="!owner" class="flex flex-col items-center mt-6">
				<BaseButton v-if="uploadedCount >= 1" @click="handleCompletedPhotoVerify" class="w-full">{{ $t("Continue") }}</BaseButton>
			</div>
            <div v-if="!owner" class="mt-base-2 text-center">{{$t('Switch account?')}}&nbsp;<button class="text-primary-color dark:text-dark-primary-color font-bold" @click="logout()">{{$t('Logout')}}</button></div>
            <div v-else>
				<BaseButton class="mt-base-2 text-center w-full" @click="continueModal()">{{$t('Continue')}}</BaseButton>
				<BaseButton class="mt-base-2 text-center w-full" type="transparent" @click="continueModal()">{{$t('Cancel')}}</BaseButton>
			</div>
		</div>
	</div>
</template>

<script>
import BaseUploadPhotosVerify from '@/components/inputs/BaseUploadPhotosVerify.vue'
import { mapState, mapActions } from 'pinia'
import { useAuthStore } from '@/store/auth'
import { useAppStore } from '@/store/app'
import BaseButton from '@/components/inputs/BaseButton.vue';
import { completedPhotoVerify } from '@/api/user'

export default {
    components: { 
		BaseUploadPhotosVerify,
		BaseButton
	},
    inject: {
        dialogRef: {
            default: null
        }
    },
	data() {
        return {
            owner: this.dialogRef?.data?.owner ?? false,
            localUser: window._.clone(this.dialogRef?.data?.user) ?? []
        };
    },
	computed: {
		...mapState(useAuthStore, ['user']),
		...mapState(useAppStore, ['config']),
		currentUser() {
			return this.owner ? this.localUser : this.user
		},
		photosVerifySlots() {
			const limit = this.config.limitPhotosVerify || 9
			const slots = Array.from({ length: limit }, () => null)

			const photos = Array.isArray(this.currentUser?.photos_verify) ? this.currentUser.photos_verify : []
			photos.forEach(photo => {
				if (photo.order >= 0 && photo.order < limit) {
					slots[photo.order] = photo
				}
			})

			return slots
		},
		uploadedCount() {
			return this.photosVerifySlots.filter(Boolean).length
		}
	},
	methods: {
		...mapActions(useAuthStore, ['me']),
		handleUploadPhoto(data) {
			const photos = this.currentUser.photos_verify || []
			if (data.is_thumbnail) {
				photos.forEach(photo => {
					photo.is_thumbnail = false
				})
			}
			if (data.order !== undefined && data.order !== null) {
				const index = photos.findIndex(p => p.order === data.order)

				if (index !== -1) {
					photos.splice(index, 1)
				}
			}
			photos.push(data)
			this.currentUser.photos_verify = photos
		},
		handleRemovePhoto(data) {
			this.currentUser.photos_verify = this.currentUser.photos_verify.filter(photo => photo.id !== data.id)
		},
		async handleCompletedPhotoVerify() {
			try {
				await completedPhotoVerify()
				if (this.owner) {
					return this.closeModal()
				}
				await this.me()
				this.$router.push({ name: this.user.already_setup_login ? 'home' : 'first_login'})
			} catch (error) {
				this.showError(error.error)
			}
		},
		async logout() {
            try {
                await useAuthStore().logout();
                window.location.href = `${window.siteConfig.siteUrl}/login`;
            } catch (error) {
                this.showError(error.error)
            }
		},
		async continueModal() {
			await this.me()   
			this.dialogRef.close({updatedUser: this.user})
		},
	}
}
</script>