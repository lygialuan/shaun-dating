<template>
    <ProfileImageInfo :user="user" :owner="owner"/>
    <div class="flex space-x-2" v-if="owner && !user.is_verify && config.userVerifyEnable" >
        <BaseButton class="w-[49%] mt-4 dark:!bg-[#2C2C2C] dark:!border-[#2C2C2C] dark:!text-white font-normal" @click="clickVerify">{{ $t('Verify Profile') }}</BaseButton>
        <BaseButton class="w-[49%] mt-4 dark:!bg-[#2C2C2C] dark:!border-[#2C2C2C] dark:!text-white font-normal" @click="shareUser(user.user_name)">{{ $t('Share My Profile') }}</BaseButton>
    </div>
    <BaseButton v-else-if="owner" @click="shareUser(user.user_name)" class="w-full mt-4 dark:!bg-[#2C2C2C] dark:!border-[#2C2C2C] dark:!text-white font-normal">{{ $t('Share My Profile') }}</BaseButton>
    <ProfileAboutMe :user="user" :owner="owner"/>
    <ProfileWorkAndEducation :user="user" :owner="owner"/>
    <ProfileMoreAboutMe :user="user" :owner="owner"/>
    <ProfileInterest :user="user" :owner="owner"/>
    <ProfileSocialLink :user="user" :owner="owner"/>
    <div v-if="!owner" class="h-[70px] shrink-0"></div>
</template>

<script>
import { mapState } from 'pinia'
import { useAppStore } from '@/store/app'
import ProfileImageInfo from '@/components/user/ProfileImageInfo.vue'
import ProfileAboutMe from '@/components/user/ProfileAboutMe.vue'
import ProfileWorkAndEducation from '@/components/user/ProfileWorkAndEducation.vue'
import ProfileMoreAboutMe from '@/components/user/ProfileMoreAboutMe.vue'
import ProfileInterest from '@/components/user/ProfileInterest.vue'
import ProfileSocialLink from '@/components/user/ProfileSocialLink.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'

export default {
    components: {
        ProfileImageInfo,
        ProfileAboutMe,
        ProfileWorkAndEducation,
        ProfileMoreAboutMe,
        ProfileInterest,
        ProfileSocialLink,
        BaseButton
    },
    inject: {
        dialogRef: { default: null }
    },
    props: {
        user: {
            type: Object,
            required: true
        },
        owner: {
            type: Boolean,
            default: false
        }
    },
    computed: {
        ...mapState(useAppStore, ['config']),
    },
    methods: {
        clickVerify() {
            document.body.style.overflow = '';
            let permission = 'user_verify.send_request'
			if(this.checkPermission(permission)){
                if (this.$route.name === 'verify_profile') {
                    this.dialogRef.close()
                    return
                }
				this.$router.push({ name: 'verify_profile'})
			}
        },
        async shareUser(username) {
            const url = `${window.siteConfig.siteUrl}/@${username}`
            await navigator.clipboard.writeText(url)
            this.showSuccess(this.$t('Profile link copied!'))
        },
    }
}
</script>

<style>
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
</style>
