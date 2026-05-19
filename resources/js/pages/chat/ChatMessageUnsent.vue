<template>
    <div class="flex items-center gap-base-2" :class="{'flex-row-reverse': !owner}">
        <div class="flex-1"></div>
        <div class="relative whitespace-pre-wrap break-word text-sm p-base-2 rounded-xl max-w-3/4 w-max bg-white border border-divider dark:bg-slate-800 dark:border-white/10">
            <div class="whitespace-pre-wrap">{{ $t('Message has been unsent') }}</div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['owner']
}
</script>