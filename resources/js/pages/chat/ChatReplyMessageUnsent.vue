<template>
    <div class="flex items-center gap-base-2" :class="{'flex-row-reverse': !owner}">
        <div class="flex-1"></div>
        <div class="relative whitespace-pre-wrap text-xs break-word p-base-2 rounded-xl max-w-3/4 w-max bg-reply-color border border-reply-color dark:bg-slate-700 dark:border-white/10 -mb-2">
            <div class="whitespace-pre-wrap">{{ $t('Message has been unsent') }}</div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['owner']
}
</script>