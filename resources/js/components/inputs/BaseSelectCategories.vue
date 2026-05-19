<template>
    <div>
        <BaseInputText :placeholder="$t('Select categories')" @click="handleClickCategoriesInput()" :error="error"/>
        <div v-if="categories.length > 0" class="flex flex-wrap gap-2 mt-base-2">
            <div v-for="category in categories" :key="category.id" class="text-sub-color inline-flex items-center rounded-base px-base-2 py-2 border border-secondary-box-color dark:text-slate-400 dark:border-white/30">
                <span class="font-bold text-xs me-2">{{category.name}}</span>
                <button @click="deleteSelectedCategories(category)" class="leading-none">
                    <BaseIcon name="close" size="16" />
                </button>
            </div>  
        </div>
    </div>
</template>

<script>
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import SelectCategoriesModal from '@/components/modals/SelectCategoriesModal.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'

export default {
    components: { BaseInputText, BaseIcon },
    props:{
        modelValue: {
			type: Array,
			default: () => []
		},
        type: {
			type: String,
			default: 'page'
		},
        error: {
			type: String,
			default: null
		}
    },
    data(){
        return{
            categories: this.modelValue,
        }
    },
    watch:{
        categories(newValue){
            this.$emit('update:modelValue', newValue.map(value => value.id));
        }
    },
    methods:{
        handleClickCategoriesInput(){
            this.$dialog.open(SelectCategoriesModal, {
                data: {
                    categoriesData: {
                        selected_category_ids: this.categories.map(category => category.id),
                        subject_type: this.type
                    }
                },
                props: {
                    header: this.$t('Select Categories'),
                    class: 'select-categories-modal',
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
                onClose: (options) => {
                    if(options.data){
                        this.categories = options.data.selectedCategoriesList
                    }
                }
            })
        },
        deleteSelectedCategories(categoryData){
            this.categories = this.categories.filter(category => category.id !== categoryData.id)
            var childrens = categoryData.childs;
            if(childrens && childrens.length){
                this.categories = window._.differenceBy(this.categories, childrens, 'id')
            }
		}
    },
    emits: ['update:modelValue']
}
</script>