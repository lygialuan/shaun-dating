<template>
    <div class="flex flex-col gap-base-2">
        <div v-for="type in typesList" :key="type.value" class="flex gap-base-2">
            <BaseRadio v-model="groupType" :inputId="type.value" name="group_type" :value="type.value" />
            <label :for="type.value" class="flex-1">
                <div class="font-bold leading-5">{{ type.label }}</div>
                <div class="text-sub-color text-base-xs dark:text-slate-400">{{ type.description }}</div>
            </label>
        </div>
        <BaseButton :disabled="groupType === 0" @click="handleSelectPrivacy">{{ $t('Save') }}</BaseButton>
    </div>
</template>

<script>
import { storeGroupTypePrivate } from '@/api/group'
import BaseRadio from '@/components/inputs/BaseRadio.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'

export default {
    components: { BaseRadio, BaseButton },
    inject: ['dialogRef'],
    data(){
        return{
            typesList: [
                { value: 0, label: this.$t('Public Group'), description: this.$t('Everyone can join')},
                { value: 1, label: this.$t('Closed Group'), description: this.$t("Everyone can join but need approval from admins. Private groups can't be changed to public to protect the privacy of group members")}
            ],
            groupId: this.dialogRef.data.groupId,
            groupType: this.dialogRef.data.groupType
        }
    },
    methods: {
        async handleSelectPrivacy(){
            try {
                await storeGroupTypePrivate(this.groupId)
                this.dialogRef.close({type: this.groupType});
                this.showSuccess(this.$t('Your changes have been saved.'))
            } catch (error) {
                this.showError(error.error)
            }
        }
    }
}
</script>