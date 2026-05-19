<template>
    <input type="hidden" :value="modelValue" class="p-inputtext p-component form-control" />

    <div class="relative">
        <label :for="`imagesUpload${key}`" class="cursor-pointer">
            <slot>
                <div class="flex items-center gap-2 bg-white border border-input-border-color rounded-base-lg px-base-2 py-base-1 dark:bg-slate-800 dark:border-white/10" :class="{'border-invalid-color': error}">
                    <BaseIcon name="camera" />
                    <span>{{ $t('Click here to upload') }}</span>
                </div>
            </slot>
        </label>
        <input v-if="multiple" type="file" @change="handleUploadImages($event)" class="absolute top-0 left-0 w-0 h-0 text-base-none opacity-0 cursor-pointer" :id="`imagesUpload${key}`" :name="`imagesUpload${key}`" :ref="`imagesUpload${key}`" multiple accept="image/*" />
        <input v-else type="file" @change="handleUploadImages($event)" class="absolute top-0 left-0 w-0 h-0 text-base-none opacity-0 cursor-pointer" :id="`imagesUpload${key}`" :name="`imagesUpload${key}`" :ref="`imagesUpload${key}`" accept="image/*" />
    </div>

    <div v-if="images_upload.length" class="flex flex-wrap gap-2 mt-2">
        <Draggable :list="images_upload" @dragover="preventPhotosListDrag($event)" @dragend="endDraggingPhotos($event)" class="flex flex-wrap gap-2">
            <TransitionGroup type="transition">
                <div
                    v-for="(image, index) in images_upload"
                    :key="image.subject.id"
                    class="inline-block w-24 h-24 md:w-[150px] md:h-[150px] bg-cover bg-center relative cursor-pointer border border-divider rounded-base-lg dark:border-white/10"
                    :style="{ backgroundImage: `url(${image.subject.url})`}"
                    @click="openGallery(index)"
                >
                    <button class="inline-flex absolute top-2 end-2 bg-black/30 text-white w-6 h-6 rounded-base" @touchend.prevent="removeImage(image)" @click.stop.prevent="removeImage(image)">
                        <BaseIcon name="close" size="24" />
                    </button>
                </div>
            </TransitionGroup>
        </Draggable>
        <div v-for="index in images_upload_loading" :key="index" class="inline-block w-24 h-24 md:w-[150px] md:h-[150px] bg-cover bg-center relative rounded-base-lg border border-divider dark:border-white/10 float-start status-box-image-upload-list-loading">
            <div class="loading-icon">
                <div class="loader"></div>
            </div>
        </div>
        <button v-if="multiple" class="add-images-icon inline-flex items-center justify-center w-24 h-24 md:w-[150px] md:h-[150px] bg-cover bg-center text-main-color border border-divider dark:text-white/50 dark:border-white/10 rounded-base-lg hover:bg-hover" @click="this.open()">
            <BaseIcon name="photo" size="40"/>
        </button>

        <Galleria v-model:activeIndex="activeIndex" v-model:visible="displayCustom" :value="images_upload" :numVisible="0" :circular="true" :fullScreen="true" :showItemNavigators="true" :showThumbnails="false">
            <template #item="slotProps">
                <img :src="slotProps.item.subject.url" :alt="slotProps.item.subject.url" style="width: 100%; display: block" />
            </template>
            <template #thumbnail="slotProps">
                <img :src="slotProps.item.subject.url" :alt="slotProps.item.subject.url" style="display: block" />
            </template>
        </Galleria>
    </div>
    <small v-if="error" class="p-error">{{error}}</small>
</template>

<script>
import { uuidv4 } from '@/utility/index';
import BaseIcon from '@/components/icons/BaseIcon.vue';
import {VueDraggableNext as Draggable} from "vue-draggable-next";
import { useUtilitiesStore } from '@/store/utilities';
import {mapActions} from "pinia";
import Galleria from "primevue/galleria";

export default {
    components: {Galleria, Draggable, BaseIcon },
    props: {
        modelValue: {
			type: Array,
			default: null
		},
        items: {
            type: Array,
            default: null
        },
        multiple: {
            type: Boolean,
            default: false
        },
		error: {
			type: String,
			default: null
		},
        upload: {
            type: Function,
            required: true
        }
    },
    data: function () {
        return {
            key: uuidv4(),
            images_upload: this.modelValue || [],
            images_upload_loading: [],
            activeIndex: 0,
            displayCustom: false,
        }
    },
    mounted() {
        if (this.items !== null && this.items.length > 0) {
            this.images_upload = this.items;
        }
	},
    watch: {
        images_upload: {
            handler: function (data){
                this.$emit('update:modelValue', data);
            },
            deep: true
        }
    },
    methods: {
        ...mapActions(useUtilitiesStore, ['setEventDragDrop']),
        async handleUploadImages(event){
            await this.startUploadImages(event.target.files)
        },
        async removeImage(image) {
            try {
                this.images_upload = this.images_upload.filter(imageTmp => imageTmp.id !== image.id);
                this.reset()
            } catch (error) {
                this.showError(error.error)
            }
        },
        async startUploadImages(uploadedFiles, clipboard){
            if (typeof clipboard === 'undefined') {
                clipboard = false
            }
            for(let i = 0; i < uploadedFiles.length; i++ ){
                if (clipboard) {
                    // Skip content if not image
                    if (uploadedFiles[i].type.indexOf("image") === -1) continue;
                }
                var checkUpload = true
                if (! clipboard) {
                    checkUpload = this.checkUploadedData(uploadedFiles[i])
                }
                if(checkUpload){
                    let formData = new FormData()
                    let blob = uploadedFiles[i];
                    if (clipboard) {
                        blob = uploadedFiles[i].getAsFile();
                    }
                    formData.append('file', blob);
                    this.images_upload_loading.unshift(i);
                    try {
                        const response = await this.upload(formData);
                        if (this.multiple) {
                            this.images_upload.push(response);
                        }else {
                            this.images_upload = [];
                            this.images_upload.push(response);
                        }
                        this.images_upload_loading.shift();
                    } catch (error) {
                        this.showError(error.error);
                        this.images_upload_loading.shift()
                        this.reset()
                    }
                }
            }
            this.reset()
        },
        preventPhotosListDrag(e){
            e.preventDefault()
            e.stopPropagation()
        },
        endDraggingPhotos(e){
            this.setEventDragDrop(e)
        },
        open(){
            this.$refs[`imagesUpload${this.key}`].click()
        },
        reset(){
            this.$refs[`imagesUpload${this.key}`].value = null
        },
        openGallery: function (index){
            this.activeIndex = index;
            this.displayCustom = true;
        }
    },
    emits: ['update:modelValue']
}
</script>
