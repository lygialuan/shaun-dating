<template>
	<CardUserDating :users="users" :loading="loading"/>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { useUserStore } from '@/store/user'
import CardUserDating from '@/components/matched/CardUserDating.vue'

export default {
	components: { 
		CardUserDating
	},
	mounted() {
		document.body.style.overflow = '';
		this.handleLoadUsers()
		window.addEventListener('scroll', this.onScroll, { passive: true })
	},
	beforeUnmount() {
		window.removeEventListener('scroll', this.onScroll)
	},
	computed: {
		...mapState(useUserStore, ['users', 'loading', 'page', 'hasNextPage'])
	},
	methods: {
		...mapActions(useUserStore, ['loadUsers', 'setPage', 'resetUsers']),
		onScroll() {
			const nearBottom = window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 150
			if (nearBottom && this.hasNextPage && !this.loading) {
				this.loadUsers()
			}
		},
		handleLoadUsers() {
			this.resetUsers()
			this.loadUsers()
		},
	},
}
</script>
