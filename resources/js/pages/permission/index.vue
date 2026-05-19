<template>
    <div class="main-content-section p-10">
        <div class="text-center space-y-3">
            <div class="main-content-section-header justify-center">
                <div class="main-content-section-header-title">{{ $t("Sorry, this page isn't available.") }}</div>
            </div>
            <div>{{$t('You do not have permission to access this page.')}}</div>
            <BaseButton :to="{ name: 'home' }">{{$t('Go back to Home')}}</BaseButton>
        </div>
    </div>
</template>

<script>
import BaseButton from '@/components/inputs/BaseButton.vue'

export default {
    components: { BaseButton }
}
</script>