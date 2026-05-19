<template>
	<DiscoveryFeeds />
</template>
<script>
import DiscoveryFeeds from '@/pages/discovery/DiscoveryFeeds.vue'
export default {
	components: { DiscoveryFeeds },
	props: ['data', 'params', 'position']
}
</script>
