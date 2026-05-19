<template>
    <div v-if="hashtagsList.length > 0" class="boxes-list flex flex-wrap gap-1">
        <div v-for="hashtagListItem in hashtagsList" :key="hashtagListItem.name" class="boxes-list-item text-sub-color inline-flex items-center rounded-base px-base-2 py-2 border border-secondary-box-color dark:text-slate-400 dark:border-white/30">
            <router-link :to="{name: 'search', params: {search_type: 'hashtag', type: 'post'}, query: {q: hashtagListItem.name}}" :target="target" class="font-bold text-xs me-2 text-inherit">{{hashtagListItem.name}}</router-link>
            <button @click="unFollowHashtag(hashtagListItem.name)" class="leading-none">
                <BaseIcon name="close" size="16" />
            </button>
        </div>  
    </div>
</template>
<script>
import { toggleFollowHashtag } from '@/api/follow';
import BaseIcon from '@/components/icons/BaseIcon.vue';

export default {
	components:{ BaseIcon },
    props: ['hashtagsList', 'target'],
	methods: {
        async unFollowHashtag(hashtagName){
            try {
                await toggleFollowHashtag({name: hashtagName, action: 'unfollow'});
                this.hashtagsList.map(hashtagItem => {
                    if (hashtagItem.name === hashtagName) {
                        hashtagItem.is_followed = false
                    }
                    return hashtagItem
                });
                this.$emit('unfollow_signup_hashtag', hashtagName)
            }
            catch (error) {
                this.showError(error.error)
            }
        }
	},
    emits: ['unfollow_signup_hashtag']
}
</script>