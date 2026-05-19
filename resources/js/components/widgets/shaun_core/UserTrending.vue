<template>
	<WidgetContainer v-if="data.data.length">
        <template v-if="data.enable_title" v-slot:title>{{ data.title }}</template>
        <template v-slot:body>
			<UsersList :loading="!data.data" :usersList="data.data" :positionWidget="position" :listStyle="(['left', 'right'].includes(position) ? 'list' : 'slider')" />
			<router-link :to="{name: 'users', params: {tab: 'trending'}}" class="widget-box-header-link inline-block text-sm font-bold mt-4">
				{{ $t('View All') }}
			</router-link>
		</template>
    </WidgetContainer>
</template>

<script>
import WidgetContainer from '@/components/article/WidgetContainer.vue';
import UsersList from '@/components/lists/UsersList.vue';

export default {
    components: {
		WidgetContainer,
		UsersList
	},
	props: ['data', 'params', 'position']
}
</script>