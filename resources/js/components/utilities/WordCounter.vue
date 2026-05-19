<template>
    <div v-if="max && Number(max) !== 0 && (Number(max) - data.length < 100)" class="text-xs text-sub-color dark:text-slate-400">{{ data.length + ' / ' + max }}</div>
</template>

<script>
export default {
    props: {
        data: {
            type: [Number, String],
            default: 0
        },
        max: {
            type: [Number, String],
            default: 0
        }
    }
}
</script>