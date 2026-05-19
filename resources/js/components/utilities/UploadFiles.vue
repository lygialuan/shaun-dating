<template>
     <div class="relative">
        <label :for="`filesUpload${key}`" class="cursor-pointer" v-tooltip.top="{ value: tip, showDelay: 1000 }">
            <slot><BaseIcon name="paperclip" /></slot>
        </label>
        <input type="file" @change="uploadChatFiles($event)" class="absolute top-0 left-0 w-0 h-0 text-base-none opacity-0 cursor-pointer" :id="`filesUpload${key}`" :name="`filesUpload${key}`" :ref="`filesUpload${key}`" multiple />
    </div>
</template>

<script>
import { uuidv4 } from '@/utility/index'
import BaseIcon from '@/components/icons/BaseIcon.vue';

export default {
    components: { BaseIcon },
    props: {
        tip: {
            type: String,
            default: ''
        }
    },
    data(){
        return {
            key: uuidv4()
        }
    },
    methods: {
        uploadChatFiles(event) {
            this.$emit('upload', event);
        },
        open(){
            this.$refs[`filesUpload${this.key}`].click()
        },
        reset(){
            this.$refs[`filesUpload${this.key}`].value = null
        }
    },
    emits: ['upload']
}
</script>