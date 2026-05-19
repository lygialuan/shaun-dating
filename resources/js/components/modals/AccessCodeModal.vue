<template>
    <form @submit.prevent="handleCheckAccessCode(code)">
        <BaseInputText v-model="code" autofocus />
        <div class="text-center mt-3">
            <BaseButton :loading="loading">{{$t('Enter')}}</BaseButton>
        </div>
    </form>
</template>

<script>
import { checkAccessCode } from '@/api/utility'
import localData from '@/utility/localData';
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'

export default {
    components: {
        BaseInputText,
        BaseButton
    },
    data(){
        return {
            code: null,
            loading: false
        }
    },
    methods: {
        async handleCheckAccessCode(code){
            if (this.loading) {
                return
            }
            this.loading = true
            try {
                await checkAccessCode({
                    access_code: code
                })
                localData.set('access_code', code)
                window.location.href = window.siteConfig.siteUrl
            } catch (error) {
                this.showError(error.error)
            } finally {
                this.loading = false
            }
        }
    }
}
</script>