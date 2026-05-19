<template>
    <form @submit.prevent="checkPassword">
        <BasePassword v-model="password" :error="error_password" autofocus />
        <p v-if="user.is_page" class="text-sub-color text-xs italic mt-base-1 dark:text-slate-400">{{ $t('Use the password of the admin of the page') }}</p>
        <div class="text-end mt-base-2">
            <BaseButton :loading="loading">{{$t('Confirm')}}</BaseButton>
        </div>
    </form>
</template>

<script>
import { mapState } from 'pinia'
import { checkPasswordAccount } from '@/api/user'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BasePassword from '@/components/inputs/BasePassword.vue'
import { useAuthStore } from '@/store/auth'

export default {
    components: { BaseButton, BasePassword },
    data(){
        return {
            password: this.dialogRef.data?.password,
            error_password: null,
            loading: false
        }
    },
    inject: ['dialogRef'],
    computed: {
        ...mapState(useAuthStore, ['user'])
    },
    methods: {
        async checkPassword(){
            if (this.loading) {
                return
            }

            this.loading = true
            try {
                await checkPasswordAccount({
                    password: this.password
                })
                this.$emit('confirm', {password: this.password});
            } catch (error) {
                this.error_password = error.error.detail.password
            } finally {
                this.loading = false
            }
        }
    },
    emits: ['confirm']
}
</script>