<template>
	<div class="bg-white text-main-color rounded-base-lg p-base-2 dark:text-white dark:bg-slate-800 dark:border-white/10">
        <BaseInputText :placeholder="$t('Search Song')" left_icon="search" v-model="searchContent" @input="onEnterSearch()" class="mb-base-2" />
        <template v-if="isSearching">
            <Loading v-if="isLoadingSearch" />
            <template v-else>
                <div v-if="searchSongs.length > 0" class="max-h-48 overflow-auto pe-1">
                    <SongItem v-for="search_song in searchSongs" :key="search_song.id" :song="search_song" @select_song_item="selectSong(search_song)" @remove_song_item="removeSong" />
                </div>
                <div v-else class="text-center">{{$t('No songs found')}}</div>
            </template>
        </template>
        <template v-else>
            <div v-if="defaultSongs.length > 0" class="max-h-48 overflow-auto pe-1">
                <SongItem v-for="default_song in defaultSongs" :key="default_song.id" :song="default_song" @select_song_item="selectSong(default_song)" @remove_song_item="removeSong" />
            </div>
            <div v-else class="text-center">{{$t('No Songs')}}</div>
        </template>
    </div>
</template>

<script>
import { getConfig, getSearchSongs } from '@/api/vibb'
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
		this.getVibbConfig()
	},
    methods:{
        async getVibbConfig(){
			try {
				const response = await getConfig()
                this.defaultSongs = window._.map(response.songs, function(element) { 
                    return window._.extend({}, element, {is_selected: false});
                });
			} catch (error) {
				this.showError(error.error)
			}
		},
        async getSongs(keyword){
			try {
				const response = await getSearchSongs(keyword)
				this.searchSongs = window._.map(response, function(element) { 
                    return window._.extend({}, element, {is_selected: false});
                });
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
            this.searchSongs.map(searchSong => {
                searchSong.is_selected = false
                if(searchSong.id == song.id){
                    searchSong.is_selected = true
                }
                return searchSong
            })
            this.defaultSongs.map(searchSong => {
                searchSong.is_selected = false
                if(searchSong.id == song.id){
                    searchSong.is_selected = true
                }
                return searchSong
            })
            this.$emit('select_song', song)
        },
        removeSong(song){
            this.searchSongs.map(searchSong => {
                if(searchSong.id == song.id){
                    searchSong.is_selected = false
                }
                return searchSong
            })
            this.defaultSongs.map(searchSong => {
                if(searchSong.id == song.id){
                    searchSong.is_selected = false
                }
                return searchSong
            })
            this.$emit('remove_song')
        },
        reset(){
            this.searchSongs.forEach(searchSong => {
                searchSong.is_selected = false;
            });
            this.defaultSongs.forEach(defaultSong => {
                defaultSong.is_selected = false;
            });
        }
    },
    emits: ['select_song', 'remove_song']
}
</script>