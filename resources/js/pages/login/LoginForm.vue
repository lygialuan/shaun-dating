<template>
    <WidgetContainer class="max-w-sm mx-auto rounded-base-lg">
        <template v-slot:title>{{ $t('Member Login') }}</template>
        <template v-slot:body>
			<BaseInputText v-model="user.email" :placeholder="config.phoneVerify ? $t('Email, username or phone number') : $t('Email or username')" :error="error.email" left_icon="user_circle" @keyup.enter="login" class="mb-base-2" />
            <BasePassword v-model="user.password" :placeholder="$t('Password')" autocomplete="current-password" :error="error.password" @keyup.enter="login" class="mb-base-2" />
            <div v-if="enableWidget" class="text-center">
                <CloudFlareTurnstile v-model="turnstileToken" />
            </div>
            <div class="mt-3 mb-3 text-right font-bold"><router-link :to="{ name: 'recover' }" class="text-primary-color dark:text-dark-text-base-gray">{{$t('Forgot password')}}</router-link></div>
            <BaseButton @click="login" :loading="loadingLogin" :disabled="!isVerified()" fluid>{{$t('Login')}}</BaseButton>
            <template v-if="config.openidProviders.length">
                <div class="my-5 text-center dark:text-dark-text-base-gray text-sm font-normal">{{$t('Or Continue Using')}}</div>
                <div class="flex flex-wrap gap-3 justify-center">
                    <a v-for="(openId, index) in config.openidProviders" :key="index" :href="openId.href" class="flex flex-row space-x-1 items-center p-2 dark:bg-[#2C2C2C] dark:border-[#2C2C2C] dark:text-white border rounded-lg">
                        <img class="max-w-[2rem]" :src="openId.photo" :alt="openId.name">
                        <span>{{ openId.name }}</span>
                    </a>
                </div>
            </template>
		</template>
    </WidgetContainer>
    <div v-if="this.config.signupEnable" class="mt-5 text-center">{{$t("Don't have an account?")}}&nbsp;<router-link :to="{ name: 'signup' }" class="text-primary-color dark:text-dark-primary-color font-bold">{{$t('Sign up')}}</router-link></div>
</template>
<script>
import { mapState } from 'pinia'
import { useAppStore } from '@/store/app'
import { useAuthStore } from '@/store/auth'
import { useCaptcha } from '@/hooks/useCaptcha'
import localData from '@/utility/localData'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BasePassword from '@/components/inputs/BasePassword.vue'
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import CloudFlareTurnstile from '@/components/utilities/CloudFlareTurnstile.vue'
import WidgetContainer from '@/components/article/WidgetContainer.vue'

export default {
    components: { BaseButton, BasePassword, BaseInputText, CloudFlareTurnstile, WidgetContainer },
    data() {
        const appStore = useAppStore()
        const captcha = useCaptcha(appStore.config.loginEnableRecapcha, this.enableRecapcha, this.enableTurnstile)
        return {
            user: {
                email: null,
                password: null
            },
            error: {
                email: null,
				password: null
			},
            loadingLogin: false,
            ...captcha
        }
    },
    computed: {
        ...mapState(useAppStore, ['config'])
    },
    mounted(){
        setTimeout(() => {
            this.loadRecaptcha(this.$recaptchaInstance)
        }, 2000);
    },
    unmounted(){
        this.unloadRecaptcha(this.$recaptchaInstance)
    },
    methods: {
        async login() {
            if (this.loadingLogin) return
            this.loadingLogin = true
            try {
                this.user.token = await this.getCaptchaToken(this.$recaptcha, this.turnstileToken, "login")
              
                await useAuthStore().login(this.user);
                
                const redirect = this.$route.query.redirect
                const target = redirect && atob(redirect).includes(window.siteConfig.siteUrl)
                    ? atob(redirect)
                    : window.siteConfig.siteUrl

                window.location.href = target
                
                this.resetErrors(this.error)
            }
            catch (error) {
                if(error.error.code === 'two_factor'){
                    localData.set('two_factor_code', error.error.detail.two_factory_code);
                    return this.$router.push({ name: 'two_factor_authentication' })
                }
                this.handleApiErrors(this.error, error)
            }
            finally {
                this.loadingLogin = false;
            }
        }
    }
}
</script>