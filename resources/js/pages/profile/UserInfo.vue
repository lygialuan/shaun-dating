<template>
    <div class="main-content-section">
        <div class="flex gap-4 mb-base-2">
            <div class="flex-150px">{{$t('Name')}}</div>
            <div class="break-word">{{userInfo.name}}</div>
        </div>
        <div class="flex gap-4 mb-base-2">
            <div class="flex-150px">{{$t('Username')}}</div>
            <div class="break-word">{{getUserName}}</div>
        </div>
        <div class="flex gap-4 mb-base-2">
            <div class="flex-150px">{{$t('Joined')}}</div>
            <div class="break-word">{{ userInfo.joined_at }}</div>
        </div>
        <div v-if="userInfo.birthday" class="flex gap-4 mb-base-2">
            <div class="flex-150px">{{$t('Birthday')}}</div>
            <div>{{userInfo.birthday}}</div>
        </div>
        <div v-if="userInfo.gender" class="flex gap-4 mb-base-2">
            <div class="flex-150px">{{$t('Gender')}}</div>
            <div>{{userInfo.gender}}</div>
        </div>
        <div v-if="userInfo.phone_number" class="flex gap-4 mb-base-2">
            <div class="flex-150px">{{$t('Phone')}}</div>
            <div>{{userInfo.phone_number}}</div>
        </div>
        <div v-if="userInfo.address_full" class="flex gap-4 mb-base-2">
            <div class="flex-150px">{{$t('Location')}}</div>
            <div>{{userInfo.address_full}}</div>
        </div>
        <div v-if="userInfo.about" class="flex gap-4 mb-base-2">
            <div class="flex-150px">{{$t('About')}}</div>
            <div><ContentHtml :content="userInfo.about" /></div>
        </div>
        <div v-if="userInfo.links.length" class="flex gap-4 mb-base-2">
            <div class="flex-150px">{{$t('Links')}}</div>
            <div class="flex flex-col items-start gap-2">
                <a v-for="(link, index) in userInfo.links" :key="index" target="_blank" :href="link.link" class="group inline-flex items-center gap-base-1 text-main-color dark:text-white">
                    <span class="block w-6 h-6 flex-shrink-0 bg-main-color dark:bg-white" :style="iconStyle(link.icon)"></span>
                    <span class="group-hover:underline break-word truncate-3">{{ link.title || link.link }}</span>
                </a>
            </div>
        </div>
    </div>
</template>

<script>
import ContentHtml from '@/components/utilities/ContentHtml.vue'
import Constant from '@/utility/constant'

export default {
    components: { ContentHtml },
    props: ['userInfo'],
    computed: {
        getUserName(){
            return Constant.MENTION + this.userInfo.user_name;
        }
    },
    methods: {
        iconStyle(icon) {
            return {
                mask: `url(${icon}) center center / contain no-repeat`,
                WebkitMask: `url(${icon}) center center / contain no-repeat`,
            };
        }
    },
    emits: ['change_tab', 'update_user_info']
}
</script>