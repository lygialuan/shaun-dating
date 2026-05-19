<template>
    <WidgetContainer v-if="pagesList.length">
        <template v-if="data.enable_title" v-slot:title>{{ data.title }}</template>
        <template v-slot:body>
			<UserPagesList :loading="!pagesList" :pagesList="pagesList" :positionWidget="position" :listStyle="(['left', 'right'].includes(position) ? 'list' : 'slider')" />
		</template>
    </WidgetContainer>
</template>

<script>
import WidgetContainer from '@/components/article/WidgetContainer.vue';
import UserPagesList from '@/components/lists/UserPagesList.vue';

export default {
    props: ['data', 'params', 'position'],
    components: {
        WidgetContainer,
        UserPagesList
    },
    data(){
        return{
            pagesList: this.data.data
        }
    }
}
</script>