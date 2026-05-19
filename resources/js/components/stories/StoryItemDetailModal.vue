<template>
	<StoryItemDetailSingle :storyItemId="storyItemId" @close-modal="closeModal"/>
    <CloseButton @click="closeModal()" />
</template>

<script>
import { mapState } from 'pinia'
import StoryItemDetailSingle from '@/components/stories/StoryItemDetailSingle.vue'
import { useStoriesStore } from '../../store/stories'
import CloseButton from '@/components/utilities/CloseButton.vue'

export default {
	components: { StoryItemDetailSingle, CloseButton },
	inject: ['dialogRef'],
	computed: {
        ...mapState(useStoriesStore, ['deleteStoryItem']),
	},
	watch: {
        '$route'(){
            this.dialogRef.close()
        },
        deleteStoryItem(){
            if(this.storyItemId == this.deleteStoryItem.id){
                this.closeModal()
            }
        }
    },
    data() {    
        return {
            storyItemId: this.dialogRef.data.storyItemId,
		}
	},
    methods: {
        closeModal() {
            this.dialogRef.close()
        }
    }
}
</script>