<template>
    <div>
        <div class="flex items-center gap-base-2 font-medium">
            <BaseCheckbox v-model="isShowContentWarningSection" :inputId="`contentWarningSection_${key}`" :binary="true" />
            <label :for="`contentWarningSection_${key}`">{{ $t('Content Warning') }}</label>
        </div>
        <div v-if="isShowContentWarningSection" class="bg-light-web-wash text-main-color dark:text-white p-4 rounded-base-lg font-medium mt-base-2 dark:bg-dark-web-wash">
            <h5 class="text-base-lg font-semibold mb-base-1">{{ $t('Put a content warning on this post') }}</h5>
            <p class="mb-base-1">{{ $t('Select a category, and we’ll put a content warning on this post. This helps people avoid content they don’t want to see.') }}</p>
            <div v-for="contentWarning in contentWarningsList" :key="contentWarning.id" class="flex items-center gap-base-2 py-base-1">
                <BaseCheckbox :value="contentWarning" v-model="contentWarningSelecteds" :inputId="contentWarning.id.toString()" />
                <label :for="contentWarning.id">{{ contentWarning.name }}</label>
            </div>
        </div>
    </div>
</template>

<script>
import { getContentWarningCategories } from '@/api/content_warning'
import BaseCheckbox from '@/components/inputs/BaseCheckbox.vue'
import { uuidv4 } from '@/utility/index'

export default {
    components: { BaseCheckbox },
    data(){
        return{
            key: uuidv4(),
            isShowContentWarningSection: this.showContentWarningSection,
			contentWarningsList: [],
			contentWarningSelecteds: this.modelValue,
        }
    },
    props: {
        showContentWarningSection: {
            type: Boolean,
            default: false
        },
        modelValue: {
			type: Array,
			default: null
		}
    },
    mounted(){
        if(this.isShowContentWarningSection){
            this.getContentWarningCategoriesList()
        } else {
            this.contentWarningSelecteds = []
            this.$emit('update:modelValue', []);
        }
    },
    unmounted(){
        this.contentWarningSelecteds = []
        this.$emit('update:modelValue', []);
    },
    watch:{
		isShowContentWarningSection(){
			if(this.isShowContentWarningSection){
				this.getContentWarningCategoriesList()
			} else {
                this.contentWarningSelecteds = []
                this.$emit('update:modelValue', []);
            }
            this.$emit('put_content_warning', this.isShowContentWarningSection)
		},
        contentWarningSelecteds(value){
            this.$emit('update:modelValue', value);
        }
	},
    methods:{
        async getContentWarningCategoriesList(){
			try {
				const response = await getContentWarningCategories()
                this.contentWarningsList = response
			} catch (error) {
				this.showError(error.error)
			}
		}
    },
    emits: ['update:modelValue', 'put_content_warning']
}
</script>