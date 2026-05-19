<template>
    <div class="main-content-section-header px-base-2 md:px-0">
        <h3 class="main-content-section-header-title">{{ $t('Explore') }}</h3>
    </div>
    <TabsMenu :menus="exploreMenus" @select="changeTab" class="mb-base-2" />
    <component :is="exploreComponent" />
</template>

<script>
import { changeUrl } from '@/utility'
import TabsMenu from '@/components/menu/TabsMenu.vue'
import Trending from '@/pages/discovery/DiscoveryFeeds.vue'
import Media from '@/pages/media/MediaFeeds.vue'
import Watch from '@/pages/watch/WatchFeeds.vue'
import Documents from '@/pages/documents/DocumentFeeds.vue'

export default {
    props: ['data', 'params', 'position'],
    components: {
        TabsMenu
    },
    data(){
        return{
            tab: this.params.tab ? this.params.tab : ''
        }
    },
    computed: {
        exploreMenus(){
			return [
				{ name: this.$t('Trending'), isActive: this.tab === '', tab: '' },
                { name: this.$t('Media'), isActive: this.tab === 'media', tab: 'media' },
                { name: this.$t('Watch'), isActive: this.tab === 'watch', tab: 'watch' },
                { name: this.$t('Documents'), isActive: this.tab === 'documents', tab: 'documents' }
			]
		},
        exploreComponent(){
            switch (this.tab) {
                case 'media':
                    return Media
                case 'watch':
                    return Watch
                case 'documents':
                    return Documents
                default:
                    return Trending
            }
        }
    },
    methods: {
        changeTab(name) {
            this.tab = name
            let exploreUrl = this.$router.resolve({
				name: 'explore'
			});
			changeUrl(exploreUrl.fullPath + (name != '' ? '/' + name : ''))
		}
    }
}
</script>