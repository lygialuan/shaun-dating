<template>
    <SkeletonSwitchPages v-if="loadingPages"/>
    <div v-else class="mb-base-2">
        <button @click="handleOpenProfile(user)" class="flex items-center gap-base-2 mb-base-2 text-inherit">
            <Avatar :user="user" :active-popover="false" :hiddenOpen="true"/>
            <div class="flex-1 font-semibold">{{ user.name }}</div>
        </button>
        <div class="w-full h-[1px] bg-divider dark:bg-white/10 my-base-2"></div>
        <div v-if="pagesList.length" class="space-y-base-2">
            <div v-for="page in pagesList" :key="page.id" class="flex items-center gap-base-2">
                <Avatar @click="handleOpenProfile(page)" :user="page" :active-popover="false" :hiddenOpen="true"/>
                <div @click="handleOpenProfile(page)" class="flex-1 font-semibold cursor-pointer">{{ page.name }}</div>
                <BaseButton @click="handleClickSwitchPage(page)" size="xs">{{ $t('Switch') }}</BaseButton>
            </div>
            <button v-if="loadmorePages" @click="handleGetPageSwitches(page)" class="font-semibold">{{ $t('See more') }}</button>
        </div>
        <div v-else>{{ $t('Sorry, there is no profile yet! Please create new profile.') }}</div>
    </div>
    <BaseButton v-if="!this.user.is_page && user.permissions['user_page.allow_create']" @click="createPage" class="mt-base-2" fluid>{{ $t('Create new profile') }}</BaseButton>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { getPageSwitches, switchPage } from '@/api/page'
import { useAuthStore } from '@/store/auth'
import { useUtilitiesStore } from '@/store/utilities'
import SkeletonSwitchPages from '@/components/skeletons/SkeletonSwitchPages.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import Avatar from '@/components/user/Avatar.vue'

export default {
    components: { SkeletonSwitchPages, BaseButton, Avatar },
    data() {
		return {
            loadingPages: true,
            pagesList: [],
            loadmorePages: false,
			page: 1
		}
	},
    inject: ['dialogRef'],
    computed: {
        ...mapState(useAuthStore, ['user']),
    },
    mounted(){
        this.handleGetPageSwitches(this.page)
    },
    methods: {
        ...mapActions(useUtilitiesStore, ['setSelectedPage']),
        async handleGetPageSwitches(page){
            try {
                const response = await getPageSwitches(page)
                if(page === 1){
                    this.pagesList = []
                }
                this.pagesList = [...this.pagesList, ...response.items].filter(page => page.id != this.user.id);
                if(response.has_next_page){
                    this.loadmorePages = true
                    this.page++;
                }else{
                    this.loadmorePages = false
                }
            } catch (error) {
                this.showError(error.error)
            } finally {
                this.loadingPages = false
            }
        },
        handleClickSwitchPage(page){
            this.setSelectedPage(page)
            setTimeout(async() => {
                try {  
                    await switchPage(page.id)
                    window.location.href = window.siteConfig.siteUrl
                } catch (error) {
                    this.showError(error.error)
                    this.setSelectedPage(null)
                    this.dialogRef.close()
                }
            }, 1500);
		},
        createPage() {
            if (this.user) {
				let permission = 'user_page.allow_create'
                if(this.checkPermission(permission)){
                    this.$router.push({ 'name': 'user_pages_create' })
                }
			}
        },
        handleOpenProfile(user) {
            if (document.querySelector('.p-dialog-profile')) return
            this.openProfile({ user, hiddenSwipe: true })
        }
    }
}
</script>