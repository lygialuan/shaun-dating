<template>
    <DropdownMenu>
        <template v-slot:dropdown-button>
            <button class="privacy-button flex gap-2 items-center font-semibold text-sm bg-web-wash p-base-1 rounded-md dark:bg-dark-web-wash">
                <BaseIcon :name="iconPrivacy(modelValue)" size="20"/>
                <div v-if="textPrivacy(modelValue)">{{ textPrivacy(modelValue) }}</div>
            </button>
        </template> 
        <template v-slot:dropdown-content>
            <div class="p-2 max-w-80">
                <div v-if="title" class="font-semibold mb-1">{{ title }}</div>
                <div v-if="description" class="text-xs text-sub-color dark:text-slate-400 mb-1">{{ description }}</div>
                <button v-for="(option, index) in visibleOptions" :key="index" class="flex gap-base-2 items-center py-base-1 w-full" @click="handleSelectPrivacy(option.value)">
                    <div class="btn-primary w-10 h-10 flex items-center justify-center bg-primary-color text-white rounded-full dark:bg-dark-primary-color">
                        <BaseIcon :name="option.icon"/>
                    </div>
                    <div class="font-semibold flex-1 text-start">{{ option.name }}</div>
                    <button v-if="isSelected(option.value)" class="text-primary-color">
                        <BaseIcon name="check"/>
                    </button>
                </button>
            </div>
        </template>
    </DropdownMenu>
</template>

<script>
import DropdownMenu from '@/components/utilities/DropdownMenu.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue';

export default {
    components: { DropdownMenu, BaseIcon },
    props: {
        modelValue: {
			type: [String, Number],
			default: null
		},
        options: {
            type: Array,
            required: true
        },
        title: {
            type: String,
            default: ''
        },
        description: {
            type: String,
            default: ''
        }
    },
    computed: {
        visibleOptions() {
            return this.options.filter(option => option.isShow || typeof(option.isShow) == 'undefined');
        }
    },
    methods:{
        isSelected(option){
            return this.modelValue === option
        },
        handleSelectPrivacy(option){
            this.$emit('update:modelValue', option);
        },
        iconPrivacy(option) {
            return this.options.find(item => item.value === option)?.icon || this.options[0].icon;
        },
        textPrivacy(option) {
            return this.options.find(item => item.value === option)?.label || this.options[0]?.label;     
        }
    },
    emits: ['update:modelValue']
}
</script>