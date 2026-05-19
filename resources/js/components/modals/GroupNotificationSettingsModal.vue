<template>
    <div class="flex flex-col gap-base-2">
        <div v-for="(value, key) in settings" :key="value.name" class="flex gap-4">
            <label class="flex-1">
                <div class="font-bold">{{ titleText(value.name) }}</div>
                <div class="text-sub-color text-base-xs dark:text-slate-400">{{ descriptionText(value.name) }}</div>
            </label>
            <BaseSwitch v-model="settings[key].value" @change="handleChangeNotificationSettings" />
        </div>
    </div>
</template>

<script>
import { mapActions } from 'pinia'
import { useGroupStore } from '@/store/group'
import { storeGroupNotificationSettings } from '@/api/group'
import BaseSwitch from '@/components/inputs/BaseSwitch.vue'

export default {
    components: { BaseSwitch }, 
    data(){
        return{
            settings: [],
            groupInfo: this.dialogRef.data.groupInfo
        }
    },
    inject: {
        dialogRef: {
            default: null
        }
    },
    mounted() {
        this.handleGetNotificationSettings()
    },
    methods: {
        ...mapActions(useGroupStore, ['handleGetGroupDetail']),
        titleText(name){
            switch(name){
                case 'request_join_notify':
                    return this.$t('Join Requests')
                case 'pending_post_notify':
                    return this.$t('Post Approval Requests')
                case 'pin_post':
                    return this.$t('A post is pinned')
                case 'post_new':
                    return this.$t('A post is created')
                default:
                    return null
            }
        },
        descriptionText(name){
            switch(name){
                case 'request_join_notify':
                    return this.$t('As an admin or owner of a group, you will receive a notification when someone sends your group a message.')
                case 'pending_post_notify':
                    return this.$t('As an admin or owner of the group, you will receive a notification when there is a new notification related to your group.')
                case 'pin_post':
                    return this.$t('As member of the group, you will receive a notification when admin or moderator of the group pins a post.')
                case 'post_new':
                    return this.$t('As member of the group, you will receive a notification when a new post is created.')
                default:
                    return null
            }
        },
        handleGetNotificationSettings(){
            this.settings = window._.map(this.groupInfo.notify_settings, function(value, key) {
                return {
                    'name': key,
                    'value': value
                };
            });
        },
        async handleChangeNotificationSettings(){
            try {
                let data = {
                    id: this.groupInfo.id
                }
                window._.forEach(this.settings, function(value)  {
                    data[value.name] = value.value
                });
                await storeGroupNotificationSettings(data)
                this.handleGetGroupDetail(this.groupInfo.id)
            } catch (error) {
                this.showError(error.error)
            }
        }
    }
}
</script>