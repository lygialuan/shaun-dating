<template>
	<WatchFeeds />
</template>
<script>
import WatchFeeds from './WatchFeeds.vue'
export default {
	components: { WatchFeeds },
	props: ['data', 'params', 'position']
}
</script>
