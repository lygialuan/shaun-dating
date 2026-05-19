<template>
    <Loading v-if="loading_status" />
    <form v-else @submit.prevent="saveNotificationSetting">
        <div class="text-main-color dark:text-white">
            <div class="flex justify-between mb-5">
                <h4>{{$t('All notification enabled')}}</h4>
                <BaseSwitch v-model="enable_notify" />
            </div>
            <div v-if="enable_notify" class="space-y-3 mb-3">
                <h4 class="font-bold dark:text-slate-400">{{$t('Related to your posts and posts you’re interacted with')}}</h4>
                <div v-for="(key, value) in posts" :key="value" class="field-checkbox flex items-center gap-base-2"> 
                    <BaseCheckbox :value="value" :inputId ="value" v-model="postsSelected"/>
                    <label :for="value">{{labelText(value)}}</label>
                </div>
                <h4 class="font-bold dark:text-slate-400">{{$t('Related to system notifications')}}</h4>
                <div v-for="(key, value) in systems" :key="value" class="field-checkbox flex items-center gap-base-2"> 
                    <BaseCheckbox :value="value" :inputId ="value" v-model="systemsSelected"/>
                    <label :for="value">{{labelText(value)}}</label>
                </div> 
                <h4 class="font-bold dark:text-slate-400">{{$t('Related to your followers')}}</h4>
                <div v-for="(key, value) in follows" :key="value" class="field-checkbox flex items-center gap-base-2"> 
                    <BaseCheckbox :value="value" :inputId ="value" v-model="followsSelected"/>
                    <label :for="value">{{labelText(value)}}</label>
                </div>
                <h4 class="font-bold dark:text-slate-400">{{$t('Related to message')}}</h4>
                <div v-for="(key, value) in chats" :key="value" class="field-checkbox flex items-center gap-base-2"> 
                    <BaseCheckbox :value="value" :inputId ="value" v-model="chatsSelected"/>
                    <label :for="value">{{labelText(value)}}</label>
                </div>
                <h4 class="font-bold dark:text-slate-400">{{$t('Related to story')}}</h4>
                <div v-for="(key, value) in stories" :key="value" class="field-checkbox flex items-center gap-base-2"> 
                    <BaseCheckbox :value="value" :inputId ="value" v-model="storiesSelected"/>
                    <label :for="value">{{labelText(value)}}</label>
                </div>  
                <h4 class="font-bold dark:text-slate-400">{{$t('Related to paid content')}}</h4>
                <div v-for="(key, value) in paidContents" :key="value" class="field-checkbox flex items-center gap-base-2"> 
                    <BaseCheckbox :value="value" :inputId ="value" v-model="paidContentsSelected"/>
                    <label :for="value">{{labelText(value)}}</label>
                </div>         
            </div>
            <BaseButton :loading="loading" fluid>{{$t('Save')}}</BaseButton>
        </div>
    </form>
</template>

<script>
import {getNotificationSettings, saveNotificationSettings} from '@/api/setting';
import BaseSwitch from '@/components/inputs/BaseSwitch.vue'
import BaseCheckbox from '@/components/inputs/BaseCheckbox.vue'
import Loading from '@/components/utilities/Loading.vue';
import BaseButton from '@/components/inputs/BaseButton.vue';

export default {
    components: { BaseSwitch, BaseCheckbox, Loading, BaseButton },
    mounted(){
        this.getNotificationSettings()
    },
    data() {
        return {
            loading_status : true,
            enable_notify: true,
            posts: [],
            postsSelected: [],
            systems: [],
            systemsSelected: [],
            follows: [],
            followsSelected: [],
            chats: [],
            chatsSelected: [],
            stories: [],
            storiesSelected: [],
            paidContents: [],
            paidContentsSelected: [],
            loading: false
        }
    },
    methods: {
        labelText(name){
            switch(name){
                case 'comment':
                    return this.$t('New Comment')
                case 'reply':
                    return this.$t('New Reply')
                case 'like':
                    return this.$t('New Like')
                case 'mention':
                    return this.$t('New Mention')
                case 'share':
                    return this.$t('New Share')
                case 'invite':
                    return this.$t('Notify when your invited users join')
                case 'new_follow':
                    return this.$t('New follower')
                case 'chat_request':
                    return this.$t('New chat request')
                case 'story_end':
                    return this.$t('When the story ends')
                case 'new_subscriber':
                    return this.$t('New subscriber');
                case 'new_ppv_purchase':
                    return this.$t('New pay per view purchase');
                default:
                    return null
            }
        },
        async getNotificationSettings() {
            try {
				const response = await getNotificationSettings()
                this.enable_notify = response.enable_notify;
                this.posts = response.post;
                this.systems = response.system;
                this.chats = response.chat;
                this.follows = response.follow;
                this.stories = response.story;
                this.paidContents = response.paid_content;
                this.postsSelected = window._.map(response.post, function(value, key) {
                    if (value) {
                        return key;
                    }
                });
                this.systemsSelected = window._.map(response.system, function(value, key) {
                    if (value) {
                        return key;
                    }
                });
                this.followsSelected = window._.map(response.follow, function(value, key) {
                    if (value) {
                        return key;
                    }
                });
                this.chatsSelected = window._.map(response.chat, function(value, key) {
                    if (value) {
                        return key;
                    }
                });
                this.storiesSelected = window._.map(response.story, function(value, key) {
                    if (value) {
                        return key;
                    }
                });
                this.paidContentsSelected = window._.map(response.paid_content, function(value, key) {
                    if (value) {
                        return key;
                    }
                });
                this.loading_status = false
                return response
			} catch (error) {
                this.loading_status = false
			}
        },
        async saveNotificationSetting() {
            if (this.loading) {
                return
            }
            
            this.loading = true
            try {
                var data = {
                    'enable_notify': this.enable_notify,
                }
                var postsSelected = this.postsSelected
                var systemsSelected = this.systemsSelected
                var chatsSelected = this.chatsSelected
                var followsSelected = this.followsSelected
                var storiesSelected = this.storiesSelected
                var paidContentsSelected = this.paidContentsSelected
                
                window._.forEach(this.posts, function(value, key)  {
                    data[key] = window._.indexOf(postsSelected, key) != -1;
                });

                window._.forEach(this.systems, function(value, key)  {
                    data[key] = window._.indexOf(systemsSelected, key) != -1;
                });

                window._.forEach(this.follows, function(value, key)  {
                    data[key] = window._.indexOf(followsSelected, key) != -1;
                });

                window._.forEach(this.chats, function(value, key)  {
                    data[key] = window._.indexOf(chatsSelected, key) != -1;
                });
                
                window._.forEach(this.stories, function(value, key)  {
                    data[key] = window._.indexOf(storiesSelected, key) != -1;
                });

                 window._.forEach(this.paidContents, function(value, key)  {
                    data[key] = window._.indexOf(paidContentsSelected, key) != -1;
                });

				await saveNotificationSettings(data)
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