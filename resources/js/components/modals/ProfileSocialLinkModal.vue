<template>
    <div class="mb-3"> 
        <div v-for="(link, index) in links" :key="index" class="flex items-center gap-base-2 mb-3">
            <label>{{ $t('URL') }}</label>
            <BaseInputText class="flex-2" v-model="links[index].link"/>
            <button v-if="links.length > 1" @click="removeMoreLink(index)"><BaseIcon name="close" size="18" class="text-white bg-red-500 p-1 rounded-full"/></button>
        </div>
        <small v-if="error.links" class="block p-error mb-2">{{error.links}}</small>
        <button class="block font-bold text-primary-color dark:text-dark-primary-color" @click="addMoreLink">{{$t('Add more link')}}</button>
    </div>
   <div class="w-full space-y-2"> 
        <BaseButton :loading="loadingSave" @click="saveProfileSettings()" fluid>{{$t('Save')}}</BaseButton>
        <BaseButton type="transparent" @click="cancel()" fluid>{{$t('Cancel')}}</BaseButton>
    </div>
</template>

<script>
import { mapActions } from 'pinia'
import { useAuthStore } from '@/store/auth'
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'

export default {
	components: { 
        BaseInputText,
        BaseButton,
        BaseIcon
    },
    inject: {
        dialogRef: {
            default: null
        }
    },
    data() {
        return{
			links: null,
            error: {
				links: null,
			},
			loadingSave: false,
        }
    },
    computed: {
        localUser(){
            return this.dialogRef?.data?.user || {}
        }
    },
    watch: {
        localUser: {
            immediate: true,
            handler(user){
                if (!user || !user.id) return
                this.setBasicInfoUser()
            }
        }
    },
    methods: {
		...mapActions(useAuthStore, ['storeProfileSettings', 'me']),
        setBasicInfoUser(){
            const user = this.localUser
            this.links = user.links.length ? user.links : [{ title: '', link: '' }]
        },
        async saveProfileSettings(){
			if (this.loadingSave) {
				return
			}
			this.loadingSave = true
			try {
                // Check if links is only [{title: "", link: ""}]
                let linksToSave = this.links;
                if (Array.isArray(this.links) &&
                    this.links.length === 1 &&
                    !this.links[0].title &&
                    !this.links[0].link) {
                    linksToSave = "";
                }

                const response = await this.storeProfileSettings({
                    links: linksToSave,
                })
                this.showSuccess(this.$t('Your changes have been saved.'))
                this.resetErrors(this.error)
                this.me()
                this.dialogRef.close(response)
			} catch (error) {
				this.handleApiErrors(this.error, error)
			} finally {
				this.loadingSave = false
			}
		},
        cancel(){
            this.dialogRef.close()
        },
        addMoreLink(){
			this.error.links = null
			this.links.push({ title: '', link: '' });
		},
		removeMoreLink(id){
			this.links = this.links.filter((link, index) => index != id)

			if (this.links.length == 0) {
				this.links.push({ title: '', link: '' });
			}
		}
    }
}
</script>