<template>
    <div class="px-base-2 md:px-0">
        <div v-if="data.enable_title" class="widget-box-header">
            <h3 class="widget-box-header-title">{{ data.title }}</h3>
        </div>
        <LoginForm />
    </div>
</template>

<script>
import LoginForm from '@/pages/login/LoginForm.vue'

export default {
    components: { LoginForm },
    props: ['data', 'params', 'position']
}
</script>