<template>
    <div>
        <div class="relative">
            <input class="absolute inset-0 opacity-0 cursor-pointer" ref="inputFile" type="file" 
                :accept="accept" :multiple="multiple"
                @drop.prevent="handleDropFiles($event)"
                @change="handleChangeFiles($event)" :id="`file-input-${key}`" />
            <label class="p-inputtext block w-full text-left cursor-pointer" :for="`file-input-${key}`">
                <div class="flex items-center gap-3 min-w-0">
                    <BaseIcon name="upload_simple" />
                    <span class="truncate"><slot>{{ multiple ? $t('Upload Files') : $t('Upload File') }}</slot></span>       
                </div>
            </label>
        </div>
        <div v-if="uploadedFiles.length" class="flex flex-wrap gap-3 mt-3">
            <div v-for="uploadFile in uploadedFiles" :key="uploadFile.id" class="inline-flex items-center gap-2 max-w-xs p-2 bg-light-gray border border-dark-gray rounded-md relative"> 
                <BaseIcon name="file-invoice"/>
                <div class="truncate">{{ uploadFile.file.name }}</div>
                <button class="shadow-md inline-flex items-center justify-center absolute -top-2 -end-2 bg-white border border-divider text-main-color rounded-full w-5 h-5" @click="handleRemoveFile(uploadFile.id)">
                    <BaseIcon name="close" size="14" />
                </button>
            </div>
        </div>
        <ProgressBar v-for="fileId in loadingUploadedFiles" :key="fileId" :value="uploadProgressMap[fileId]" class="mt-2" />
    </div>
</template>
<script>
import { uuidv4 } from '@/utility/index'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import ProgressBar from '@/components/utilities/ProgressBar.vue'

export default {
    components: { BaseIcon, ProgressBar },
    props: {
        modelValue: {
			type: Array,
			default: null
		},
        accept: {
			type: String,
			default: null
		},
        multiple: {
			type: Boolean,
			default: false
		},
        upload: {
            type: Function,
            required: true
        }
    },
    data(){
        return{
            uploadedFiles: this.modelValue || [],
            key: uuidv4(),
            loadingUploadedFiles: [],
            uploadProgressMap: {}
        }
    },
    watch: {
        uploadedFiles: {
            handler: function (data){
                this.$emit('update:modelValue', data);
            },
            deep: true
        }
    },
    methods: {
        handleDropFiles(event){
            this.handleUploadFiles(event.dataTransfer.files)
        },
        handleChangeFiles(event){
            this.handleUploadFiles(event.target.files)
        },
        async handleUploadFiles(uploadedFiles) {
            for(let i = 0; i < uploadedFiles.length; i++ ){
                const file = uploadedFiles[i];
                const fileId = uuidv4();
                if(this.upload){
                    const formData = new FormData();
                    formData.append('file', file);

                    this.loadingUploadedFiles.unshift(fileId);
                    this.uploadProgressMap[fileId] = 0;

                    const onProgress = (progressEvent) => {
                        const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                        this.uploadProgressMap[fileId] = percentCompleted;
                    };

                    try {
                        const response = await this.upload(formData, onProgress);
                        if (this.multiple) {
                            this.uploadedFiles.push(response);
                        } else {
                            this.uploadedFiles = [response];
                        }
                    } catch (error) {
                        this.showError(error.error);
                    } finally {
                        this.loadingUploadedFiles = this.loadingUploadedFiles.filter(id => id !== fileId);
                        this.uploadProgressMap[fileId] = 0;
                    }
                } else {
                    if (this.multiple) {
                        this.uploadedFiles.push({id: fileId, file: file});
                    } else {
                        this.uploadedFiles = [{id: fileId, file: file}];
                    }
                }
            }
        },
        handleRemoveFile(fileId) {
            this.uploadedFiles = this.uploadedFiles.filter(uploadedFile => uploadedFile.id !== fileId);
            if(this.uploadedFiles.length === 0){
                this.clearSelectedFile()
            }
        },
        clearSelectedFile(){
            this.$refs.inputFile.value = null
            this.uploadedFiles = []
        }
    },
    emits: ['update:modelValue']
}
</script>