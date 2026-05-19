<template>
	<Component :is="bookmarkTypeComponent"></Component>
</template>
<script>
import BookmarkedPosts from '@/pages/bookmarks/BookmarkedPosts.vue'

export default {
    props: ['data', 'params', 'position'],
	computed: {
        bookmarkTypeComponent() {
            return BookmarkedPosts;
        }
    }
}
</script>