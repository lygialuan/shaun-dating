<template>
    <router-link :to="to" class="boxes-list-item text-xs text-sub-color font-bold inline-flex gap-2 items-center rounded-base px-base-2 py-2 border border-secondary-box-color dark:border-white/30 dark:text-slate-400">
        {{ item.name }}
    </router-link> 
</template>

<script>
export default {
    props: {
        item: Object,
        to: Object
    }
}
</script>