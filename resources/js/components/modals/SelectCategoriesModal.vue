<template>
    <BaseInputText v-model="categoryKeyword" @input="handleSearchCategories()" :placeholder="$t('Search Categories')" :autofocus="true" class="mb-base-2"/>
    <form @submit.prevent="handleSelectCategories">
        <div v-if="categoriesList.length">
            <div v-for="category in filteredCategoriesList" :key="category.id">
                <div class="flex justify-between items-center gap-base-2 mb-4 font-bold">
                    <label :for="category.id" class="flex-1">{{ category.name }}</label>
                    <BaseCheckbox :value="category.id" v-model="categoriesData.selected_category_ids" :inputId="category.id.toString()" @change="handleChangeParentCategory(category)"/>
                </div>
                <div v-if="category.childs.length > 0">
                    <div v-for="childCategory in category.childs" :key="childCategory.id" class="flex justify-between items-center gap-base-2 ms-5 mb-4">
                        <label :for="childCategory.id" class="flex-1">{{ childCategory.name }}</label>
                        <BaseCheckbox :value="childCategory.id" v-model="categoriesData.selected_category_ids" :inputId="childCategory.id.toString()" @change="handleChangeChildCategory(category)"/>
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="text-center pb-12">{{$t('No results found')}}</div>
        <div class="p-dialog-footer-fixed absolute left-0 bottom-0 right-0 px-6 pt-base-2 pb-6 rounded-b-base-lg bg-white dark:bg-dark-form-base">
            <BaseButton fluid>{{ $t('Select')}}</BaseButton>
        </div>
    </form>
</template>

<script>
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseCheckbox from '@/components/inputs/BaseCheckbox.vue'
import { getPageCategories, storeCategoriesPage } from '@/api/page'
import { getGroupCategories, storeGroupCategories } from '@/api/group'
var typingTimer = null;

export default {
    components: { BaseButton, BaseInputText, BaseCheckbox },
    inject: ['dialogRef'],
    data(){
        return{
            categoryKeyword: null,
            categoriesList: [],
            filteredCategoriesList: [],
            categoriesData: this.dialogRef.data.categoriesData
        }
    },
    mounted(){
        this.fetchPageCategories()
    },
    methods: {
        closeStatusBox(){
            this.dialogRef.close()
        },
        async fetchPageCategories(){
            try {
                let response;
                switch (this.categoriesData.subject_type) {
                    case 'group':
                        response = await getGroupCategories();
                        break;
                    default:
                        response = await getPageCategories();
                        break;
                }
                this.categoriesList = response
                this.filteredCategoriesList = response
            } catch (error) {
                console.log(error)
            }
        },
        handleChangeChildCategory(parentCategory){
            if(!this.categoriesData.selected_category_ids.includes(parentCategory.id)){ // auto select parent category when select child categories
                this.categoriesData.selected_category_ids.push(parentCategory.id)
            }
        },
        handleChangeParentCategory(parentCategory){
            if(!this.categoriesData.selected_category_ids.includes(parentCategory.id)){  // auto unselect child categories when unselect parent category
                this.categoriesData.selected_category_ids = window._.differenceBy(this.categoriesData.selected_category_ids, parentCategory.childs.map(category => category.id))
            }
        },
        filterCategoriesList(list, search){
            return list.filter((category) => {
                var childrens = category.childs;
                if(childrens && childrens.length){
                    category.childs = this.filterCategoriesList(childrens, search.toLowerCase());
                    if(category.childs && category.childs.length){
                        return true;
                    }
                }
                return category.name.toLowerCase().indexOf(search.toLowerCase()) > -1;
            })
        },
        handleSearchCategories(){
            clearTimeout(typingTimer);
			typingTimer = setTimeout(() => {
                const categoriesList = window._.cloneDeep(this.categoriesList)
                this.filteredCategoriesList = this.filterCategoriesList(categoriesList, this.categoryKeyword)
            }
			, 400);
        },
        selectedCategoriesList(categoriesList, selectedCategorieIdsList){
            var selectedCategoriesList = []
            window._.forEach(categoriesList, function (category) {
                if(selectedCategorieIdsList.includes(category.id)){
                    selectedCategoriesList.push(category)
                }
                if(category.childs && category.childs.length){
                    window._.forEach(category.childs, function (category) {
                        if(selectedCategorieIdsList.includes(category.id)){
                            selectedCategoriesList.push(category)
                        }
                    });
                }
            });
            return selectedCategoriesList
        },
        async handleSelectCategories(){
            try {
                if(this.categoriesData.selected_type === 'save'){
                    switch (this.categoriesData.subject_type) {
                        case 'group':
                            await storeGroupCategories({
                                id: this.categoriesData.subject_id,
                                categories: this.categoriesData.selected_category_ids
                            });
                            break;
                        default:
                            await storeCategoriesPage({
                                categories: this.categoriesData.selected_category_ids
                            })
                            break;
                    }
                    this.showSuccess(this.$t('Your changes have been saved.'))
                }
                this.dialogRef.close({selectedCategoriesList: this.selectedCategoriesList(this.categoriesList, this.categoriesData.selected_category_ids)});
            } catch (error) {
                this.showError(error.error)
            }
        }
    }
}
</script>