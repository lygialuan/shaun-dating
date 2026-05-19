<template>
    <div class="-m-5">
        <VerifyPage />
    </div>
</template>

<script>
import VerifyPage from '@/pages/verify_profile/index.vue'

export default {
    components: {
        VerifyPage
    },
    emits: ['update']
}
</script>