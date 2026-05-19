<template>
	<MediaFeeds />
</template>
<script>
import MediaFeeds from './MediaFeeds.vue'
export default {
	components: { MediaFeeds },
	props: ['data', 'params', 'position']
}
</script>
