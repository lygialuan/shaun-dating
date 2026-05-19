<template>
    <StoryDetail :storyId="storyId" @close-modal="closeModal" @set-error="closeModal" :storiesList="storiesList"/>
</template>

<script>
import StoryDetail from '@/components/stories/StoryDetail.vue'

export default {
	components: { StoryDetail },
	inject: ['dialogRef'],
    data() {    
        return {
            storyId: this.dialogRef.data.id,
			storiesList:  this.dialogRef.data.storiesList,
		}
	},
    watch: {
        '$route'(){
            this.dialogRef.close()
        }
    },
    methods: {
        closeModal() {
            this.dialogRef.close()
        }
    }
}
</script>