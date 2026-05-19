<template>
    <div class="flex flex-col sm:flex-row flex-wrap gap-x-5 gap-y-2"> 
        <div class="flex-1">{{ $t('Allow notifications') }}</div>
        <div class="flex-2">
            <BaseSwitch v-model="allowNotifications" @click="toggleAllowNotifications" readonly /> 
        </div>  
    </div>
</template>

<script>
import localData from '@/utility/localData';
import BaseSwitch from '@/components/inputs/BaseSwitch.vue'
import { removeFcmToken } from '@/api/utility';

export default {
    components: {
        BaseSwitch
    },
    data(){
        return {
            allowNotifications: (Notification.permission === 'granted' && !localData.get('pwa_disable_notifications')) || false
        }
    },
    methods: {
        toggleAllowNotifications(){
            if(Notification.permission === 'denied'){
                alert(this.$t('Notifications are blocked. Please open your browser preferences or click the lock near address bar to change your notification preferences'))
                return;
            }
            this.allowNotifications = !this.allowNotifications
            localData.set('pwa_disable_notifications', !this.allowNotifications)
            if(Notification.permission === 'granted'){
                if(!localData.get('pwa_disable_notifications')){
                    this.saveFcmToken()
                } else {
                    removeFcmToken({
                        token: localData.get('fcm_token')
                    })
                }
            } else {
                Notification.requestPermission().then((permission) => {
					if (permission === "granted") {
                        this.saveFcmToken()
                    } else {
                        this.allowNotifications = false
                        localData.set('pwa_disable_notifications', true)
                    }
                });
            }
        }
    }
}

</script>