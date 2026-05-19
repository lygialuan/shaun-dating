<template>
    <div class="flex flex-col space-y-2 mb-4"> 
        <BaseTextarea v-model="about" :error="error.about" :placeholder="$t('Tell a little about yourself')"/>
    </div>
    <div class="w-full space-y-2"> 
        <BaseButton :loading="loadingSave" @click="saveProfileSettings()" fluid>{{$t('Save')}}</BaseButton>
        <BaseButton type="transparent" @click="cancel()" fluid>{{$t('Cancel')}}</BaseButton>
    </div>
</template>

<script>
import { mapActions } from 'pinia'
import { useAuthStore } from '@/store/auth'
import BaseTextarea from '@/components/inputs/BaseTextarea.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'

export default {
	components: { 
        BaseTextarea,
        BaseButton,
    },
    inject: {
        dialogRef: {
            default: null
        }
    },
    data() {
        return{
			about: null,
            error: {
				about: null,
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
            this.about = user.about
        },
        async saveProfileSettings(){
			if (this.loadingSave) {
				return
			}
			this.loadingSave = true
			try {
                const response = await this.storeProfileSettings({
                    about: this.about,
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