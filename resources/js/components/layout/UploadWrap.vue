<template>
    <div ref="uploadWrapper" class="relative h-full">
        <div v-if="isDragging" class="flex flex-col gap-5 items-center justify-center absolute inset-0 z-[999] rounded-lg bg-web-wash text-sub-color border-4 border-dashed border-secondary-box-color dark:bg-dark-web-wash dard:text-white">
            <BaseIcon name="upload_simple"/>
            <p class="text-2xl">{{ $t('Drop it here to upload') }}</p>
        </div>
        <slot></slot>
    </div>
</template>
<script>
import { mapState, mapActions } from 'pinia'
import { checkAdvancedUpload } from '@/utility/index'
import { useUtilitiesStore } from '@/store/utilities'
import BaseIcon from '@/components/icons/BaseIcon.vue';

export default {
    components: { BaseIcon },
    data(){
        return{
            isDragging: false,
            eventUploadWrap: null
        }
    },
    computed: {
        ...mapState(useUtilitiesStore, ['eventDragDrop']),
    },
    mounted() {
        if (checkAdvancedUpload()) {
            var self = this;
            ['dragover', 'dragleave', 'drop'].forEach(function (event) {
				self.$refs.uploadWrapper.addEventListener(event, function (e) {
					self.eventUploadWrap = e
				});
			});
		}
    },
    watch: {
        eventDragDrop(newValue) {
            if(newValue.type === 'dragover'){
                this.isDragging = true
            }else if(newValue.type === 'dragleave'){
                this.isDragging = false
            }

            if( newValue.type === 'dragend'){
                this.isDragging = false
            }
            else if (newValue.type === 'drop' || this.eventUploadWrap?.type === 'drop') {
                if(this.eventUploadWrap?.type === 'drop'){
                    this.$emit('drop_data', newValue)
                }
                this.eventUploadWrap = null
                this.isDragging = false
            }
        }
    },
    methods:{
        ...mapActions(useUtilitiesStore, ['setEventDragDrop']),
    },
    emits: ['drop_data']
}
</script>