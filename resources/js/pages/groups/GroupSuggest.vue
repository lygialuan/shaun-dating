<template>
    <div class="p-base-2 md:p-0">
        <GroupsList :loading="loadingGroupsList" :groups-list="groupsList" @load-more="loadmoreSuggestGroups" />
    </div>
</template>

<script>
import { mapActions, mapState } from 'pinia'
import { useGroupStore } from '@/store/group'
import GroupsList from '@/components/group/GroupsList.vue';

export default {
    components: { GroupsList },
    data(){
        return {
            currentPage: 1
        }
    },
    mounted() {
        this.getSuggestGroupsList(this.currentPage)
    },
    unmounted(){
        this.unsetGroupsList()
    },
    computed: {
        ...mapState(useGroupStore, ['loadingGroupsList', 'groupsList'])
    },
    methods: {
        ...mapActions(useGroupStore, ['getSuggestGroupsList', 'unsetGroupsList']),
        loadmoreSuggestGroups($state) {
			this.getSuggestGroupsList(++this.currentPage).then((response) => {
				if(response.has_next_page){
                    $state.loaded()
                }else{
                    $state.complete()
                }
			})
		}
    }
}
</script>