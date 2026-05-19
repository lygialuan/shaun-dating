<template>
    <div v-for="postItem in postItemsList" :key="postItem.id" class="activity_content share_link_activity_content"
        :style="{ backgroundColor: `${postItem.subject.photo?.params.dominant_color}`}">
        <div v-if="postItem.subject.photo" class="activity_content_thumb w-full">
            <a :href=postItem.subject.url target="_blank">
                <img class="activity-img w-full" :src=postItem.subject.photo.url>
            </a>
        </div>
        <div v-if="postItem.subject.title" class="fetched-link activity_content_info bg-primary-color p-base-2 text-white dark:bg-dark-primary-color">
            <div class="font-bold block">
                <a :href=postItem.subject.url class="text-inherit break-word" target="_blank">{{postItem.subject.title}}</a>
            </div>                    
            <div v-if="postItem.subject.description" class="truncate-3 mt-2">{{postItem.subject.description}}</div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['postItemsList']
}
</script>