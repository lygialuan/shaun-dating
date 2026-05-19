<template>
    <div class="space-y-base-2">
        <BaseSelectHashtags v-model="hashtagsData.selectedHashtags" />
        <BaseButton @click="saveHashtags(hashtagsData.selectedHashtags)" fluid>{{ $t('Save') }}</BaseButton>
    </div>
</template>

<script>
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseSelectHashtags from '@/components/inputs/BaseSelectHashtags.vue'
import { storeHashtagsPage } from '@/api/page'
import { storeGroupHashtags } from '@/api/group'

export default {
    inject: ['dialogRef'],
    data(){
        return{
            hashtagsList: [],
            hashtagsData: this.dialogRef.data.hashtagsData
        }
    },
    components: { BaseButton, BaseSelectHashtags },
    methods: {
        async saveHashtags(selectedHashtags){
            try {
                switch (this.hashtagsData.subject_type) {
                    case 'group':
                        await storeGroupHashtags({
                            id: this.hashtagsData.subject_id,
                            hashtags: selectedHashtags.map(hashtag => hashtag.name)
                        })
                        break;
                    default:
                        await storeHashtagsPage({
                            hashtags: selectedHashtags.map(hashtag => hashtag.name)
                        })
                        break;
                }
                this.dialogRef.close({hashtags: selectedHashtags})
                this.showSuccess(this.$t('Your changes have been saved.'))
            } catch (error) {
                this.showError(error.error)
            }
        }
    }
}
</script>