<template>
    <UsersBoxList :usersList="selectedUsers" :router="false" @remove_user="removeFromSelectedList" class="mb-base-2" />
    <BaseInputText v-model="searchText" @input="handleSearchUser()" :placeholder="$t('Search')" :autofocus="true" />
    <div class="pt-base-2" :class="selectedUsers.length ? 'pb-32' : 'pb-12'">
        <template v-if="usersList.length">
            <div v-for="userItem in usersList" :key="userItem.id" class="users-list-item flex items-center gap-base-2 px-base-2 py-base-1 rounded-md hover:bg-light-web-wash dark:hover:bg-dark-web-wash">
                <label :for="userItem.id" class="flex-1 flex items-center gap-base-2 min-w-0">
                    <Avatar :user="userItem" :router="false" :activePopover="false"/>
                    <div class="flex-1 min-w-0">
                        <UserName :user="userItem" :router="false" :activePopover="false" class="list_items_title_text_color" />
                        <p class="list_items_sub_text_color text-xs text-sub-color mb-1 dark:text-slate-400 truncate">{{mentionChar + userItem.user_name}}</p>
                    </div>
                </label>
                <BaseCheckbox v-model="selectedUsers" :value="userItem" :inputId="userItem.id" :disabled="!canSelect(userItem.id)" />
            </div>
        </template>
        <div v-else class="text-center">{{ searchText != '' ? $t('No users found') : '' }}</div>
    </div>
    <div class="p-dialog-footer-fixed absolute left-0 bottom-0 right-0 px-6 pt-base-2 pb-6 rounded-b-base-lg bg-white dark:bg-dark-form-base">
        <BaseTextarea v-if="selectedUsers.length" v-model="messageContent" class="mb-base-2" rows="2" :placeholder="$t('Message')" />
        <BaseButton @click="handleShareStory" :disabled="selectedUsers.length === 0" fluid>{{ selectedUsers.length > 1 ? $t('Send separately') : $t('Send') }}</BaseButton>
    </div>
</template>
<script>
import { mapState } from 'pinia'
import { getMentionUserList } from '@/api/user'
import { shareStories } from '@/api/stories'
import { useAppStore } from '@/store/app'
import UsersBoxList from '@/components/lists/UsersBoxList.vue';
import BaseButton from '@/components/inputs/BaseButton.vue';
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'
import Constant from '@/utility/constant'
import BaseCheckbox from '@/components/inputs/BaseCheckbox.vue'
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import BaseTextarea from '@/components/inputs/BaseTextarea.vue'

export default{
    components: { UsersBoxList, BaseButton, Avatar, UserName, BaseCheckbox, BaseInputText, BaseTextarea },
    inject: {
        dialogRef: {
            default: null
        }
    },
    data(){
        return{
            mentionChar: Constant.MENTION,
            searchText: '',
            usersList: [],
            selectedUsers: [],
            messageContent: ''
        }
    },
    computed: {
		...mapState(useAppStore, ['config'])
	},
    methods: {
        debouncedGetMentionedUsersList: window._.debounce(function(text) {
            if(text){
                this.getMentionedUsersList(text);
            } else {
                this.usersList = []
            }
        }, 500),
        handleSearchUser(){
            this.debouncedGetMentionedUsersList(this.searchText);
        },
        async getMentionedUsersList(text){
			try {
				const response = await getMentionUserList(text, 1);
				this.usersList = response;
			} catch (error) {
				this.showError(error.error)
			}
			
		},
        async handleShareStory(){
            try {
                await shareStories({
                    id: this.dialogRef.data.storyId,
                    user_ids: this.selectedUsers.map(user => user.id),
                    content: this.messageContent
                })
                this.dialogRef.close()
                this.showSuccess(this.$t('Shared Successfully.'))
            } catch (error) {
                this.showError(error.error)
            }
        },
        removeFromSelectedList(user){
            this.selectedUsers = this.selectedUsers.filter(userItem => userItem.id !== user.id)
        },
        canSelect(userId){
            return this.selectedUsers.map(user => user.id).includes(userId) || this.selectedUsers.length < this.config.story.shareUserMax
        }
    } 
}
</script>