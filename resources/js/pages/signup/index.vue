<template>
	<div class="flex justify-center w-full">
        <div class="w-full px-base-2">
            <div class="text-center mb-10">
                <Logo />
            </div>
            <SignupForm />
        </div>
    </div>
</template>

<script>
import SignupForm from '@/pages/signup/SignupForm.vue'
import Logo from '../../components/utilities/Logo.vue'
import localData from '../../utility/localData'
import { mapState } from 'pinia'
import { useAppStore } from '../../store/app'


export default {
    components: { SignupForm, Logo },
    created(){
        var ref_code = !window._.isNil(this.$route.query.ref_code) ? this.$route.query.ref_code : ''
        if (ref_code != '') {
            localData.set('ref_code', ref_code)
        }
    },
    computed: {
        ...mapState(useAppStore, ['config'])
    },
    mounted() {
        if (! this.config.signupEnable) {
            this.$router.push({
                name: 'home'
            })
        }
    }
}
</script>
