<template>
    <div class="flex gap-x-base-2">
        <div class="flex items-center justify-center w-10 h-10 bg-base-red rounded-full flex-shrink-0">
            <BaseIcon name="bell"/>
        </div>
        <div class="flex-1 space-y-base-2">
            <div class="font-semibold">{{ $t('Turn on notifications ') }}</div>
            <div class="text-sm">{{ $filters.textTranslate(this.$t('We will keep you inform about [siteName]'), { siteName: this.config.siteName }) }}</div>
            <div class="flex gap-2">
                <BaseButton @click="dialogRef.close()" type="secondary">{{ $t('Cancel') }}</BaseButton>
                <BaseButton @click="handleClickAllow">{{ $t('Allow') }}</BaseButton>
            </div>
        </div>
    </div>
</template>

<script>
import { mapState } from 'pinia';
import { useAppStore } from '@/store/app';
import BaseIcon from '@/components/icons/BaseIcon.vue';
import BaseButton from '@/components/inputs/BaseButton.vue';

export default {
    components: {
        BaseIcon,
        BaseButton
    },
    inject: ['dialogRef'],
    computed:{
        ...mapState(useAppStore, ['config'])
    },
    methods: {
        handleClickAllow(){
			this.dialogRef.close()
            if (Notification.permission === "denied") {
                alert(this.$t('Notifications are blocked. Please open your browser preferences or click the lock near address bar to change your notification preferences'))
            } else {
				Notification.requestPermission().then((permission) => {
					if (permission === "granted") {
                        this.saveFcmToken()
                    }
                });
            }
        }
    }
}
</script>