<template>
     <div class="relative">
        <label :for="`videoUpload${key}`" class="cursor-pointer">
            <slot><BaseIcon name="youtube_logo" /></slot>
        </label>
        <input type="file" @change="uploadChatImages($event)" class="absolute top-0 left-0 w-0 h-0 text-base-none opacity-0 cursor-pointer" :id="`videoUpload${key}`" :name="`videoUpload${key}`" :ref="`videoUpload${key}`" accept="video/*" />
    </div>
</template>

<script>
import { uuidv4 } from '@/utility/index'
import BaseIcon from '@/components/icons/BaseIcon.vue';

export default {
    components: { BaseIcon },
    data(){
        return {
            key: uuidv4()
        }
    },
    methods: {
        uploadChatImages(event) {
            this.$emit('upload', event);
        },
        open(){
            this.$refs[`videoUpload${this.key}`].click()
        },
        reset(){
            this.$refs[`videoUpload${this.key}`].value = null
        }
    },
    emits: ['upload']
}
</script>