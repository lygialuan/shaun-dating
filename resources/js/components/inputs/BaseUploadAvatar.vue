<template>
     <button class="inline-flex items-center gap-1 bg-gray-trans-4 text-white rounded-full p-1" @click="$refs.avatar.click()">
		<BaseIcon name="camera"/>
		<input type="file" ref="avatar" @change="uploadAvatar($event)" @click="onInputClick($event)" accept="image/*" class="hidden">
	</button>
</template>

<script>
import { mapState } from 'pinia'
import { checkPopupBodyClass } from '@/utility'
import { defineAsyncComponent } from 'vue'
import { useProfileStore } from '@/store/profile'
import BaseIcon from '@/components/icons/BaseIcon.vue'

export default {
    components: { BaseIcon },
    computed: {
        ...mapState(useProfileStore, ['userInfo'])
    },
    methods:{
        uploadAvatar(event){
			var input = event.target;
			if (input.files && input.files[0]) {
				var reader = new FileReader();
				reader.onload = e => {
					const UploadAvatarModal = defineAsyncComponent(() =>
						import('@/components/modals/UploadAvatarModal.vue')
					)
					this.$dialog.open(UploadAvatarModal, {
						data: {
							imageData: e.target.result
						},
						props:{
							header: this.$t('Crop Avatar'),
							class: 'crop-avatar-modal p-dialog-md',
							modal: true
						},
						onClose: () => {
							checkPopupBodyClass();
						}
					})
				};
				reader.readAsDataURL(input.files[0]);

			}
		},
        onInputClick(event){
			event.target.value = null
		}
    }
}
</script>