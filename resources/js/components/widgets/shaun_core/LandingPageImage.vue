<template>
    <div class="mb-base-2">
        <div v-if="data.enable_title" class="widget-box-header">
            <h3 class="widget-box-header-title">{{ data.title }}</h3>
        </div>
        <video v-if="data.data.extension === 'mp4'" autoplay muted loop class="w-full rounded-base max-h-[70vh] mx-auto">
            <source :src="data.data.file_url" type="video/mp4">
        </video>
        <img v-else :src="data.data.file_url" class="max-w-full rounded-base max-h-[70vh] mx-auto" />
    </div>
</template>

<script>
export default {
    props: ['data', 'params', 'position']
}
</script>