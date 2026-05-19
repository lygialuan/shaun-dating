<template>
    <div class="inline-block cursor-pointer">
        <div @click="handleOpenTooltip">
            <BaseIcon name="info" :size="size" />
        </div>
        <OverlayPanel ref="tooltip_content" class="tooltip_content_op max-w-sm mt-base-1 text-base-xs">
            <div v-html="content"></div>
        </OverlayPanel>
    </div>
</template>

<script>
import BaseIcon from '@/components/icons/BaseIcon.vue'
import OverlayPanel from 'primevue/overlaypanel';

export default {
    props: {
        content: {
			type: String,
			default: null
		},
        size: {
            type: [String, Number],
            default: 20
        }
    },
    components: { BaseIcon, OverlayPanel },
    methods:{
        handleOpenTooltip(e){
            this.$refs.tooltip_content.toggle(e);
        }
    }
}
</script>