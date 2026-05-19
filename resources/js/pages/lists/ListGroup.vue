<template>
    <h2 class="text-xl font-bold mb-4">{{ $t('Groups') }}</h2>
    <BaseSelect v-model="status" :options="statusList" optionLabel="name" optionValue="value" class="mb-base-2 max-w-xs" @change="handleFilterGroupsList" />
	<GroupsList :loading="loadingGroupsList" :groups-list="groupsList" @load-more="loadMoreGroups" :show-button="false" :show-badge="true" />
</template>

<script>
import { mapActions, mapState } from 'pinia'
import { useGroupStore } from '@/store/group'
import BaseSelect from '@/components/inputs/BaseSelect.vue'
import GroupsList from '@/components/group/GroupsList.vue';

export default {
    components: { BaseSelect, GroupsList },
    data() {
		return {
			status: 'all',
			currentPage: 1,
            statusList: [
                { name: this.$t('All'), value: 'all' },
                { name: this.$t('Active'), value: 'active' },
                { name: this.$t('Pending'), value: 'pending' },
                { name: this.$t('Hidden'), value: 'hidden' },
                { name: this.$t('Disable'), value: 'disable' }
            ]
		}
	},
    computed: {
		...mapState(useGroupStore, ['loadingGroupsList', 'groupsList'])
    },
    mounted(){
		this.getManagedGroupsList(this.status, this.currentPage)
    },
    unmounted(){
        this.unsetGroupsList()
    },
    methods: {
        ...mapActions(useGroupStore, ['getManagedGroupsList', 'unsetGroupsList']),
        loadMoreGroups($state) {
			this.getManagedGroupsList(this.status, ++this.currentPage).then((response) => {
				if(response.has_next_page){
                    $state.loaded()
                }else{
                    $state.complete()
                }
			})	
		},
        handleFilterGroupsList(){
            this.currentPage = 1
            this.getManagedGroupsList(this.status, this.currentPage)
        }
    }
}
</script>