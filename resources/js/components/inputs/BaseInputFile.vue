<template>
    <div class="relative">
        <input class="absolute inset-0 opacity-0 cursor-pointer" ref="inputFile" type="file" 
            :accept="accept" :multiple="multiple"
            @drop.prevent="handleDropFiles($event)"
            @change="handleChangeFiles($event)" :id="`file-input-${key}`" />
        <label class="p-inputtext block w-full text-left cursor-pointer" :for="`file-input-${key}`">
            <div class="flex items-center gap-base-2 min-w-0">
                <BaseIcon name="upload_simple" size="20"/>
                <span v-if="uploadedFile" class="truncate">{{ uploadedFile }}</span>
                <span v-else class="truncate"><slot></slot></span>       
            </div>
        </label>
    </div>
</template>
<script>
import { uuidv4 } from '@/utility/index'
import BaseIcon from '@/components/icons/BaseIcon.vue'

export default {
    components: { BaseIcon },
    props: {
        accept: {
			type: String,
			default: null
		},
        multiple: {
			type: Boolean,
			default: false
		}
    },
    data(){
        return{
            uploadedFile: null,
            key: uuidv4()
        }
    },
    methods: {
        handleDropFiles(event){
            this.startUploadFiles(event, event.dataTransfer.files)
        },
        handleChangeFiles(event){
            this.startUploadFiles(event, event.target.files)
        },
        startUploadFiles(e, files) {
            if (files.length > 1) {
                this.uploadedFile = files.length + ' ' + this.$t('files')
            }else{
                this.uploadedFile = files[0].name
            }
            this.$emit('upload-file', e);
        },
        clearSelectedFile(){
            this.$refs.inputFile.value = null
            this.uploadedFile = null
        }
    },
    emits: ['upload-file']
}
</script>