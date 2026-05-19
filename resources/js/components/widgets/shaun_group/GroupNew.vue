<template>
    <WidgetContainer v-if="groupsList.length">
        <template v-if="data.enable_title" v-slot:title>{{ data.title }}</template>
        <template v-slot:body>
            <GroupsList :loading="!groupsList" :groups-list="groupsList" :has-load-more="false" :listStyle="(['left', 'right'].includes(position) ? 'list' : 'slider')" />
		</template>
    </WidgetContainer>
</template>

<script>
import WidgetContainer from '@/components/article/WidgetContainer.vue';
import GroupsList from '@/components/group/GroupsList.vue';

export default {
    props: ['data', 'params', 'position'],
    components: {
        WidgetContainer,
        GroupsList
    },
    data(){
        return{
            groupsList: this.data.data
        }
    }
}
</script>