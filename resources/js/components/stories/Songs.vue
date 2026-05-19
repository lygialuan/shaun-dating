<template>
	<div class="bg-white text-main-color rounded-base-lg p-base-2 dark:text-white dark:bg-slate-800">
        <BaseInputText :placeholder="$t('Search Song')" left_icon="search" v-model="searchContent" @input="onEnterSearch()" class="mb-base-2" />
        <template v-if="isSearching">
            <Loading v-if="isLoadingSearch" />
            <template v-else>
                <div v-if="searchSongs.length > 0" class="max-h-48 overflow-auto pe-1">
                    <SongItem v-for="search_song in searchSongs" :key="search_song.id" :song="search_song" @select_song_item="selectSong(search_song)"/>
                </div>
                <div v-else class="text-center">{{$t('No songs found')}}</div>
            </template>
        </template>
        <template v-else>
            <div v-if="defaultSongs.length > 0" class="max-h-48 overflow-auto pe-1">
                <SongItem v-for="default_song in defaultSongs" :key="default_song.id" :song="default_song" @select_song_item="selectSong(default_song)"/>
            </div>
            <div v-else class="text-center">{{$t('No Songs')}}</div>
        </template>
    </div>
</template>

<script>
import { getConfig, getSearchSongs } from '@/api/stories'
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import SongItem from '@/components/stories/SongItem.vue'
import Loading from '@/components/utilities/Loading.vue'
var typingTimer = null

export default {
    components: { SongItem, Loading, BaseInputText },
	data(){
        return {
            defaultSongs: [],
            isSearching: false,
            isLoadingSearch: true,
            searchSongs: [],
            isShownSearchBox: false,
            searchContent: ''
        }
    },
    mounted(){
		this.getStoriesConfig()
	},
    methods:{
        async getStoriesConfig(){
			try {
				const response = await getConfig()
				this.defaultSongs = response.songs
			} catch (error) {
				this.showError(error.error)
			}
		},
        async getSongs(keyword){
			try {
				const response = await getSearchSongs(keyword)
				this.searchSongs = response
                this.isLoadingSearch = false
			} catch (error) {
				this.showError(error.error)
                this.isLoadingSearch = false
			}
		},
        onEnterSearch(){
			if(this.searchContent.trim() != ''){
				clearTimeout(typingTimer);
				var self = this
				typingTimer = setTimeout(function(){
                    if (self.searchContent.trim() != '') {
                        self.getSongs(self.searchContent)
                    }
                }, 600);
                this.isSearching = true
			}else{
				this.isSearching = false
			}
		},
        selectSong(song){
            this.$emit('select_song', song)
        }
    },
    emits: ['select_song']
}
</script>