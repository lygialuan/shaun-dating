<template>
	<div class="h-full">
		<div class="flex h-full">
			<div class="flex-1 bg-cover lg:rounded-base-lg bg-center relative" :style="{'backgroundImage': 'url('+selectedBackground.photo_url+')'}">			
				<span class="absolute top-3 left-3 right-3 z-10">			
					<div class="flex gap-2 justify-between items-center">
						<div class="flex gap-2">
							<button class="flex justify-center items-center bg-white shadow-md h-8 w-8 rounded-full dark:bg-slate-800 rtl:rotate-180" @click="closeCreateStoryModal()">
								<BaseIcon name="arrow_left" size="20" />                  
							</button>						
							<button class="flex justify-center items-center bg-white shadow-md h-8 w-8 rounded-full dark:bg-slate-800" @click="toggleSongsList()">
								<BaseIcon name="music_notes" size="20" />                  
							</button>
							<button class="flex justify-center items-center bg-white shadow-md h-8 w-8 rounded-full dark:bg-slate-800" @click="toggleAddTextarea()">
								<BaseIcon name="character" size="20" />                  
							</button>									
						</div>
						<BaseButton :loading="loading" @click="postStory()">{{ $t('Post Story') }}</BaseButton>
					</div>
					<span v-if="selected_song" class="bg-black text-white px-2 py-1 text-xs rounded-lg inline-flex items-center gap-1 relative mt-4 z-10">
						<BaseIcon name="music_note" size="16" />
						{{ selected_song.name }}
						<button @click="removeSong" class="absolute -top-2 -end-2 bg-red-600 text-white rounded-full">
							<BaseIcon name="close" size="16" />
						</button>
					</span>
				</span>
				<div
					v-if="showAddTextarea"
					class="absolute top-14 left-3 right-3 z-10"
					v-click-outside="{
						handler: toggleAddTextarea,
						middleware: ignoreMentionDropdown
					}"
					>
					<div class="relative leading-none">
						<Mentionable ref="mentionableRef" v-model="content" :placeholder="$t('Enter Text')" maxRows="5" maxlength="1024" autofocus>
							<div class="text-end mt-1">
								<BaseColorPicker v-model="color" />
							</div>
						</Mentionable>	
					</div>
				</div>
				<Songs v-if="showSongsList" class="absolute top-14 left-3 right-3 z-10" v-click-outside="toggleSongsList" @select_song="selectSong"/>
				<button v-if="storyBackgrounds.length > 0" class="absolute bottom-4 start-3 z-10" @click="toggleBackgroundsList()">
					<BaseIcon name="background" />
				</button>
				<Backgrounds v-if="showBackgroundsList" class="absolute bottom-3 left-3 right-3 z-20" v-click-outside="toggleBackgroundsList"/>
				<div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-center text-main-color text-xl max-w-[80%] w-full max-h-[60%] overflow-x-hidden overflow-y-auto story-content-box">
					<ContentHtml v-if="content" :style="{ color: color}" :content="content" :limit="100" />
					<span v-else class="text-sub-color dark:text-slate-300">{{$t('Enter Text')}}</span>
				</div>
			</div>			
		</div>
    </div>
	<CloseButton @click="closeCreateStoryModal()" />
</template>

<script>
import { mapActions, mapState } from 'pinia'
import { storeStory } from '@/api/stories'
import { useStoriesStore } from '@/store/stories'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import BaseButton from "@/components/inputs/BaseButton.vue"
import BaseColorPicker from '@/components/inputs/BaseColorPicker.vue'
import Backgrounds from '@/components/stories/Backgrounds.vue'
import Songs from '@/components/stories/Songs.vue'
import ContentHtml from '@/components/utilities/ContentHtml.vue'
import CloseButton from '@/components/utilities/CloseButton.vue'
import constant from '@/utility/constant'
import Mentionable from "@/components/utilities/Mentionable.vue"

export default {
	components: { BaseIcon, BaseButton, BaseColorPicker, Backgrounds, Songs, ContentHtml, CloseButton, Mentionable },
	inject: ['dialogRef'],
	data(){
		return{		
			type: 'text',
			content: null,
			color: '#ffffff',
			photo: null,
			selected_song: null,
			showSongsList: false,
			showBackgroundsList: false,
			showAddTextarea: true,
			loading: false
		}
	},
	mounted(){
		this.getStoryBackgrounds()
	},
	unmounted() {
		this.selectBackground(null)
	},
	computed:{
		...mapState(useStoriesStore, ['storyBackgrounds', 'selectedBackground'])
	},
	watch: {
        '$route'(){
            this.dialogRef.close()
        }
    },
	methods: {
		...mapActions(useStoriesStore, ['getStoryBackgrounds', 'selectBackground']),
		toggleSongsList(){
			this.showSongsList = !this.showSongsList;
		},
		toggleAddTextarea(){
			this.showAddTextarea = !this.showAddTextarea;
		},
		selectSong(song){
			this.selected_song = song
			this.toggleSongsList()
		},
		removeSong(){
			this.selected_song = null
		},
		toggleBackgroundsList(){
			this.showBackgroundsList = !this.showBackgroundsList;
		},
		async postStory(){
			if (this.loading) {
				return
			}
			this.loading = true
			try {
				await storeStory({
					type: this.type,
					content: this.content,
					content_color: this.color,
					song_id: this.selected_song ? this.selected_song.id : 0,
					background_id: this.selectedBackground.id,
					photo: this.photo
				})
				this.showSuccess(this.$t('Your story has been posted.'))
				this.closeCreateStoryModal()
				this.$router.push({'name' : 'home'})
			} catch (error) {
				if (error.error.code == constant.RESPONSE_CODE_MEMBERSHIP_PERMISSION) {
					this.showPermissionPopup('story', error.error.message);
				} else {
					this.showError(error.error)
				}
			} finally {
				this.loading = false
			}
		},
		closeCreateStoryModal(){
            this.dialogRef.close()
        },
		ignoreMentionDropdown(event) {
			const mentionable = this.$refs.mentionableRef

			if (!mentionable || !mentionable.$refs || !mentionable.$refs.dropdownMention) return true

			return !mentionable.$refs.dropdownMention.contains(event.target)
		}
	}
}
</script>