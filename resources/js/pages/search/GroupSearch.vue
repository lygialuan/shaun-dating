<template>
	<div class="main-content-section">
        <GroupsList :loading="loadingGroupsList" :groups-list="groupsList" @load-more="loadmoreGroups">
            <template #empty>
                <div class="text-center">{{$t('No groups are found')}}</div>
            </template>
        </GroupsList>
	</div>
</template>

<script>
import { mapActions, mapState } from 'pinia'
import { useGroupStore } from '@/store/group'
import GroupsList from '@/components/group/GroupsList.vue';

export default {
	props: ["search_type", "type", "query"],
	components: { GroupsList },
	data(){
        return{
            queryData: this.query,
            currentPage: 1
        }
    },
    computed:{
        ...mapState(useGroupStore, ['loadingGroupsList', 'groupsList']),
    },
    mounted(){
        this.getSearchGroupsList(this.search_type, this.queryData, this.type, this.currentPage)
    },
    unmounted(){
        this.unsetGroupsList()
    },
    watch: {
        '$route'() {
			this.queryData = !window._.isNil(this.$route.query.q) ? this.$route.query.q : ''
            this.currentPage = 1
            if(this.queryData){
                this.getSearchGroupsList(this.search_type, this.queryData, this.type, this.currentPage)
            }
        }
    },
    methods: {
        ...mapActions(useGroupStore, ['getSearchGroupsList', 'unsetGroupsList']),
        loadmoreGroups($state) {
			this.getSearchGroupsList(this.search_type, this.queryData, this.type, ++this.currentPage).then((response) => {
                if (response.length === 0) {
                    $state.complete();
                } else {
                    $state.loaded();
                }
			})
		}
    } 
}
</script>