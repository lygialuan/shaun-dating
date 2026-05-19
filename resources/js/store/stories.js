import { defineStore } from 'pinia'
import { deleteStory, getConfig } from '../api/stories'

export const useStoriesStore = defineStore('stories', {
    // convert to a function
    state: () => ({
        deleteStoryItem: null,
        seenStoryItem: null,
        storyBackgrounds: [],
        selectedBackground: {
            id: 0,
            photo_url: null
        },
        enableStorySound: false,
        hasLayerModal: false
    }),
    actions: {
        doSetStoryBackgrounds(storyBackgrounds){
            this.storyBackgrounds = storyBackgrounds
            this.storyBackgrounds = window._.map(this.storyBackgrounds, function(element) { 
                return window._.extend({}, element, {is_selected: false});
            });
            if(this.selectedBackground.id != 0){
                this.storyBackgrounds.map(backgroundItem => {
                    if (backgroundItem.id === this.selectedBackground.id){
                        backgroundItem.is_selected = true
                    }else{
                        backgroundItem.is_selected = false
                    }
                    return backgroundItem;
                });
            }else{
                this.storyBackgrounds.map((background, index) => {
                    if (index == 0){
                        this.storyBackgrounds.map(backgroundItem => {
                            if (backgroundItem.id === background.id){
                                backgroundItem.is_selected = true
                                this.selectedBackground.id = background.id
                                this.selectedBackground.photo_url = background.photo_url
                            }else{
                                backgroundItem.is_selected = false
                            }
                            return backgroundItem;
                        });
                    }
                    return background;
                });
            }
        },
        doSelectBackground(background){
            if(background){
                this.storyBackgrounds.map(backgroundItem => {
                    if (backgroundItem.id === background.id){
                        backgroundItem.is_selected = true
                    }else{
                        backgroundItem.is_selected = false
                    }
                    return backgroundItem;
                });       
                this.selectedBackground.id = background.id
                this.selectedBackground.photo_url = background.photo_url
            } else {
                this.selectedBackground.id = 0
                this.selectedBackground.photo_url = null
            }
        },
        async doDeleteStoryItem(storyItem){
            try {
                await deleteStory({id: storyItem.id})
                this.deleteStoryItem = storyItem
            } catch (error) {
                console.log(error)
            }
        },
        setSeenStoryItem(seenStoryItem) {
            this.seenStoryItem = seenStoryItem
        },
        async getStoryBackgrounds(){
			try {
				const response = await getConfig()
                this.doSetStoryBackgrounds(response.backgrounds)
			} catch (error) {
				console.log(error)
			}
		},
        selectBackground(background){
            this.doSelectBackground(background)
        },
        setEnableStorySound(enable){
            this.enableStorySound = enable
        },
        setHasLayerModal(isHas){
            this.hasLayerModal = isHas
        }
    },
    persist: false
  })