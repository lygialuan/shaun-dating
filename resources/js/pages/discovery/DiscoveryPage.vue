<template>
	<DiscoveryFeeds />
</template>
<script>
import DiscoveryFeeds from './DiscoveryFeeds.vue'
export default {
	components: { DiscoveryFeeds },
	props: ['data', 'params', 'position']
}
</script>
