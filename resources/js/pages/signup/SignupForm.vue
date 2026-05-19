<template>
    <WidgetContainer class="max-w-sm mx-auto rounded-base-lg">
        <template v-slot:title>{{ $t('Create an account') }}</template>
        <template v-slot:body>
            <form @submit.prevent="handleSignup" class="flex flex-col gap-base-2">
                <BaseInputText v-model="name" :placeholder="$t('Name')" :error="error.name" left_icon="user" :autocomplete="'name'" />
                <BaseInputText v-model="user_name" :placeholder="$t('Username')" :error="error.user_name" left_icon="at" :autocomplete="'nickname'" />
                <BaseInputText v-model="email" :placeholder="$t('Email')" :error="error.email" left_icon="mail" :autocomplete="'on'" />
                <BaseInputTel v-if="config.phoneVerify" v-model="phoneNumber" :placeholder="$t('Phone Number')" :error="error.phone_number" />
                <BasePassword v-model="password" :error="error.password" :placeholder="$t('Password')" />
                <BaseSelect v-if="signupConfig?.signupField.genderShow" v-model="gender_id" :options="[{'id': 0, 'name': this.$t('Prefer not to say')}, ...signupConfig?.genders]" optionLabel="name" optionValue="id" :error="error.gender_id" />
                <BaseCalendar v-if="signupConfig?.signupField.birthShow" v-model="birthday" :placeholder="$t('Birthday')" :error="error.birthday"/>
                <BaseSelectLocation v-if="signupConfig?.signupField.locationShow" v-model="location" :show-label="false" :error="error"/>
                <BaseInputText v-if="signupConfig?.inviteOnly" v-model="ref_code" :placeholder="$t('Invitation Code')" :error="error.ref_code" left_icon="user_plus" />
                <div v-if="enableWidget" class="text-center">
                    <CloudFlareTurnstile v-model="turnstileToken" />
                </div>
                <div class="mt-base-2 text-center dark:text-dark-text-base-gray text-sm font-normal">{{ $t('By clicking Sign Up, you agree to our') }} <router-link :to="{name: 'sp_detail', params: {slug: 'terms-of-service'}}" class="text-primary-color dark:text-dark-primary-color font-bold">{{ $t('Terms of Service') }}</router-link> {{ $t('and') }} <router-link :to="{name: 'sp_detail', params: {slug: 'privacy-policy'}}" class="text-primary-color dark:text-dark-primary-color font-bold">{{ $t('Privacy Policy') }}</router-link>.</div>
                <BaseButton :loading="loadingSignup" :disabled="!isVerified()" fluid>{{$t('Sign up')}}</BaseButton>
            </form>
            <div class="mt-4">
                <template v-if="config.openidProviders.length">
                    <div class="text-center dark:text-dark-text-base-gray text-sm font-normal">{{$t('Or Continue Using')}}</div>
                    <div class="flex justify-center flex-wrap gap-3 my-4">
                        <a v-for="(openId, index) in config.openidProviders" :key="index" :href="openId.href" class="flex flex-row space-x-1 items-center p-2 dark:bg-[#2C2C2C] dark:border-[#2C2C2C] dark:text-white border rounded-lg">
                            <img class="max-w-[2rem]" :src="openId.photo" :alt="openId.name">
                            <span>{{ openId.name }}</span>
                        </a>
                    </div>
                </template>
            </div>
        </template>
    </WidgetContainer>
    <div class="text-center">{{$t('Already had an account?')}}&nbsp;<router-link :to="{name: 'login'}" class="text-primary-color dark:text-dark-primary-color font-bold">{{$t('Login')}}</router-link></div>
</template>
<script>
import { mapState, mapActions } from 'pinia'
import { useAppStore } from '@/store/app'
import { useAuthStore } from '@/store/auth'
import { getSignupConfig } from '@/api/auth'
import { useCaptcha } from '@/hooks/useCaptcha'
import localData from '@/utility/localData';
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import BasePassword from '@/components/inputs/BasePassword.vue'
import BaseSelect from '@/components/inputs/BaseSelect.vue'
import BaseCalendar from '@/components/inputs/BaseCalendar.vue'
import BaseSelectLocation from '@/components/inputs/BaseSelectLocation.vue'
import BaseInputTel from '@/components/inputs/BaseInputTel.vue'
import CloudFlareTurnstile from '@/components/utilities/CloudFlareTurnstile.vue'
import WidgetContainer from '@/components/article/WidgetContainer.vue'

export default {
    components: { BaseButton, BaseInputText, BasePassword, BaseSelect, BaseCalendar, BaseSelectLocation, BaseInputTel, CloudFlareTurnstile, WidgetContainer },
    data() {
        const appStore = useAppStore()
        const captcha = useCaptcha(appStore.config.signupEnableRecapcha, this.enableRecapcha, this.enableTurnstile)
        return {
            name: null,
            user_name: null,
            email: null,
            phoneNumber: null,
            password: null,
            ref_code : localData.get('ref_code', null),
            gender_id: 0,
            birthday: null,
            location: {
                country_id: null,
                state_id: null,
                city_id: null,
                zip_code: null,
                address: null
            },
            loadingSignup: false,
            error: {
				name: null,
				user_name: null,
				email: null,
				password: null,
                gender_id: null,
                birthday: null,
                country_id: null,
				state_id: null,
				city_id: null,
				zip_code: null,
                address: null,
                phone_number: null,
                ref_code: null
			},
            signupConfig: null,
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
        this.handleGetSignupConfig()
    },
    unmounted(){
        this.unloadRecaptcha(this.$recaptchaInstance)
    },
    methods: {
        ...mapActions(useAuthStore, ['signupUser']),
        async handleGetSignupConfig(){
            try {
                const response = await getSignupConfig()
                this.signupConfig = response
            } catch (error) {
                this.showError(error.error)
            }
        },
        async handleSignup(){
            this.loadingSignup = true
            try {
                const dataSignup = {
                    name: this.name,
                    user_name: this.user_name,
                    email: this.email,
                    password: this.password,
                    ref_code: this.ref_code,
                    gender_id: this.gender_id,
                    birthday: this.formatDateTime(this.birthday),
                    country_id: this.location.country_id,
                    state_id: this.location.state_id,
                    city_id: this.location.city_id,
                    zip_code: this.location.zip_code,
                    address: this.location.address,
                    phone_number: this.phoneNumber
                }
                dataSignup.token = await this.getCaptchaToken(this.$recaptcha, this.turnstileToken, "signup")

                await this.signupUser(dataSignup)
                this.resetErrors(this.error)
                if (this.ref_code) {
					localData.remove('ref_code')
				}
                window.location.href = window.siteConfig.siteUrl
            } catch (error) {
                this.resetErrors(this.error)
                if(error.error?.code == 'error_validate'){
                    Object.keys(this.error).forEach((key) => this.error[key] = error.error.detail[key] ? error.error.detail[key] : null)
                }else if(error.error?.code == 'inactive'){
                    this.showError(error.error.message)
                }else {
                    this.showError(error.error)
                }
            } finally {
                this.loadingSignup = false
            }
        }
    }
}
</script>