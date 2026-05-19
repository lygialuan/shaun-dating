<template>
	<div class="flex gap-4 items-center justify-between mb-2 last:mb-0">
		<span class="flex gap-base-2 items-center justify-between w-full">
			<button class="flex gap-base-2 items-center">
				<BaseIcon name="pause_fill" size="20" :id="'pause'+song.id" class="pauseBtn bg-primary-color p-base-1 text-white rounded-full dark:bg-dark-primary-color" :style="{ display: 'none' }" @click="pauseSong(song.id)" />
				<BaseIcon name="play_fill" size="20" :id="'play'+song.id" class="playBtn bg-primary-color p-base-1 text-white rounded-full dark:bg-dark-primary-color" @click="playSong(song.id)" />
			</button>
			<div class="flex-1 truncate-3">{{ song.name }}</div>
			<BaseButton @click="song.is_selected ? removeSongItem(song) : selectSongItem(song)" size="xs">{{ song.is_selected ? $t('Remove') : $t('Select') }}</BaseButton>
		</span>
		<audio :src="song.file_url" loop :id="'audio'+song.id" class="audio"></audio>
	</div>
</template>

<script>
import BaseIcon from "@/components/icons/BaseIcon.vue"
import BaseButton from "@/components/inputs/BaseButton.vue"

export default {
	components: { BaseIcon, BaseButton },
	props: ['song'],
	methods: {
		playSong(songId) {
			const audio = document.getElementById('audio'+songId)
			document.querySelectorAll('audio').forEach(el => {
				el.pause()
				el.currentTime = 0
			})
			audio.play()
			document.querySelectorAll('.playBtn').forEach(el => el.style.display = 'block')
			document.querySelectorAll('.pauseBtn').forEach(el => el.style.display = 'none')
			document.getElementById('play'+songId).style.display = 'none'
			document.getElementById('pause'+songId).style.display = 'block'
		},
		pauseSong(songId) {
			const audio = document.getElementById('audio'+songId)
			audio.pause()
			audio.currentTime = 0
			document.getElementById('play'+songId).style.display = 'block'
			document.getElementById('pause'+songId).style.display = 'none'
		},
		selectSongItem(song){
            this.$emit('select_song_item', song)
        },
		removeSongItem(song){
            this.$emit('remove_song_item', song)
        }
	},
	emits: ['select_song_item', 'remove_song_item']
}
</script>