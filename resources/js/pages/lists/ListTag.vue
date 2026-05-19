<template>
	<h2 class="text-xl font-bold mb-4">{{ $t('Tags') }}</h2>
	<HashtagsList :loading="loadingHashtags" :hashtags-list="followingHashtags" :hasLoadMore="loadmoreStatus" @load-more="getFollowingHashtags(page)" />
</template>

<script>
import { getMyHashtags } from '@/api/follow';
import HashtagsList from '@/components/lists/HashtagsList.vue';

export default {
    components: { HashtagsList},
	data(){
		return {
			error: null,
			loadmoreStatus: false,
			loadingHashtags: true,
            followingHashtags: [],
			page: 1
        };
	},
	mounted(){
		this.getFollowingHashtags(this.page)
	},
	methods:{
		async getFollowingHashtags(page){
			try {
				const response = await getMyHashtags(page)
				if(page === 1){
                    this.followingHashtags = response.items
					this.followingHashtags = window._.map(this.followingHashtags, function(element) { 
						return window._.extend({}, element, {is_followed: true});
					});
                }else{
                    this.followingHashtags = window._.concat(this.followingHashtags, response.items);
					this.followingHashtags = window._.map(this.followingHashtags, function(element) { 
						return window._.extend({}, element, {is_followed: true});
					});
                }
                if(response.has_next_page){
                    this.loadmoreStatus = true
                    this.page++;
                }else{
                    this.loadmoreStatus = false
                }
			} catch (error) {
				this.showError(error.error)
			} finally {
				this.loadingHashtags = false
			}
		}
	}
}
</script>