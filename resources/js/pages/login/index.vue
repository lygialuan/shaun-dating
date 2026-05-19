<template>
	<div class="flex justify-center w-full">
        <div class="w-full px-base-2">
            <div class="text-center mb-10">
                <Logo />
            </div>
            <LoginForm />
        </div>
    </div>
</template>

<script>
import LoginForm from '@/pages/login/LoginForm.vue'
import Logo from '../../components/utilities/Logo.vue'

export default {
    components: { LoginForm, Logo }
}
</script>
