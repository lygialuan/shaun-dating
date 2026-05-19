<template>
	<WidgetContainer v-if="data.data.length">
        <template v-if="data.enable_title" v-slot:title>{{ data.title }}</template>
        <template v-slot:body>
			<HashtagsList :hashtagsList="data.data" />
			<router-link :to="{name: 'hashtags', params: {tab: 'trending'}}" class="widget-box-header-link inline-block text-sm font-bold mt-4">
				{{$t('View All')}}
			</router-link>
		</template>
    </WidgetContainer>
</template>

<script>
import WidgetContainer from '@/components/article/WidgetContainer.vue';
import HashtagsList from '@/components/lists/HashtagsList.vue';

export default {
    components: { 
		WidgetContainer,
		HashtagsList
	},
	props: ['data', 'params', 'position']
}
</script>