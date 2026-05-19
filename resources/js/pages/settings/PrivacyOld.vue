<template>
    <Loading v-if="loading_status" />
    <form v-else @submit.prevent="saveSetting" class="space-y-3">
        <div class="flex flex-wrap gap-x-6">
            <div class="md:flex-1 md:text-end w-full mb-1"><label>{{$t('Who can see your posts and other account information')}}</label></div>
            <div class="md:flex-2 w-full"><BaseSelect v-model="privacy" :options="privacyLists" optionLabel="name" optionValue="value"></BaseSelect></div>
        </div>
        <div class="flex flex-wrap gap-x-6">
            <div class="md:flex-1 md:text-end w-full mb-1"><label>{{$t('Who can send you a message')}}</label></div>
            <div class="md:flex-2 w-full"><BaseSelect v-model="chat_privacy" :options="chatPrivacyLists" optionLabel="name" optionValue="value"></BaseSelect></div>
        </div>
        <div v-for="(value, key) in settings" :key="value.name" class="flex flex-wrap gap-x-6"> 
            <div class="flex-2 sm:flex-1 md:text-end w-full mb-1"><label>{{labelText(value.name)}}</label></div>
            <div class="flex-1 sm:flex-2 w-full">
                <BaseSwitch v-model="settings[key].value"/>            
            </div>  
        </div>
        <div class="flex flex-wrap gap-x-5">
			<div class="md:flex-1 md:text-end w-full"></div>
			<div class="md:flex-2 w-full">
				<BaseButton :loading="loading" fluid>{{$t('Save')}}</BaseButton>
			</div>
		</div>
    </form>
</template>

<script>
import BaseSwitch from '@/components/inputs/BaseSwitch.vue'
import BaseSelect from '@/components/inputs/BaseSelect.vue'
import Loading from '@/components/utilities/Loading.vue';
import BaseButton from '@/components/inputs/BaseButton.vue';
import {getPrivacySettings, savePrivacySettings} from '@/api/setting';

export default {
    components: { BaseSwitch, Loading, BaseSelect, BaseButton },
    mounted(){
        this.getPrivacySettings()
        this.privacyLists = [
            {
                value: 1,
                name: this.$t('Everyone'),
            },
            {
                value: 2,
                name: this.$t('My followers'),
            },
            {
                value: 3,
                name: this.$t('Only me'),
            }
        ];
        this.chatPrivacyLists = [
            {
                value: 1,
                name: this.$t('Everyone'),
            },
            {
                value: 2,
                name: this.$t('My followers'),
            },
            {
                value: 3,
                name: this.$t('No One'),
            }
        ];
    },
    data() {
        return {
            loading_status : true,
            settings: [],
            privacy: 1,
            privacyLists: null,
            chat_privacy: 1,
            chatPrivacyLists: null,
            success: null,
            error: null,
            loading: false
        }
    },
    methods: {
        labelText(name){
            switch(name){
                case 'show_following':
                    return this.$t('View my followings')
                case 'show_follower':
                    return this.$t('View my followers')
                default:
                    return null
            }
        },
        async getPrivacySettings() {
            try {
				const response = await getPrivacySettings()
                
                this.privacy = response.privacy;
                this.chat_privacy = response.chat_privacy;
                this.settings = window._.map(response.settings, function(value, key) {
                    return {
                        'name': key,
                        'value': value
                    };
                });
                this.loading_status = false
                return response
			} catch (error) {
                this.loading_status = false
			}
        },
        async saveSetting() {
            if (this.loading) {
                return
            }
            
            this.loading = true
            try {
                var data = {
                    'privacy': this.privacy,
                    'chat_privacy' : this.chat_privacy
                }
                window._.forEach(this.settings, function(value)  {
                    data[value.name] = value.value
                });            
				await savePrivacySettings(data)
                this.showSuccess(this.$t('Your changes have been saved.'))
			} catch (error) {
                this.showError(error.error)
			} finally {
                this.loading = false
            }
        }
    }
}
</script>

<style>

</style>