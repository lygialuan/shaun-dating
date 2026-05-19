<template>
    <div class="space-y-base-2">
        <div>
            <label class="mb-base-1">{{ $t('Title') }}</label>
            <BaseInputText v-model="ruleTitle" />
        </div>
        <div>
            <label class="mb-base-1">{{ $t('Description') }}</label>
            <BaseTextarea v-model="ruleDescription" />
        </div>
        <BaseButton @click="handleSaveRule" fluid>{{ $t('Save') }}</BaseButton>
    </div>
</template>

<script>
import { storeGroupRule } from '@/api/group'
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import BaseTextarea from '@/components/inputs/BaseTextarea.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'

export default {
    components: { BaseButton, BaseInputText, BaseTextarea },
    data() {
        return {
            ruleId: this.dialogRef.data?.id,
            groupId: this.dialogRef.data.groupId,
            ruleTitle: this.dialogRef.data.title,
            ruleDescription: this.dialogRef.data.description
        }
    },
    inject: ['dialogRef'],
    methods: {
        async handleSaveRule(){
            try {
                await storeGroupRule({
                    id: this.ruleId,
                    group_id: this.groupId,
                    title: this.ruleTitle,
                    description: this.ruleDescription
                })
                this.dialogRef.close();
            } catch (error) {
                this.showError(error.error)
            }
        }
    }
}
</script>