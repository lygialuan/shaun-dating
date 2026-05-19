<template>
    <div v-if="user" class="flex flex-col cursor-pointer">
        <div class="w-full aspect-[4/5] overflow-hidden rounded-[16px]">
            <img :src="display.avatar" class="w-full h-full object-cover bg-cover bg-center bg-no-repeat transition-transform duration-200 md:hover:scale-110"/>
        </div>
        <div v-if="display.showInfo" class="text-sm md:text-base truncate max-w-full">
            <UserName :user="user" :activePopover="false" class="text-sm !font-normal" :show-age="true"/>
        </div>
    </div>
</template>

<script>
import UserName from '@/components/user/UserName.vue'

export default {
    components: {
        UserName,
    },
    props: {
        user: Object,
        action: String,
        allowSeeProfilePrivate: Boolean,
    },
    computed: {
        display() {
            const isViewedMe = this.action === 'viewed_me'
            const isPrivate = this.user.can_prowse_profile_privately

            let avatar = this.user.avatar
            let showInfo = true

            if (isViewedMe && isPrivate && !this.allowSeeProfilePrivate) {
                avatar = this.user.blur_avatar
                showInfo = false
            }

            return { avatar, showInfo }
        }
    }
}
</script>