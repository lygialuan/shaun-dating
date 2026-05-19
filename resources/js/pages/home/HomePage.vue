<template>
	<HomeFeeds />
</template>

<script>
import HomeFeeds from '@/pages/home/HomeFeeds.vue'

export default {
	components: { HomeFeeds },
	props: ['data', 'params', 'position']
}
</script>