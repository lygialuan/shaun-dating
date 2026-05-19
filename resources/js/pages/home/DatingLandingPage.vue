<template>
	<div class="relative overflow-hidden">
		<Transition mode="out-in" enter-active-class="transition duration-400 ease-out" enter-from-class="translate-x-full opacity-0" enter-to-class="translate-x-0 opacity-100" leave-active-class="transition duration-300 ease-in" leave-from-class="translate-x-0 opacity-100" leave-to-class="-translate-x-full opacity-0">
			<div :key="step" class="flex flex-col items-center text-center gap-4 dark:text-white">
				<template v-if="step === 1">
					<img :src="config.dating.sign_up_photo_one" class="max-w-[80%] md:max-w-[50%] mb-6">
					<div class="flex flex-col gap-4 leading-[35px] md:leading-[56px]">
						<h1 class="text-[32px] md:text-[46px] font-bold">{{ $t('Swipe Right for Love!') }}</h1>
						<p class="text-[23px] dark:text-dark-text-base-gray">{{ $t('Your next great match is just a swipe away.') }}</p>
					</div>
				</template>
				<template v-else-if="step === 2">
					<img :src="config.dating.sign_up_photo_two" class="max-w-[80%] md:max-w-[50%] mb-6">
					<div class="flex flex-col gap-4 leading-[35px] md:leading-[56px]">
						<h1 class="text-[32px] md:text-[46px] font-bold">{{ $t('Chat, Flirt, Repeat!') }}</h1>
						<p class="text-[23px] dark:text-dark-text-base-gray">{{ $t('Break the ice with fun conversations and see where it leads.') }}</p>
					</div>
				</template>
				<template v-else-if="step === 3">
					<img :src="config.dating.sign_up_photo_three" class="max-w-[80%] md:max-w-[50%] mb-6">
					<div class="flex flex-col gap-4 leading-[35px] md:leading-[56px]">
						<h1 class="text-[32px] md:text-[46px] font-bold">{{ $t('Your Perfect Match is Out There') }}</h1>
						<p class="text-[23px] dark:text-dark-text-base-gray">{{ $t('Don’t wait for love to find you— take the first step today.') }}</p>
					</div>
				</template>
				<div class="flex flex-col space-y-4 w-full items-center">
					<router-link :to="{ name: 'signup' }" class="block w-[50%] md:w-[20%]">
						<BaseButton size="sm" class="w-full">{{ $t('Get Started') }}</BaseButton>
					</router-link>
					<router-link :to="{ name: 'login' }" class="block w-[50%] md:w-[20%]">
						<BaseButton size="sm" class="w-full dark:!bg-[#5A5A5A] dark:!border-[#5A5A5A] dark:!text-white">{{ $t('Login') }}</BaseButton>
					</router-link>
				</div>
			</div>
		</Transition>
		<div v-if="step < 3" class="fixed bottom-4 left-1/2 -translate-x-1/2 z-50">
			<BaseIcon name="arrow_circle_right" :size="44" class="animate-bounce cursor-pointer" @click="nextStep"/>
		</div>
	</div>
</template>



<script>
import { mapState } from 'pinia'
import { useAppStore } from '@/store/app';
import BaseIcon from '@/components/icons/BaseIcon.vue';
import BaseButton from '@/components/inputs/BaseButton.vue'

export default {
    components: { BaseIcon, BaseButton },
	props: ['data', 'params', 'position'],
	data(){
		return {
			step: 1
		}
	},
	computed: {
        ...mapState(useAppStore, ['config']),
    },
	methods: {
		nextStep(){
			this.step += 1;
		}
	}
}
</script>
