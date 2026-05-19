<template>
    <div v-if="message" class="relative border whitespace-pre-wrap break-word text-xs p-base-2 rounded-xl max-w-3/5 w-max -mb-2 bg-reply-color border-reply-color dark:bg-slate-700 dark:border-white/10" :class="replyTextClass">
        {{ $t('sent funds') }}
    </div>
</template>

<script>
export default {
    props: ['message', 'owner'],
    computed: {
        replyTextClass() {
            return {
                'ms-auto': this.owner
            }
        }
    }
}
</script>
