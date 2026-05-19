<template>
    <div class="main-content-section bg-white border border-white text-main-color rounded-none md:rounded-base-lg p-5 mb-base-2 dark:bg-dark-form-base dark:border-dark-form-base dark:text-white">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-base-2">
            <h3 class="text-main-color text-base-lg font-extrabold dark:text-white">{{ $t('Create New Profile') }}</h3>
        </div>
        <form @submit.prevent="handleCreatePage">
            <div class="mb-base-2">
                <label class="block mb-base-1">{{ $t('Profile Name') }}</label>
                <BaseInputText v-model="name" :placeholder="$t('Please enter profile name')" :error="error.name"/>
            </div>
            <div class="mb-base-2">
                <label class="block mb-base-1">{{ $t('Username') }}</label>
                <BaseInputText v-model="user_name" :placeholder="$t('Please enter username')" :error="error.user_name"/>
            </div>
            <div class="mb-base-2">
                <label class="block mb-base-1">{{ $t('About') }}</label>
                <BaseTextarea v-model="description" :placeholder="$t('Enter about')" :error="error.description" />
            </div>
            <BaseButton :loading="loadingCreate" fluid>{{ $t('Continue') }}</BaseButton>
        </form>
    </div>
</template>

<script>
import { storeUserPage } from '@/api/page'
import { mapState, mapActions} from 'pinia'
import { useAuthStore } from '@/store/auth'
import { useAppStore } from '@/store/app'
import { useUserStore } from '@/store/user'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import BaseTextarea from '@/components/inputs/BaseTextarea.vue'
import Constant from '@/utility/constant'

export default {
    components: { BaseButton, BaseInputText, BaseTextarea },
    data(){
        return{
            loadingCreate: false,
            name: null,
            user_name: null,
            description: null,
            categories: [],
            hashtags: [],
            error: {
				name: null,
				user_name: null,
                description: null,
                categories: null,
                hashtags: null
            }
        }
    },
    computed:{
        ...mapState(useAuthStore, ['user']),
        ...mapState(useAppStore, ['config']),
    },
    mounted() {
        if (this.user.is_page || !this.config.user_page.enable || !this.user.permissions['user_page.allow_create']) {
            this.setErrorLayout(true)
        }
    },
    methods: {
        ...mapActions(useAppStore, ['setErrorLayout']),
        ...mapActions(useUserStore, ['addUser']),
        async handleCreatePage(){
            this.loadingCreate = true
            try {
                const res = await storeUserPage({
                    name: this.name,
                    user_name: this.user_name,
                    description: this.description,
                    categories: this.categories
                })
                this.name = null
                this.user_name = null
                this.description = null
                this.showSuccess(this.$t('Your profile has been created.'))
                this.addUser(res)
                this.resetErrors(this.error)
            } catch (error) {
                if (error.error.code == Constant.RESPONSE_CODE_MEMBERSHIP_PERMISSION) {
					this.showPermissionPopup('user_page', error.error.message);
				} else {
					this.handleApiErrors(this.error, error)
				}
            } finally {
                this.loadingCreate = false
            }
        }
    }
}
</script>