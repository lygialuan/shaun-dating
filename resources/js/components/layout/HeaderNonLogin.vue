<template>
	<header class="main-header-non-login sticky top-0 z-[998] bg-body dark:bg-dark-body">
		<Container class="px-4 py-5 lg:px-0">
			<div class="flex gap-x-4 justify-center items-center">
				<Logo :className="'max-h-8'"/>
			</div>
		</Container>
	</header>
</template>

<script>
import Container from '@/components/article/Container.vue';
import Logo from '../utilities/Logo.vue';
import { mapState } from 'pinia';
import { useAppStore } from '../../store/app';

export default {
	components: {
		Container, Logo
	},
	computed:{
		...mapState(useAppStore, ['config'])
	}
}
</script>
