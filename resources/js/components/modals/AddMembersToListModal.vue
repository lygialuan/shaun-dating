<template>
    <div>
        <div class="mb-base-2">
            <div class="mb-1"><label>{{ $t('Enter Name') }}</label></div>
            <BaseSuggest v-model="selectedUser" :suggestions="userMentionList" multiple optionLabel="name" @complete="suggestUsersMention" :emptySearchMessage="$t('No users found')">
                <template #option="{ option }">
                    <AutoSuggestUserItem :user="option"/>
                </template>
            </BaseSuggest>
        </div>
        <BaseButton @click="handleClickAddMemberToList()" fluid>{{$t('Add')}}</BaseButton>
    </div>
</template>
<script>
import { getMentionUserList } from '@/api/user'
import { storeMembersList } from '@/api/user_list'
import BaseButton from '@/components/inputs/BaseButton.vue'
import AutoSuggestUserItem from '@/components/user/AutoSuggestUserItem.vue'
import BaseSuggest from '@/components/inputs/BaseSuggest.vue'

export default {
    inject: ['dialogRef'],
    components: { BaseButton, AutoSuggestUserItem, BaseSuggest },
    data(){
        return{
            userMentionList: null,
            selectedUser: [],
            id: this.dialogRef.data?.id
        }
    },
    methods:{
        async suggestUsersMention(event) {
            try {
                this.userMentionList = await getMentionUserList(event.query, 1);
			} catch (error) {
				this.userMentionList = [];
			}  
        },
        async handleClickAddMemberToList(){   
            if(this.selectedUser.length === 0){
                this.showError(this.$t('Please select user to add.'));
                return;
            }
            try {
                const response = await storeMembersList({
                    id: this.id,
                    user_ids: this.selectedUser.map(member => member.id)
                })
                this.showSuccess(this.$t('Add Member Successfully.'))
                this.dialogRef.close({response: response})
            } catch (error) {
                this.showError(error.error)
            }
        }
    }
}
</script>