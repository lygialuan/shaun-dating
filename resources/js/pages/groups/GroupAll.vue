<template>
    <div class="p-base-2 md:p-0">
        <form class="flex flex-wrap gap-base-2 mb-base-2" @submit.prevent="handleSearchGroups">
            <BaseInputText v-model="searchKeyword" :placeholder="$t('Keyword')" class="flex-1 min-w-[150px] max-w-[240px]"/>
            <BaseTreeSelect v-if="categoriesList.length" v-model="selectedCategory" :options="categoriesList" :placeholder="$t('Category')" class="flex-1 min-w-[150px] max-w-[240px]" />
            <BaseButton>{{$t('Search')}}</BaseButton>
        </form>
        <GroupsList :key="key" :loading="loadingGroupsList" :groups-list="groupsList" @load-more="loadmoreGroups" />
    </div>
</template>

<script>
import { mapActions, mapState } from 'pinia'
import { changeUrl } from '@/utility/index'
import { getGroupCategories } from '@/api/group'
import { useGroupStore } from '@/store/group'
import { uuidv4 } from '@/utility';
import GroupsList from '@/components/group/GroupsList.vue';
import BaseButton from '@/components/inputs/BaseButton.vue';
import BaseInputText from '@/components/inputs/BaseInputText.vue';
import BaseTreeSelect from '@/components/inputs/BaseTreeSelect.vue';

export default {
    props: ['categoryId'],
    components: { GroupsList, BaseButton, BaseInputText, BaseTreeSelect },
    data(){
        return {
            searchPage: 1,
            searchKeyword: '',
            selectedCategory: this.categoryId ? { [this.categoryId]: true } : { '': true },
            categoriesList: [],
            key: uuidv4()
        }
    },
    mounted() {
        this.handleGetGroupCategories()
        this.getAllGroupsList(this.searchPage, this.searchKeyword, this.formatedSelectedCategory)
    },
    unmounted(){
        this.unsetGroupsList()
    },
    computed: {
        ...mapState(useGroupStore, ['loadingGroupsList', 'groupsList']),
        formatedSelectedCategory(){
            return Object.keys(this.selectedCategory)[0]
        }
    },
    methods: {
        ...mapActions(useGroupStore, ['getAllGroupsList', 'unsetGroupsList']),
        async handleGetGroupCategories(){  
            try {
                const response = await getGroupCategories()
                this.categoriesList = window._.map(response, function(category) {
                    return { key: category.id, label: category.name, children: window._.map(category.childs, function(childKey){
                        return { key: childKey.id, label: childKey.name }
                    }) }
                });
                this.categoriesList = [{ key: "", label: this.$t('All')}, ...this.categoriesList]
            } catch (error) {
                console.log(error)
            }
        },
        handleSearchGroups(){
            this.searchPage = 1;
            this.key = uuidv4()
            this.getAllGroupsList(this.searchPage, this.searchKeyword, this.formatedSelectedCategory)
            let groupUrl = this.$router.resolve({
                name: 'groups',
                params: { tab: 'all' },
                query: { 'category_id': this.formatedSelectedCategory }
            });
            changeUrl(groupUrl.fullPath)
        },
        loadmoreGroups($state) {
			this.getAllGroupsList(++this.searchPage, this.searchKeyword, this.formatedSelectedCategory).then((response) => {
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