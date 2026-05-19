<template>
    <button class="inline-flex items-center gap-1 bg-gray-trans-4 text-white rounded p-1" @click="$refs.cover.click()">
        <BaseIcon name="camera"/>
        <input type="file" ref="cover" @change="uploadCover($event)" @click="onInputClick($event)" accept="image/*" class="hidden">
    </button>
</template>

<script>
import { mapState } from 'pinia'
import { checkPopupBodyClass } from '@/utility'
import { defineAsyncComponent } from 'vue'
import { useProfileStore } from '@/store/profile'
import { useGroupStore } from '@/store/group'
import BaseIcon from '@/components/icons/BaseIcon.vue'

export default {
	props:{
		type: {
			type: String,
			default: 'user'
		},
		subjectId: {
			type: Number,
			default: null
		}
	},
    components: { BaseIcon },
    computed: {
        ...mapState(useProfileStore, ['userInfo']),
		...mapState(useGroupStore, ['groupInfo'])
    },
    methods:{
        uploadCover(event){
			var input = event.target;
			if (input.files && input.files[0]) {
				var reader = new FileReader();
				reader.onload = e => {
					const UploadCoverModal = defineAsyncComponent(() =>
						import('@/components/modals/UploadCoverModal.vue')
					)
					this.$dialog.open(UploadCoverModal, {
						data: {
							imageData: {
								file: e.target.result,
								subject_id: this.subjectId,
								subject_type: this.type
							}
						},
						props:{
							header: this.$t('Crop Cover'),
							class: 'crop-cover-modal p-dialog-lg',
							modal: true
						},
						onClose: (options) => {
							if(options.data){
								switch(this.type){
									case 'group':
										this.groupInfo.cover = options.data.cover
										break;
									default:
										this.userInfo.cover = options.data.cover
								}
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
		}
    }
}
</script>