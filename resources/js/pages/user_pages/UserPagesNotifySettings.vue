<template>
    <div class="main-content-section">
        <h3 class="text-main-color text-base-lg font-extrabold dark:text-white mb-base-2">{{ $t('Notification Settings') }}</h3>
        <div class="flex gap-base-2 mb-base-2">
            <div class="flex-1">
                <div class="font-bold">{{ $t('New page chat message') }}</div>
                <div>{{ $t('As an admin or owner of a page, you will receive a notification when someone sends your page a message.') }}</div>
            </div>
            <BaseSwitch v-model="messageNotify" @change="saveNotifySettings()"/>
        </div>
        <div class="flex gap-base-2 mb-base-2">
            <div class="flex-1">
                <div class="font-bold">{{ $t('New page notification') }}</div>
                <div>{{ $t('As an admin or owner of the page, you will receive a notification when there is a new notification related to your page.') }}</div>
            </div>
            <BaseSwitch v-model="pageNotify" @change="saveNotifySettings()"/>
        </div>
    </div>
</template>

<script>
import { getNotifySettingsPage, storeNotifySettingsPage } from '@/api/page'
import BaseSwitch from '@/components/inputs/BaseSwitch.vue'
import { useAppStore } from '@/store/app'
import { useAuthStore } from '@/store/auth'
import { mapState, mapActions } from 'pinia'

export default {
    components: { BaseSwitch },
    data(){
        return{
            pageNotify: null,
            messageNotify: null
        }
    },
    mounted(){
        if (! this.user.is_page) {
            this.setErrorLayout(true)
        } else {
            this.getNotifySettings()
        }
    },
    computed: {
		...mapState(useAuthStore, ['user']),
    },
    methods:{
        ...mapActions(useAppStore, ['setErrorLayout']),
        async getNotifySettings(){
            try {
                const response = await getNotifySettingsPage()
                this.pageNotify = response.page_notify
                this.messageNotify = response.message_notify
            } catch (error) {
                this.showError(error.error)
            }
        },
        async saveNotifySettings(){
            try {
                await storeNotifySettingsPage({
                    message_notify: this.messageNotify,
                    page_notify: this.pageNotify
                })
            } catch (error) {
                this.showError(error.error)
            }
        }
    }
}
</script>