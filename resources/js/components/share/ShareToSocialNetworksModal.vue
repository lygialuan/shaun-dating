<template>
    <div class="flex gap-3 text-black text-center dark:text-white">
        <div class="flex-1">
            <a :href="'https://www.linkedin.com/sharing/share-offsite/?url='+subject.href" target="_blank" class="flex flex-col items-center text-main-color dark:text-white"> 
                <BaseIcon name="linkedin" size="48"/>
                <div class="mt-2">{{$t('Linkedin')}}</div>
            </a>
        </div>
        <div class="flex-1">
            <a :href="'https://www.facebook.com/share.php?u='+subject.href" target="_blank" class="flex flex-col items-center text-main-color dark:text-white">
                <BaseIcon name="facebook" size="48"/>
                <div class="mt-2">{{$t('Facebook')}}</div>
            </a>
        </div>
        <div class="flex-1">
            <a :href="'https://twitter.com/intent/tweet?url='+subject.href" target="_blank" class="flex flex-col items-center text-main-color dark:text-white">
                <BaseIcon name="twitter" size="48"/>
                <div class="mt-2">{{$t('Twitter')}}</div>
            </a>
        </div>
    </div>
</template>

<script>
import BaseIcon from '@/components/icons/BaseIcon.vue';

export default {
    components: { BaseIcon },
    inject: {
        dialogRef: {
            default: null
        }
    },
    data(){
        return{
            subject: this.dialogRef.data.subject
        }
    }
}
</script>