<template>
    <div class="text-center">
        <div class="mb-base-2">{{ content }}</div>
        <BaseButton :to="{name: 'contact'}">{{$t('Contact admin')}}</BaseButton>
    </div>
</template>
<script>
import BaseButton from '@/components/inputs/BaseButton.vue';
export default {
    components: { BaseButton },
    inject: {
        dialogRef: {
            default: null
        }
    },
    data(){
        return{
            content: this.dialogRef.data.content
        }
    },
}
</script>