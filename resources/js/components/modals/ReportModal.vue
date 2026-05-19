<template> 
    <Error v-if="error">{{error}}</Error>
    <div v-if="report_success_status" class="flex flex-col items-center">
        <BaseIcon name="check_circle" size="64" class="text-primary-color dark:text-dark-primary-color"></BaseIcon>
        <h3 class="text-2xl mt-4">{{$t('Thanks for letting us know.')}}</h3>
    </div>
    <div v-else>
        <BaseSelect class="mb-base-2" v-model="selectedReasonCategory" :options="reportCategoriesList" optionLabel="name" optionValue="id" />
        <BaseTextarea v-model="reason" :rows="5" :placeholder="$t('Reason')" class="mb-base-2"/>
        <div class="text-end">
            <BaseButton :loading="loading" @click="report()">{{$t('Send')}}</BaseButton>
        </div>
    </div>
</template>
<script>
import { getReportCategoriesList, reportItem } from '../../api/report';
import BaseSelect from '@/components/inputs/BaseSelect.vue'
import BaseTextarea from '@/components/inputs/BaseTextarea.vue'
import BaseButton from '@/components/inputs/BaseButton.vue';
import Error from '@/components/utilities/Error.vue';
import BaseIcon from '@/components/icons/BaseIcon.vue';

export default {
    components:{ BaseSelect, BaseTextarea, BaseButton, Error, BaseIcon },
    inject: ['dialogRef'],
    props:{
        type: {
            type: String,
            default: 'posts'
        },
    },
    data(){
        return {
            reportCategoriesList: [],
            error: null,
            selectedReasonCategory: null,
            reason: null,
            report_success_status: null,
            loading: false
        }
    },
    mounted(){
        this.getReportCategories()
    },
    methods:{
        async getReportCategories(){
            try {
				const response = await getReportCategoriesList();
                this.reportCategoriesList = response
                this.selectedReasonCategory = this.reportCategoriesList[0].id
			} catch (error) {
				this.error = error
			}  
        },
        async report(){
            if (this.loading) {
                return
            }
            this.loading = true
            try {
                await reportItem({
                    subject_type: this.dialogRef.data.type,
                    subject_id: this.dialogRef.data.id,
                    category_id: this.selectedReasonCategory,
                    reason: this.reason
                });
                this.report_success_status = true
            } catch (error) {
                this.showError(error.error)
            } finally {
                this.loading = false
            }
        }
    }
}
</script>