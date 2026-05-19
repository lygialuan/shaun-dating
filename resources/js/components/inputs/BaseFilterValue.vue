<template>
    <div class="flex items-center gap-2 bg-white border border-web-dash text-xs font-semibold rounded-base-lg px-2 py-base-1 dark:bg-[#2C2C2C] dark:border-[#2C2C2C]">
        <slot></slot>
        <button v-if="icon" class="text-primary-blue leading-none" @click="handleRemoveFilter()">
            <BaseIcon :name="icon" size="12" />
        </button>
    </div>
</template>

<script>
import BaseIcon from '@/components/icons/BaseIcon.vue'

export default {
    props: {
        icon: {
			type: String,
			default: 'close'
		}
    },
    components: { BaseIcon },
    methods: {
        handleRemoveFilter() {
            this.$emit('onRemove');
        }
    },
    emits: ['onRemove'] 
}
</script>