<template>
    <div>
        <p class="mb-base-2">{{ $t('Before you can transfer funds, it’s important that we verify your uniqueness. Click on the below button to start the verification process.') }}</p>
        <div class="text-end">
            <BaseButton :to="{name: 'verify_profile'}">{{ $t('Verify now') }}</BaseButton>
        </div>
    </div>
</template>
<script>
import BaseButton from '@/components/inputs/BaseButton.vue'

export default {
    components: { BaseButton },
}
</script>