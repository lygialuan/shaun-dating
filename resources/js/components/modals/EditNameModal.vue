<template>
    <form @submit.prevent="handleSaveName(nameData.content, nameData.subject_id)" class="space-y-base-2">
        <BaseTextarea v-model="nameData.content" autofocus autoResize />
        <BaseButton fluid>{{ $t('Save') }}</BaseButton>
    </form>
</template>

<script>
import { storeNameGroup } from '@/api/group'
import BaseTextarea from '@/components/inputs/BaseTextarea.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'

export default {
    inject: ['dialogRef'],
    data(){
        return{
            nameData: this.dialogRef.data.nameData
        }
    },
    components: { BaseTextarea, BaseButton },
    methods: {
        async handleSaveName(name, subjectId = null){
            try {
                switch (this.nameData.subject_type) {
                    case 'group':
                        await storeNameGroup({
                            id: subjectId,
                            name: name
                        });
                        break;
                    default:
                        break;
                }
                this.dialogRef.close({name: name});
                this.showSuccess(this.$t('Your changes have been saved.'))
            } catch (error) {
                this.showError(error.error)
            }
        },
    }
}
</script>