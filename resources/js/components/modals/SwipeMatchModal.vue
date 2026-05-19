<template>
    <div class="flex flex-col text-center space-y-2">
        <div class="font-bold text-[26px]">{{ $t('Congratulation!') }}</div>
        <div class="font-bold text-[26px]">{{ $t("It's a match!") }}</div>
        <div class="relative h-[150px] flex items-center justify-center">
            <div class="absolute left-1/2 -translate-x-[85%] top-2 border-3 rounded-full dark:border-dark-border-color-icon">
                <Avatar :user="userInfo" :size="100" :activePopover="false" :hiddenOpen="true"/>
            </div>
            <div class="absolute left-1/2 translate-x-[-15%] top-2 border-3 rounded-full dark:border-dark-border-color-icon">
                <Avatar :user="user" :size="100" :activePopover="false" :hiddenOpen="true" />
            </div>
            <div class="absolute left-1/2 -translate-x-1/2 top-[50%] z-10">
                <div class="w-14 h-14 md:w-16 md:h-16 bg-dark-body border-3 rounded-full dark:border-dark-border-color-icon flex items-center justify-center">
                    <BaseIcon name="heart_yellow" size="26"/>
                </div>
            </div>
        </div>
        <div class="dark:text-dark-text-base-gray text-sm font-normal">{{ $t('Will you be the one to make the first move?') }}</div>
        <BaseButton @click="clickChat()">{{ $t('Start Conversation') }}</BaseButton>
        <BaseButton type="transparent" class="dark:!text-white" @click="cancel()" fluid>{{$t('Continue Browsing')}}</BaseButton>
    </div>
</template>

<script>
import BaseButton from '@/components/inputs/BaseButton.vue';
import Avatar from '@/components/user/Avatar.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'

export default {
	components: { 
       BaseButton,
       Avatar,
       BaseIcon
    },
    inject: {
        dialogRef: {
            default: null
        }
    },
    data() {
        return{
            roomId: this.dialogRef.data?.roomId,
            user: this.dialogRef.data?.user,
            userInfo: this.dialogRef.data?.userInfo,
        }
    },
    methods: {
        cancel(){
            this.dialogRef.close()
        },
        clickChat() {
			let permission = 'chat.allow'
			if(this.checkPermission(permission)){
                this.$router.push({name: 'chat', params: { 'room_id': this.roomId }});
			}
		},
    }
}
</script>