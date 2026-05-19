<template>
    <div class="text-center">
        <div class="mb-base-2">{{ content }}</div>
        <BaseButton autofocus @click="handleContact">{{$t('Contact')}}</BaseButton>
    </div>
</template>

<script>
import BaseButton from '@/components/inputs/BaseButton.vue'

export default {
    components: {
        BaseButton
    },
    inject: {
        dialogRef: {
            default: null
        }
    },
    data(){
        return{
            content: this.dialogRef.data.content,
            userName: this.dialogRef.data.userName
        }
    },
    methods:{
        handleContact(){
            this.$router.push({ name: 'profile',  params: {user_name: this.userName} })
            this.dialogRef.close()
        }
    }
}
</script>