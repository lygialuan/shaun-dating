<template>
    <div v-for="(privacy, index) in privaciesList" :key="index" class="flex items-center gap-base-2 mb-base-2">
        <label :for="privacy.value" class="flex-1">
            <div class="flex items-center gap-base-2">
                <BaseIcon :name="privacy.icon"/>
                <div>
                    <div class="font-bold">{{ privacy.title }}</div>
                    <div class="text-base-xs">{{ privacy.description }}</div>
                </div>
            </div>
        </label>
        <BaseRadio :value="privacy.value" v-model="selectedPrivacyValue" :inputId="privacy.value.toString()" name="privacy" @change="handleSelectPrivacy(this.selectedPrivacyType, this.selectedPrivacyValue)" />
    </div>
</template>

<script>
import BaseIcon from '@/components/icons/BaseIcon.vue';
import BaseRadio from '@/components/inputs/BaseRadio.vue'
import { storePrivacyPage } from '@/api/page'

export default {
    components: { BaseIcon, BaseRadio },
    inject: ['dialogRef'],
    data(){
        return{
            privaciesList: [
                { title: this.$t('Public'), description: this.$t('Everyone will see'), icon: 'earth', value: 1 },
                { title: this.$t('Follower'), description: this.$t('Your followers will see'), icon: 'friend', value: 2 },
                { title: this.$t('Only Me'), icon: 'lock', value: 3 },
            ],
            selectedPrivacyType: this.dialogRef.data.selectedPrivacyType,
            selectedPrivacyValue: this.dialogRef.data.selectedPrivacyValue,
        }
    },
    methods: {
        async handleSelectPrivacy(type, value){
            try {
                await storePrivacyPage(type, value)
                this.dialogRef.close({type: type, value: value});
                this.showSuccess(this.$t('Your changes have been saved.'))
            } catch (error) {
                this.showError(error.error)
            }
        }
    }
}
</script>