<template>
    <Error v-if="error">{{error}}</Error>
    <h1 class="page-title mb-base-2">
        <div v-if="search_type === 'hashtag'">
            <Loading v-if="loading_query"/>
            <div v-else class="flex flex-wrap items-center gap-x-3 gap-y-base-2">
                <span class="break-word">{{hashtagChar + query}}</span>
                <BaseButton v-if="queryInfo && authenticated" @click="toggleFollowHashtag()">
                    {{ queryInfo.is_followed ? $t('Unfollow') : $t('Follow') }}
                </BaseButton>
            </div>
        </div>
        <div v-else class="truncate-3">{{$t('Search') + ': ' + query}}</div>
    </h1>
    <div>
        <TabsMenu :menus="searchMenus" @select="changeTab" type="secondary" class="mb-base-2" />
        <Component :is="searchTypeComponent" :search_type="search_type" :type="currentTab" :query="query"></Component>
    </div>
</template>
<script>
import { mapState } from 'pinia';
import { changeUrl } from '@/utility';
import { toggleFollowHashtag } from '@/api/follow';
import { getHashtag } from '@/api/hashtag'
import { useAuthStore } from '@/store/auth';
import { useAppStore } from '@/store/app'
import PostsSearch from '@/pages/search/PostsSearch.vue'
import UsersSearch from '@/pages/search/UsersSearch.vue'
import PageSearch from '@/pages/search/PageSearch.vue'
import GroupSearch from '@/pages/search/GroupSearch.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue';
import Constant from '@/utility/constant';
import BaseButton from '@/components/inputs/BaseButton.vue';
import Error from '@/components/utilities/Error.vue';
import Loading from '@/components/utilities/Loading.vue';
import TabsMenu from '@/components/menu/TabsMenu.vue'

export default {
    props: ["search_type", "type"],
    components: { BaseIcon, BaseButton, Error, Loading, TabsMenu },
    data(){
        return{
            currentTab: this.type,
            hashtagChar: Constant.HASHTAG,
            loading_query: this.search_type === 'hashtag' ? true : false,
            query: null,
            queryInfo: null,
            error: null
        }
    },
    created(){
        this.query = !window._.isNil(this.$route.query.q) ? this.$route.query.q : ''
        if(this.search_type === 'hashtag'){
            this.getHashtagInfo(this.query)
        }
    },
    watch: {
        '$route'() {
            this.query = !window._.isNil(this.$route.query.q) ? this.$route.query.q : ''
            if(this.search_type === 'hashtag' && this.query){
                this.getHashtagInfo(this.query)
            }
        }
    },
    computed: {
        ...mapState(useAuthStore, ['authenticated']),
        ...mapState(useAppStore, ['config']),
        searchTypeComponent() {
            if (this.query != '') {
                switch(this.currentTab){
                    case 'user':
                        return UsersSearch;
                    case 'page':
                        return PageSearch;
                    case 'group':
                        return GroupSearch;
                    default: 
                        return PostsSearch;
                }
            }
            return null;
		},
        searchMenus(){
			return [
				{ icon: 'feeds', name: this.$t('Post'), isShow: true, isActive: this.currentTab === 'post', tab: 'post'},
				{ icon: 'user', name: this.$t('People'), isShow: true, isActive: this.currentTab === 'user', tab: 'user'},
				{ icon: 'briefcase', name: this.$t('Page'), isShow: this.config.user_page.enable, isActive: this.currentTab === 'page', tab: 'page'},
                { icon: 'users', name: this.$t('Group'), isShow: this.config.group.enable, isActive: this.currentTab === 'group', tab: 'group'}
			]
		}
    },
    methods: {
        changeTab(name) {
			this.currentTab = name
			let searchUrl = this.$router.resolve({
				name: 'search',
                params: {'search_type': this.search_type, 'type': this.currentTab},
                query: {'q': this.query}
			});
			changeUrl(searchUrl.fullPath)
		},
        async getHashtagInfo(hashtagName){
            try {
                const response = await getHashtag(hashtagName)
                this.queryInfo = response
                this.loading_query = false
            } catch (error) {
                this.loading_query = false
            }
        },
        async toggleFollowHashtag(){
            try {
                await toggleFollowHashtag({name: this.queryInfo.name, action: this.queryInfo.is_followed ? 'unfollow' : 'follow'});
                this.queryInfo.is_followed = !this.queryInfo.is_followed;
            }
            catch (error) {
                this.showError(error.error)
            }
        },
    }
}
</script>