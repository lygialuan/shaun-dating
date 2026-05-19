<template>
    <div v-if="data.enable_title" class="widget-box-header">
        <h3 class="widget-box-header-title">{{ data.title }}</h3>
    </div>
    <SignupForm />
</template>

<script>
import SignupForm from '@/pages/signup/SignupForm.vue'

export default {
    components: { SignupForm },
    props: ['data', 'params', 'position']
}
</script>