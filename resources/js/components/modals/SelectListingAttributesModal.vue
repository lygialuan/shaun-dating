<template>
    <Loading v-if="dialogRef.data.isLoading"/>
    <template v-else>
        <div v-if="dialogRef.data.selectedAttributes.length > 0" class="flex flex-wrap gap-2 mb-3 max-h-20 overflow-y-auto overflow-x-hidden">
            <BaseFilterValue v-for="(item, index) in dialogRef.data.selectedAttributes" :key="index" @onRemove="doRemoveSelectedItems(index)">
                <div
                    v-if="item.icon"
                    :style="{
                        mask: `url(${item.icon}) center / contain no-repeat`,
                        WebkitMask: `url(${item.icon}) center / contain no-repeat`
                    }"
                    class="w-4 h-4 sm:w-5 sm:h-5 shrink-0 bg-gray-700 dark:bg-white">
                </div>
                <span>{{item.name}}</span>
            </BaseFilterValue>
        </div>
		<BaseInputText v-model="searchText" :placeholder="$t('Search')" class="mb-4" right_icon="search" />
        <div class="flex flex-row gap-1 h-72">
            <div class="flex-4 py-3 font-medium overflow-x-hidden overflow-y-auto">
                <div v-for="(category, index) in dialogRef.data.categoryObjects" :key="index" class="cursor-pointer mb-base-2" :class="[{'text-primary-blue font-bold': dialogRef.data.selectedAttributeId != null && dialogRef.data.selectedAttributeId === category.id}]" @click="handleClickAttribute(category)">
                    <div class="flex gap-3">
                        <div
                            v-if="category.icon"
                            :style="{
                                mask: `url(${category.icon}) center / contain no-repeat`,
                                WebkitMask: `url(${category.icon}) center / contain no-repeat`
                            }"
                            class="w-4 h-4 sm:w-5 sm:h-5 shrink-0 bg-gray-700 dark:bg-white">
                        </div>
                        <span>{{category.name}}</span>
                    </div>
                </div>
            </div>
            <div class="flex-4 border border-divider rounded-base-lg p-3 dark:border-white/10">
                <div class="flex flex-col h-full">
                    <div v-if="dialogRef.data.selectedAttributeName" class="text-base font-bold mb-1">{{dialogRef.data.selectedAttributeName}}</div>
                    <div v-if="dialogRef.data.paginatorAttributes.items" class="flex-1 overflow-x-hidden overflow-y-auto">
                        <div v-for="(item, idx) in dialogRef.data.paginatorAttributes.items" :key="idx" class="flex items-center my-1 gap-2 cursor-pointer" @click="handleClickItem(item)">
                            <div v-if="dialogRef.data.allowMultiple">
                                <BaseIcon v-if="isActiveItem(item)" name="check_square" />
                                <BaseIcon v-else name="uncheck_square" />
                            </div>
                            <div v-else>
                                <BaseIcon v-if="isActiveItem(item)" name="check_circle_fill" />
                                <BaseIcon v-else name="uncheck_circle" />
                            </div>
                            <span>{{ item.name }}</span>
                        </div>
                    </div>
                    <div v-if="dialogRef.data.paginatorAttributes.total_page > 0" class="flex gap-2 items-center pt-1">
                        <BaseIcon v-if="dialogRef.data.paginatorAttributes.current_page > 1" name="arrow_left" size="16" class="icon-btn text-highlight cursor-pointer" @click="handleClickBackLoadMore" />
                        <span v-if="dialogRef.data.paginatorAttributes.current_page < dialogRef.data.paginatorAttributes.total_page" class="text-highlight cursor-pointer font-semibold" @click="handleClickLoadMore">{{$t('Load more')}}</span>
                        &nbsp;
                    </div>
                </div>
            </div>
        </div>
    </template>
</template>
<script>
import BaseIcon from '@/components/icons/BaseIcon.vue';
import Loading from "@/components/utilities/Loading.vue";
import BaseFilterValue from '@/components/inputs/BaseFilterValue.vue';
import BaseInputText from '@/components/inputs/BaseInputText.vue'

export default {
    components:{ BaseIcon, Loading, BaseFilterValue, BaseInputText },
    inject: ['dialogRef'],
    data(){
        return {
            search_items: [],
            searchText: '',
            filteredCategoryAttributeValues: []
        }
    },
    mounted(){
        this.getModalData();
    },
    watch: {
        'dialogRef.data.categoryObjects': {
            deep: true,
            immediate: true,
            handler(val) {
                if (val && val.length && this.dialogRef.data.selectedIds?.length) {
                    this.updateSelectedAttributes();
                    this.doDefaultSelectedCategory();
                }
            }
        },
        searchText(val) {
            const keyword = val.trim().toLowerCase();

            if (!keyword) {
                this.filteredCategoryAttributeValues =
                    this.dialogRef.data.categoryAttributeValues;
            } else {
                this.filteredCategoryAttributeValues =
                    this.dialogRef.data.categoryAttributeValues.filter(item =>
                        item.name.toLowerCase().includes(keyword)
                    );
            }

            this.dialogRef.data.paginatorAttributes.current_page = 1;
            this.calculatorPaginatorAttributes();
        }
    },
    methods:{
        getModalData: function (){
            try{
                this.doDefaultSelectedCategory();
                this.updateDialogData();
            }catch (error){
                this.showError(error.error);
            }
        },
        calculatorPaginatorAttributes: function (){
            const list = this.filteredCategoryAttributeValues;
            if (list.length > 0) {
                this.dialogRef.data.paginatorAttributes.total_page =
                    Math.ceil(list.length / this.dialogRef.data.paginatorAttributes.limit);

                this.dialogRef.data.paginatorAttributes.current_page = 1;
                this.dialogRef.data.paginatorAttributes.items =
                    list.slice(0, this.dialogRef.data.paginatorAttributes.limit);
            } else {
                this.dialogRef.data.paginatorAttributes.total_page = 0;
                this.dialogRef.data.paginatorAttributes.current_page = 0;
                this.dialogRef.data.paginatorAttributes.items = [];
            }
        },
        updateSelectedAttributes: function (){
            this.dialogRef.data.selectedAttributes = [];
            if(this.dialogRef.data.selectedIds.length > 0 && this.dialogRef.data.categoryObjects.length > 0){
                this.search_items = [];
                this.dialogRef.data.selectedIds.forEach(id => {
                    this.dialogRef.data.categoryObjects.forEach(category => {
                        if(category.attribute_values.length > 0){
                            category.attribute_values.forEach(item => {
                                if(item.id === id){
                                    this.dialogRef.data.selectedAttributes.push(item);
                                }
                            });
                        }
                    });
                });
            }
        },
        handleClickAttribute: function (attribute){
            this.doActiveAttribute(attribute);
        },
        handleClickItem: function (item) {
            item.icon = this.getCurrentCategoryIcon().icon;
            item.category_name = this.getCurrentCategoryIcon().name;

            if (this.dialogRef.data.allowMultiple) {
                if (this.dialogRef.data.selectedAttributes.indexOf(item) === -1) {
                    this.dialogRef.data.selectedAttributes.push(item);
                } else {
                    this.dialogRef.data.selectedAttributes.splice(this.dialogRef.data.selectedAttributes.indexOf(item), 1);
                }
            }
            else {
                let selectedBefore = this.checkSelectedActiveAttributeValue();

                if (this.dialogRef.data.selectedAttributes.indexOf(item) === -1) {
                    if (selectedBefore) {
                        const selected = this.getSelectedActiveAttributeValue();
                        this.dialogRef.data.selectedAttributes.splice(this.dialogRef.data.selectedAttributes.indexOf(selected), 1);
                        if (selected.id != item.id) {
                            this.dialogRef.data.selectedAttributes.push(item);
                        }
                    }
                    else {
                        this.dialogRef.data.selectedAttributes.push(item);
                    }
                } else {
                    if (selectedBefore) {
                        const selected = this.getSelectedActiveAttributeValue();
                        this.dialogRef.data.selectedAttributes.splice(this.dialogRef.data.selectedAttributes.indexOf(selected), 1);
                        if (selected.id != item.id) {
                            this.dialogRef.data.selectedAttributes.push(item);
                        }
                    }
                    else {
                        this.dialogRef.data.selectedAttributes.push(item);
                    }
                }
            }
            this.updateDialogData();
        },
        checkSelectedActiveAttributeValue() {
            const valueIds = this.dialogRef.data.categoryAttributeValues.map(function (attribute) {
                return attribute.id;
            })
            let isExist = false;
            this.dialogRef.data.selectedAttributes.forEach(function (attribute) {
                if (valueIds.indexOf(attribute.id) != -1) {
                    isExist = true;
                }
            })
            return isExist;
        },
        getSelectedActiveAttributeValue() {
            const valueIds = this.dialogRef.data.categoryAttributeValues.map(function (attribute) {
                return attribute.id;
            })
            let value = null;
            this.dialogRef.data.selectedAttributes.forEach(function (attribute) {
                if (valueIds.indexOf(attribute.id) != -1) {
                    value = attribute;
                }
            })
            return value;
        },
        doActiveAttribute: function (category) {
            this.dialogRef.data.selectedAttributeId = category.id;
            this.dialogRef.data.selectedAttributeName = category.name;
            this.dialogRef.data.allowMultiple = category.allow_multiple;
            this.setAttributeActive();
        },
        isActiveItem: function (item) {
            return this.dialogRef.data.selectedAttributes.findIndex(x => x.id === item.id) !== -1;
        },
        setAttributeActive: function () {
            this.dialogRef.data.categoryObjects.forEach(category => {
                if(category.id === this.dialogRef.data.selectedAttributeId) {
                    this.dialogRef.data.categoryAttributeValues = category.attribute_values;

                    this.filteredCategoryAttributeValues = category.attribute_values;

                    this.calculatorPaginatorAttributes();
                }
            });
        },
        doDefaultSelectedCategory: function () {
            if(this.dialogRef.data.selectedAttributeId === null){
                if(this.dialogRef.data.categoryObjects.length > 0){
                    this.doActiveAttribute(this.dialogRef.data.categoryObjects[0]);
                }
            }
        },
        handleClickLoadMore: function () {
            let current_page = this.dialogRef.data.paginatorAttributes.current_page;
            let next_page = this.dialogRef.data.paginatorAttributes.current_page + 1;
            if(next_page <= this.dialogRef.data.paginatorAttributes.total_page){
                let start = current_page * this.dialogRef.data.paginatorAttributes.limit;
                let end = start + this.dialogRef.data.paginatorAttributes.limit;
                this.dialogRef.data.paginatorAttributes.current_page = next_page;
                this.dialogRef.data.paginatorAttributes.items = this.filteredCategoryAttributeValues.slice(start, end);
            }
        },
        handleClickBackLoadMore: function () {
            let prev_page = this.dialogRef.data.paginatorAttributes.current_page - 1;
            if(prev_page > 0){
                let start = 0;
                if(prev_page > 1){
                    start = (prev_page - 1) * this.dialogRef.data.paginatorAttributes.limit;
                }
                let end = start + this.dialogRef.data.paginatorAttributes.limit;
                this.dialogRef.data.paginatorAttributes.current_page = prev_page;
                this.dialogRef.data.paginatorAttributes.items = this.filteredCategoryAttributeValues.slice(start, end);
            }
        },
        doRemoveSelectedItems: function (index){
            this.dialogRef.data.selectedAttributes.splice(index, 1);
            this.updateDialogData();
        },
        updateDialogData: function (){
            this.dialogRef.data.selectedIds = this.dialogRef.data.selectedAttributes.map(x => x.id);
        },
        getCurrentCategoryIcon () {
            const category = this.dialogRef.data.categoryObjects.find(
                c => c.id === this.dialogRef.data.selectedAttributeId
            );

            return category ?? null;
        }
    }
}
</script>
