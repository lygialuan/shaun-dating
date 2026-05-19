<template>
	<div class="main-content-section">	
		<!-- Select Tags Section -->
		<div v-if="currentStep === 1" class="space-y-3">
			<div class="main-content-section-header">
				<div class="main-content-section-header-title">{{ $t('Select Tags to Follow') }}</div>
			</div>
			<HashtagsBoxList :hashtagsList="followingHashtags" target="_blank" @unfollow_signup_hashtag="removeFollowingHashtags"/>
			<BaseInputText v-model="hashtagKeyword" @input="findHashtag()" :placeholder="$t('Enter topics')" left_icon="pencil" />
			<HashtagsList :loading="loading_suggest_hashtags" :hashtagsList="suggestHashtags" target="_blank" @follow_hashtag="followHashtag" @unfollow_hashtag="unFollowHashtag"/>
		</div>
		<!-- End Select Tags Section -->

		<!-- Select Users Section -->
		<div v-if="currentStep === 2" class="space-y-3">
			<div class="main-content-section-header">
				<div class="main-content-section-header-title">{{ $t('Select Users to Follow') }}</div>
			</div>
			<UsersBoxList :usersList="followingUsersList" target="_blank" @remove_user="removeFromFollowingUsers" />
			<BaseInputText v-model="userKeyword" @input="findUser()" :placeholder="$t('Enter name')" left_icon="pencil" />
			<UsersList :loading="loading_suggest_users" :usersList="suggestUserLists" target="_blank" @follow_user="followUser" @unfollow_user="unFollowUser" :showPopover="false">
				<template #sub_text="{ item }">
					<p v-if="item.show_follower" class="users-list-item-content-sub list_items_sub_text_color flex items-center text-xs text-sub-color dark:text-slate-400"><BaseIcon name="users" size="16" class="me-1"/>{{ $filters.numberShortener(item.follower_count, $t('[number] follower'), $t('[number] followers')) }}</p> 
				</template>
			</UsersList>
		</div>
		<!-- End Select Users Section -->

		<div class="mt-3">
			<BaseButton @click="nextStep()" fluid>{{$t('Continue')}}</BaseButton>
		</div>
	</div>
</template>
<script>
import { mapState } from 'pinia';
import { getSuggestSignupHashtags } from '@/api/hashtag'
import { getSuggestUsers, toggleFollowUser } from '@/api/follow';
import BaseIcon from '@/components/icons/BaseIcon.vue';
import HashtagsList from '@/components/lists/HashtagsList.vue';
import UsersList from '@/components/lists/UsersList.vue';
import HashtagsBoxList from '@/components/lists/HashtagsBoxList.vue';
import UsersBoxList from '@/components/lists/UsersBoxList.vue';
import BaseButton from '@/components/inputs/BaseButton.vue';
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import { useAuthStore } from '../../store/auth';
var typingTimer = null;
const authStore = useAuthStore()

export default {
	components:{ BaseIcon, BaseButton, HashtagsList, HashtagsBoxList, UsersList, UsersBoxList, BaseInputText },
	computed: {
		...mapState(useAuthStore, ['user'])
	},
	data(){
		return{
			hashtagKeyword: '',
			userKeyword: '',
			loading_suggest_hashtags: true,
            followingHashtags: [],
			suggestHashtags: null,
			loading_suggest_users: true,
            suggestUserLists: null,
			followingUsersList: [],
			currentStep: 1
		}
	},
	mounted(){
		this.getSuggestHashtagsList('')
		this.getSuggestUsersList('')
		authStore.loginFirst()
    },
	methods: {
		findHashtag(){
			clearTimeout(typingTimer);
			typingTimer = setTimeout(() => 
				this.getSuggestHashtagsList(this.hashtagKeyword)
			, 400);
		},
		findUser(){
			clearTimeout(typingTimer);
			typingTimer = setTimeout(() => 
				this.getSuggestUsersList(this.userKeyword)
			, 400);
		},
		async getSuggestHashtagsList(keyword){
			try {
				const response = await getSuggestSignupHashtags(keyword)
				this.suggestHashtags = response
			} catch (error) {
				this.showError(error.error)
			} finally {
				this.loading_suggest_hashtags = false
			}
		},
		async getSuggestUsersList(keyword){
            try {
				const response = await getSuggestUsers(keyword)
                this.suggestUserLists = response
			} catch (error) {
                this.showError(error.error)
			} finally {
				this.loading_suggest_users = false
			}
        },
		removeFollowingHashtags(hashtagName){
			this.followingHashtags = this.followingHashtags.filter(followingHashtagItem => followingHashtagItem.name !== hashtagName)
			this.suggestHashtags.map(suggestHashtagItem => {
				if (suggestHashtagItem.name === hashtagName) {
					suggestHashtagItem.is_followed = false
				}
				return suggestHashtagItem
			});
		},
		followHashtag(hashtagName){
			this.followingHashtags.push({name: hashtagName})
		},
		unFollowHashtag(hashtagName){
			this.followingHashtags = this.followingHashtags.filter(followingHashtagItem => followingHashtagItem.name !== hashtagName)
		},
		async removeFromFollowingUsers(user){
			try {
				await toggleFollowUser({
					id: user.id,
					action: "unfollow",
					name: user.name,
					avatar: user.avatar
				});
				this.followingUsersList = this.followingUsersList.filter(followingUsersItem => followingUsersItem.id !== user.id)
				this.suggestUserLists.map(suggestUserItem => {
					if (suggestUserItem.id === user.id) {
						suggestUserItem.is_followed = false
					}
					return suggestUserItem
				});
			} catch (error) {
				this.showError(error.error)
			}
		},
		followUser(user){
			this.followingUsersList.push({
				id: user.id,
				name: user.name,
				user_name: user.user_name,
				avatar: user.avatar
			})
		},
		unFollowUser(user){
			this.followingUsersList = this.followingUsersList.filter(followingUsersItem => followingUsersItem.id !== user.id)
		},
		nextStep(){
			if(this.currentStep === 2){
				authStore.me()
				this.$router.push({ name: "home" });
			}else{
				this.currentStep++;
			}
		}
	}
}
</script>