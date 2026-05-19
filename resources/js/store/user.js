import { defineStore } from 'pinia'
import { getAllUsers } from '@/api/user'
import { swipe } from '@/api/dating'
import { nextTick } from 'vue'

export const useUserStore = defineStore('user', {
    state: () => ({
        users: [],
        page: 1,
        loading: false,
        hasNextPage: true,
        filterParams: {
            age: {
                min: 18,
                max: 28,
            },
            attributeValues: [],
            interestAttributeValues: [],
            gender: 0,
            location: [],
            verifiedProfiles: [],
            isAdvancedFilter: false,
        },
        activeResetUsers: false,
    }),
    actions: {
        async loadUsers() {
            if (this.loading) return
            this.loading = true
            try {
                const res = await getAllUsers({
                    page: this.page,
                    ...this.filterParams
                })
                const newUsers = res.items.filter(u => !this.users.some(c => c.id === u.id))
                this.users.push(...newUsers)
                this.hasNextPage = res.has_next_page
                this.page++ 
                nextTick(() => {
                    if (newUsers.length === 0 && this.hasNextPage) {
                        this.loadUsers()
                        return
                    }

                    const scrollable = document.documentElement.scrollHeight > window.innerHeight
                    if (!scrollable && this.hasNextPage) {
                        this.loadUsers()
                    }
                })
            } catch (err) {
                console.error(err)
            } finally {
                this.loading = false
                this.activeResetUsers = false
            }
        },
        addUser(user){
            this.users.push(user)
        },
        resetUsers() {
            this.users = []
            this.page = 1
            this.hasNextPage = true
            this.activeResetUsers = true
        },
        setPage(page){
            this.page = page + 1
        },
        updateFilters(newFilter) {
            this.filterParams = newFilter;
        },
        sortUser(user) {
            if (user && user.id) {
                this.users = [
                    user,
                    ...this.users.filter(u => u.id !== user.id)
                ]
            } else {
                this.users = [...this.users].sort((a, b) => b.id - a.id)
            }
        },
        removeUser(user) {
            if (!user?.id) return
            this.users = this.users.filter(u => u.id !== user.id)
        },
        setLocationFromUser(user) {
            if (!user?.address_full || !user?.dating_addresses_id) return
            if (this.filterParams.location.length > 0) return

            this.filterParams.location = [{
                id: user.dating_addresses_id,
                name: user.address_full
            }]
        },
        async handleSwipe(action) {
            const target = this.users[0]
            if (!target) return null
            try {
                const res = await swipe({ target_user_id: target.id, action })
                if (this.users.length <= 2 && this.hasNextPage) {
                    this.loadUsers()
                }
                return { roomId: res, target }
            } catch (e) {
                return e
            }
        }
    }
})
