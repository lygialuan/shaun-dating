<template>
    <Loading v-if="loading_status" />
    <form v-else @submit.prevent="saveSetting" class="space-y-3">
        <div v-for="(value, key) in settings" :key="value.name" class="flex flex-wrap gap-x-6"> 
            <div class="flex-4"><label>{{labelText(value.name)}}</label>
                <div v-if="value.name === 'browse_profile_privately'" class="text-sm text-gray-400">{{ $t("We won't tell people when you you're visited their profile.") }}</div>
                <div v-if="value.name === 'hide_my_account'" class="text-sm text-gray-400">{{ $t("Hide from explore, search and swipe. People who interacted with you before still can see you in matched section.") }}</div>
            </div>
            <div class="flex-1">
                <BaseSwitch v-model="settings[key].value"/>            
            </div>  
        </div>
        <BaseButton :loading="loading" fluid>{{$t('Save')}}</BaseButton>
    </form>
</template>

<script>
import BaseSwitch from '@/components/inputs/BaseSwitch.vue'
import Loading from '@/components/utilities/Loading.vue';
import BaseButton from '@/components/inputs/BaseButton.vue';
import {getPrivacySettings, savePrivacySettings} from '@/api/setting';

export default {
    components: { BaseSwitch, Loading, BaseButton },
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
                case 'show_my_gift':
                    return this.$t('View my gifts')
                case 'browse_profile_privately':
                    return this.$t('Browse profile privately')
                case 'show_my_location':
                    return this.$t('Show my location')
                case 'show_my_age':
                    return this.$t('Show my age')
                case 'hide_my_account':
                    return this.$t('Hide my account')
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