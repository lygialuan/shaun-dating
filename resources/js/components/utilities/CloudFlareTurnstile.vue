<template>
    <VueTurnstile v-if="recaptchaType === 'turnstile' && siteKey" :site-key="siteKey" :theme="appearanceTheme" />
</template>

<script>
import { mapState } from 'pinia'
import { useAppStore } from '@/store/app'
import VueTurnstile from 'vue-turnstile';

export default {
    components: {
        VueTurnstile
    },
    data(){
        return{
            siteKey: window.siteConfig.turnstileSiteKey,
            recaptchaType: window.siteConfig.recaptchaType
        }
    },
    computed: {
        ...mapState(useAppStore, ['darkmode', 'systemMode']),
        appearanceTheme(){
            if (this.darkmode === 'on' || (this.darkmode === 'auto' && this.systemMode === 'dark')) {
                return 'dark';
            }
            return 'light';
        }
    }
}
</script>