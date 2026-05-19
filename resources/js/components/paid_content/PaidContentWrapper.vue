<template>
    <template v-if="item.canViewContent"><slot></slot></template>
    <template v-else>
        <div v-if="ratio" class="relative bg-cover bg-center bg-no-repeat" :style="{ paddingBottom: ratio * 100 + '%', backgroundImage: `url(${item.thumb})` }">
            <PaidContentOverlay :item="item" :parent-item="parentItem" />
        </div>
        <div v-else class="relative">
            <img :src="item.thumb" class="min-h-60 mx-auto object-contain" />
            <PaidContentOverlay :item="item" :parent-item="parentItem" />
        </div>
    </template>
</template>

<script>
import PaidContentOverlay from '@/components/paid_content/PaidContentOverlay.vue' 

export default {
    props: {
        item: Object,
        ratio: {
            type: Number,
            default: 0
        },
        parentItem: {
            type: Object,
            default: null
        }
    },
    components: {
        PaidContentOverlay
    }
}
</script>