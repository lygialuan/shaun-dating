<template>
    <div class="relative overflow-hidden rounded-none md:rounded-t-base-lg pb-[38.5%]">
        <div class="absolute inset-0 h-full">
            <div class="bg-cover bg-center bg-no-repeat w-full h-full" :style="{ backgroundImage: `url(${user.cover})`}"></div>
        </div>
        <BaseUploadCover class="absolute top-base-2 end-base-2" />
    </div>
    <div class="text-center">
        <div class="relative inline-block p-base-1 -mt-20 bg-white rounded-full dark:bg-slate-800">
            <Avatar :user="user" :size="140" :activePopover="false" :border="false" :router="false" />
            <BaseUploadAvatar class="absolute top-0 end-1 md:top-2 md:end-2" />
        </div>
    </div>
</template>

<script>
import { mapState } from 'pinia'
import { useAuthStore } from '@/store/auth'
import BaseUploadCover from '@/components/inputs/BaseUploadCover.vue'
import BaseUploadAvatar from '@/components/inputs/BaseUploadAvatar.vue'
import Avatar from '@/components/user/Avatar.vue'

export default {
    components: {
        BaseUploadCover,
        BaseUploadAvatar,
        Avatar
    },
    computed: {
        ...mapState(useAuthStore, ['user'])
    },
    emits: ['update']
}
</script>