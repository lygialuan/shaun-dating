<template>
    <div v-if="currentStep === 1" class="space-y-base-2">
        <div class="flex flex-col gap-base-2">                             
            <div>{{ $t("In addition to your username and password, you'll have to enter a code to sign in to your account.") }}</div>
            <div 
                v-for="provider in config.two_factor.providers" 
                :key="provider.id" 
                class="flex items-center gap-4 ps-3 rounded-lg border border-divider dark:border-white/10"
                :class="{'border-primary-color': method === provider.type}"
            >
                <BaseRadio v-model="method" :inputId="provider.type" name="method" :value="provider.type" />
                <label :for="provider.type" class="flex-1 py-3 pe-3">
                    <div class="font-bold">{{ provider.name }}</div>
                    <div class="text-xs text-sub-color dark:text-slate-400">{{ provider.description }}</div>
                </label>
            </div>
        </div>
        <BaseButton :disabled="!method" @click="handleMoveNextStep">{{$t('Next')}}</BaseButton>
    </div>
    <Component v-if="currentStep > 1" :is="methodComponent" :password="password" @back="handleMovePreviousStep" @success="handleEnableSuccessfully" />
</template>

<script>
import { mapState } from 'pinia'
import { useAppStore } from '@/store/app';
import BaseRadio from '@/components/inputs/BaseRadio.vue'
import BaseButton from '@/components/inputs/BaseButton.vue';
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import Mail from '@/components/two_factor/Mail.vue';
import Sms from '@/components/two_factor/Sms.vue';
import AuthenticationApp from '@/components/two_factor/AuthenticationApp.vue';

export default {
    components: {
        BaseRadio,
        BaseButton,
        BaseInputText
    },
    inject: ['dialogRef'],
    data(){
        return {
            method: null,
            currentStep: 1,
            password: this.dialogRef.data?.password
        }
    },
    computed:{
        ...mapState(useAppStore, ['config']),
        methodComponent() {
			switch(this.method){
				case 'mail':
					return Mail;
                case 'sms':
                    return Sms;
                case 'auth_app':
                    return AuthenticationApp;
				default: 
					return null
			}
		}
    },
    methods: {
        handleMoveNextStep(){
            this.currentStep = 2
        },
        handleMovePreviousStep(){
            this.currentStep = 1
        },
        handleEnableSuccessfully(){
            this.dialogRef.close({ enable: true });
        }
    }
}
</script>