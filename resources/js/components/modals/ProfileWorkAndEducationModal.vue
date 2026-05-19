<template>
    <div class="flex flex-col space-y-2 mb-4"> 
        <label>{{$t('School')}}</label>
        <BaseInputText v-model="school_name" :error="error.school_name"/>
    </div>
    <div class="flex flex-col space-y-2 mb-4"> 
        <label>{{$t('Job Title')}}</label>
        <BaseInputText v-model="job_title" :error="error.job_title"/>
    </div>
    <div class="flex flex-col space-y-2 mb-4"> 
        <label>{{$t('Company')}}</label>
        <BaseInputText v-model="company_name" :error="error.company_name"/>
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

export default {
	components: { 
        BaseInputText,
        BaseButton,
    },
    inject: {
        dialogRef: {
            default: null
        }
    },
    data() {
        return{
			school_name: null,
			job_title: null,
			company_name: null,
            error: {
				school_name: null,
				job_title: null,
				company_name: null,
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
		...mapActions(useAuthStore, ['storeProfileSettings', 'updateUserMeInfo', 'me']),
        setBasicInfoUser(){
            const user = this.localUser
            this.school_name = user.school_name
            this.job_title = user.job_title
            this.company_name = user.company_name
        },
        async saveProfileSettings(){
			if (this.loadingSave) {
				return
			}
			this.loadingSave = true
			try {
                const response = await this.storeProfileSettings({
                    school_name: this.school_name,
                    job_title: this.job_title,
                    company_name: this.company_name,
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
        }
    }
}
</script>