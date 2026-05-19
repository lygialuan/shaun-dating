<template>
    <form @submit.prevent="saveDescription(descriptionData.content, descriptionData.subject_id)">
        <BaseTextarea v-model="descriptionData.content" autofocus autoResize class="mb-base-2" />
        <BaseButton fluid>{{ $t('Save') }}</BaseButton>
    </form>
</template>

<script>
import { storeDescriptionPage } from '@/api/page'
import { storeDescriptionGroup } from '@/api/group'
import BaseTextarea from '@/components/inputs/BaseTextarea.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'

export default {
    inject: ['dialogRef'],
    data(){
        return{
            descriptionData: this.dialogRef.data.descriptionData
        }
    },
    components: { BaseTextarea, BaseButton },
    methods: {
        async saveDescription(description, subjectId = null){
            try {
                switch (this.descriptionData.subject_type) {
                    case 'group':
                        await storeDescriptionGroup({
                            description: description,
                            id: subjectId
                        });
                        break;
                    default:
                        await storeDescriptionPage({
                            description: description
                        })
                        break;
                }
                this.dialogRef.close({description: description});
                this.showSuccess(this.$t('Your changes have been saved.'))
            } catch (error) {
                this.showError(error.error)
            }
        },
    }
}
</script>