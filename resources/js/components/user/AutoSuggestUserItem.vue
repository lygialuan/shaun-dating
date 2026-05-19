<template>
    <div class="flex items-center gap-base-2 text-main-color dark:text-white w-full">
        <img :src="user.avatar" class="w-8 h-8 rounded-full"/>
        <div class="flex-1 min-w-0">
            <div class="font-bold text-sm truncate">{{ user.name }}</div>
            <div class="text-xs text-sub-color dark:text-slate-400 truncate">{{mentionChar + user.user_name}}</div>
        </div>
    </div>
</template>

<script>
import Constant from '@/utility/constant'

export default {
    props: ['user'],
    data(){
        return{
            mentionChar: Constant.MENTION
        }
    }
}
</script>