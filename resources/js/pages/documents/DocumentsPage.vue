<template>
	<DocumentFeeds />
</template>
<script>
import DocumentFeeds from './DocumentFeeds.vue'

export default {
	components: { DocumentFeeds },
	props: ['data', 'params', 'position']
}
</script>
