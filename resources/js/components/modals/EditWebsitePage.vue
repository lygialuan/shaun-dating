<template>
    <div>
        <div v-for="(website, index) in websites" :key="index" class="flex items-center gap-base-2 mb-3">
            <BaseInputText class="flex-1" v-model="websites[index].title" :placeholder="$t('Title')" />
			<BaseInputText class="flex-2" v-model="websites[index].link" :placeholder="$t('URL')" />
            <button v-if="websites.length > 1" @click="removeMoreLink(index)"><BaseIcon name="close" class="text-base-red"/></button>
        </div>
        <small v-if="errorWebsites" class="block p-error mb-2">{{errorWebsites}}</small>
        <button class="block text-xs font-bold text-primary-color dark:text-dark-primary-color mb-base-2" @click="addMoreLink">{{$t('Add more link')}}</button>
        <BaseButton @click="saveWebsites(websites)" fluid>{{ $t('Save') }}</BaseButton>
    </div>
</template>

<script>
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import { storeWebsitesPage } from '@/api/page'

export default {
    inject: ['dialogRef'],
    data(){
        return{
            websites: this.dialogRef.data.pageWebsites.length ? window._.cloneDeep(this.dialogRef.data.pageWebsites) : [{ title: '', link: '' }],
            errorWebsites: null
        }
    },
    components: { BaseInputText, BaseButton, BaseIcon },
    methods: {
        async saveWebsites(websites){
            try {
                // Check if links is only [{title: "", link: ""}]
                let linksToSave = websites;
                if (
                    Array.isArray(websites) &&
                    websites.length === 1 &&
                    !websites[0].title &&
                    !websites[0].link
                ) {
                    linksToSave = "";
                }
                const response = await storeWebsitesPage(linksToSave)
                this.dialogRef.close({ websites: response });
				this.showSuccess(this.$t('Your changes have been saved.'))
            } catch (error) {
                this.errorWebsites = error.error.detail['websites']
            }
        },
        addMoreLink(){
			this.errorWebsites = null
			this.websites.push({ title: '', link: '' });
		},
        removeMoreLink(id){
			this.websites = this.websites.filter((website, index) => index != id)
			if (this.websites.length == 0) {
				this.websites.push({ title: '', link: '' });
			}
		}
    }
}
</script>