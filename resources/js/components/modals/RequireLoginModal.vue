<template>
    <div class="text-center">
        <div class="mb-base-2">{{$t('Please login to continue')}}</div>
        <BaseButton autofocus :to="{ name: 'login' }">{{$t('Login')}}</BaseButton>
    </div>
</template>
<script>
import BaseButton from '@/components/inputs/BaseButton.vue';
export default {
    components: { BaseButton }
}
</script>