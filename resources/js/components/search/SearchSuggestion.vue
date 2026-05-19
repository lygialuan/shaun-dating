<template>
    <div class="search-suggestion-dropdown dropdown-menu-box max-h-96 overflow-y-auto absolute left-0 right-0 top-10 z-[9000] bg-white shadow-md rounded-base-lg dark:bg-slate-800 dark:shadow-slate-600">
        <div class="p-5" @click="$emit('close')">
            <div v-if="searchContent">
                <SearchResultItem v-if="searchResultsList.text"
                    :to="{ name: 'search', params: { search_type: 'text', type: 'post' }, query: { q: searchContent } }"
                    @click.native="$emit('save-history', searchContent)"
                >
                    <template #icon>
                        <div class="global-search-suggestion-box-icon flex items-center justify-center w-base-13 h-base-13 text-main-color dark:text-white"><BaseIcon name="search"/></div>
                    </template>
                    <template #main>
                        <div class="truncate"><strong>{{ searchResultsList['text'] }}</strong></div>
                    </template>
                </SearchResultItem>
                <SearchResultItem v-for="(item, i) in searchResultsList.hashtags" :key="i"
                    :to="{ name: 'search', params: { search_type: 'hashtag', type }, query: { q: item.name } }"
                    @click.native="$emit('save-history', item.name)"
                >
                    <template #icon>
                        <div class="global-search-suggestion-box-icon flex items-center justify-center w-base-13 h-base-13 text-main-color dark:text-white"><BaseIcon name="hashtag"/></div>
                    </template>
                    <template #main>
                        <div v-html="hashtagChar + matchingSearch(item.name)"></div>
                    </template>
                    <template #sub>
                        <div class="global-search-suggestion-box-sub text-xs text-sub-color dark:text-slate-400">{{ $filters.numberShortener(item.post_count, $t('[number] Post'), $t('[number] Posts')) }}</div>
                    </template>
                </SearchResultItem>
                <SearchResultItem v-for="(item, i) in searchResultsList.users" :key="i"
                    :to="{ name: 'profile', params: {user_name: item.user_name} }"
                    @click.native="$emit('save-history', item.name)"
                >
                    <template #icon>
                        <Avatar :user="item" :size="48" :activePopover="false"/>
                    </template>
                    <template #main>
                        <UserName :user="item" :activePopover="false"/>
                    </template>
                    <template #sub>
                        <div class="global-search-suggestion-box-sub flex items-center text-xs text-sub-color dark:text-slate-400">{{ mentionChar + item.user_name }}</div>
                    </template>
                </SearchResultItem>
                <SearchResultItem v-for="(item, i) in searchResultsList.pages" :key="i"
                    :to="{ name: 'profile', params: {user_name: item.user_name} }"
                    @click.native="$emit('save-history', item.name)"
                >
                    <template #icon>
                        <Avatar :user="item" :size="48" :activePopover="false" />
                    </template>
                    <template #main>
                        <UserName :user="item" :activePopover="false"/>
                    </template>
                    <template #sub>
                        <div v-if="item.show_follower" class="global-search-suggestion-box-sub flex items-center text-xs text-sub-color dark:text-slate-400">{{ $filters.numberShortener(item.follower_count, $t('[number] follower'), $t('[number] followers')) }}</div>
                    </template>
                </SearchResultItem>
                <SearchResultItem v-for="(item, i) in searchResultsList.groups" :key="i"
                    :to="{ name: 'group_profile', params: {id: item.id, slug: item.slug} }"
                    @click.native="$emit('save-history', item.name )"
                >
                    <template #icon>
                        <div class="w-12 h-12 rounded-full mx-auto max-w-full border border-divider dark:border-slate-700">
                            <img :src="item.cover" :alt="item.name" class="rounded-full w-full h-full object-cover">
                        </div>
                    </template>
                    <template #main>
                        <GroupName :group="item" :activePopover="false" />
                    </template>
                    <template #sub>
                        <div class="global-search-suggestion-box-sub flex items-center text-xs text-sub-color dark:text-slate-400">{{ $filters.numberShortener(item.member_count, $t('[number] member'), $t('[number] members')) }}</div>
                    </template>
                </SearchResultItem>
            </div>
            <div v-else>
                <SearchHistory :loading="loadingSearchHistories" :items="historySearchesList" @delete="id => $emit('delete-history', id)" />
            </div>
        </div>
    </div>
</template>

<script>
import Constant from '@/utility/constant'
import SearchResultItem from '@/components/search/SearchResultItem.vue'
import SearchHistory from '@/components/search/SearchHistory.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'
import GroupName from '@/components/group/GroupName.vue'

export default {
    components: { SearchResultItem, SearchHistory, BaseIcon, Avatar, UserName, GroupName },
    props: {
        searchContent: String,
        searchResultsList: Object,
        historySearchesList: Array,
        type: { type: String, default: 'post' },
        loadingSearchHistories: { type: Boolean, default: true }
    },
    data(){
        return {
            mentionChar: Constant.MENTION,
            hashtagChar: Constant.HASHTAG,
        }
    },
    methods: {
        matchingSearch(keyword){
			let words = this.searchContent
			keyword = window._.replace(window._.lowerCase(keyword), window._.lowerCase(words), '<strong>'+words+'</strong>')
			return keyword
		},
    }
}
</script>