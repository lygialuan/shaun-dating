<template>
    <CardUserDating :users="users" :loading="loading" :isPageExplore="false" :action="action" hidden-swipe="true"/>
</template>

<script>
import { getUserActions } from '@/api/dating'
import CardUserDating from '@/components/matched/CardUserDating.vue'

export default {
	components: { 
		CardUserDating
	},
    props: {
        action: {
            type: String,
            default: 'liked_me'
        },
    },
    data() {
        return {
            users: [],
            loading: false,
            hasNextPage: false,
            page: 1
        }
    },
	mounted() {
		this.handleGetUsers()
		window.addEventListener('scroll', this.onScroll, { passive: true })
	},
	beforeUnmount() {
		window.removeEventListener('scroll', this.onScroll)
	},
	methods: {
		onScroll() {
			const nearBottom = window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 150
			if (nearBottom && this.hasNextPage && !this.loading) {
				this.handleGetUsers()
			}
		},
        async handleGetUsers() {
            if (this.loading) return
            this.loading = true
            try {
                const res = await getUserActions(this.page, this.action)
                this.users.push(...res.items.map(item => item.user))
            } catch (err) {
                console.error(err)
            } finally {
                this.loading = false
            }
        },
	},
}
</script>
