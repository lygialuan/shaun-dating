<template>
    <div>
        <div class="main-content-section">
            <div class="flex gap-5 flex-col sm:flex-row">
                <div class="create-photo-block rounded-base-lg flex-1 flex gap-2 flex-col justify-center items-center min-h-40 sm:min-h-96 p-4 cursor-pointer" @click="$refs.uploadPhotoStoryRef.click()">
                    <div class="flex justify-center items-center bg-white h-12 w-12 rounded-full dark:bg-slate-800">
                        <BaseIcon name="photo"/>
                    </div>
                    <div class="font-semibold text-sm text-white">{{ $t('Create a photo story') }}</div>                 
                    <input type="file" ref="uploadPhotoStoryRef" @change="showPhotoStoryModal($event)" accept="image/*" class="hidden">
                </div>
                <div class="create-text-block rounded-base-lg flex-1 flex gap-2 flex-col justify-center items-center min-h-40 sm:min-h-96 p-4 cursor-pointer" @click="showTextStoryModal">              
                    <div class="flex justify-center items-center bg-white h-12 w-12 rounded-full dark:bg-slate-800">
                        <BaseIcon name="character"/>                  
                    </div>
                    <div class="font-semibold text-sm text-white">{{ $t('Create a text story') }}</div>  
                </div>
                <div v-if="config.ffmegEnable" class="create-video-block rounded-base-lg flex-1 flex gap-2 flex-col justify-center items-center min-h-40 sm:min-h-96 p-4 cursor-pointer" @click="showVideoStoryModal">              
                    <div class="flex justify-center items-center bg-white h-12 w-12 rounded-full dark:bg-slate-800">
                        <BaseIcon name="youtube_logo"/>                  
                    </div>
                    <div class="font-semibold text-sm text-white">{{ $t('Create a video story') }}</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { mapState } from 'pinia'
import { checkPopupBodyClass } from '@/utility/index'
import { useAppStore } from '@/store/app';
import Photo from './Photo.vue'
import Text from './Text.vue'
import Video from './Video.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'

export default {
    components: {
        BaseIcon
    },
    computed: {
        ...mapState(useAppStore, ['config']),
    },
    methods: {
        showPhotoStoryModal(event){      
            var input = event.target;
			// Ensure that you have a file before attempting to read it
			if (input.files && input.files[0]) {
				// create a new FileReader to read this image and convert to base64 format
				var reader = new FileReader();
				// Define a callback function to run, when FileReader finishes its job
				reader.onload = e => {
					// Note: arrow function used here, so that "this.imageData" refers to the imageData of Vue component
					// Read image as base64 and set to imageData
					// Open modal to crop cover image
					this.$dialog.open(Photo, {
						data: {
							imageData: e.target.result
						},
						props:{
							class: 'p-dialog-story',
                            modal: true,
                            showHeader: false,
                            draggable: false
						},
						onClose: () => {					
							checkPopupBodyClass();
						}
					})
				};
				// Start the reader job - read file as a data url (base64 format)
				reader.readAsDataURL(input.files[0]);
			}
        },
        showTextStoryModal(){
            this.$dialog.open(Text, {
                props:{
                    class: 'p-dialog-story',
                    modal: true,
                    showHeader: false,
                    draggable: false
                },
                onClose: () => {
                    checkPopupBodyClass();
                }
            });
        },
        showVideoStoryModal() {
            this.$dialog.open(Video, {
                props:{
                    class: 'p-dialog-story',
                    modal: true,
                    showHeader: false,
                    draggable: false
                },
                onClose: () => {					
                    checkPopupBodyClass();
                }
            })
        }
    }
}
</script>