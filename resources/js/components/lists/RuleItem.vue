<template>
    <div class="flex gap-4">
        <span class="flex items-center justify-center font-semibold w-9 h-9 bg-web-wash rounded-full text-center dark:bg-dark-web-wash">
            {{ item.number }}
        </span>
        <div class="flex-1">
            <div class="font-semibold break-word">{{ item.title }}</div>
            <ContentHtml :content="item.description"/>
        </div>
    </div>
</template>

<script>
import ContentHtml from '@/components/utilities/ContentHtml.vue'

export default {
    components: { ContentHtml },
    props: {
        item: Object
    }
}
</script>