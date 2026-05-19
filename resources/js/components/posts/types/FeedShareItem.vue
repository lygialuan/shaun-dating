<template>
    <Component v-if="shareItemType" :is="shareItemType" :item="subject" />
</template>

<script>
import Groups from '../share_item_types/Groups.vue';

export default {
    props: {
        post: {
            type: Object,
            default: null
        },
        parentPost: {
            type: Object,
            default: null
        }
    },
    computed:{
        subject(){
            return this.post.items[0].subject
        },
        shareItemType(){
            switch(this.post.items[0].subject_type) {
                case 'groups':
                    return Groups
            }  
            return null
        }
    }
}
</script>