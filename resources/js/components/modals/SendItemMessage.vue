<template>
    <div class="space-y-base-2">
        <div>
            <div class="mb-1"><label>{{ $t('List name') }}</label></div>
            <BaseSelect 
                v-model="selectedList" 
                :options="messageConfig" 
                optionLabel="name" 
                :optionValue="(option) => option"
                filter resetFilterOnHide
                @filter="handleFilterListsList"
                @show="getMessageConfig"
            />
            <div v-if="selectedList?.type !== 'new_list'" class="flex items-center justify-between gap-base-2 mt-2">
                <div v-if="selectedList?.member_count" class="inline-flex gap-2 items-center bg-web-wash px-2 py-1 rounded-md dark:bg-dark-web-wash">
                    <BaseIcon name="users" size="20"/>
                    <div>{{ $filters.numberShortener(selectedList.member_count, $t('[number] member selected'), $t('[number] members selected')) }}</div>
                </div>
                <div v-else></div>
                <router-link :to="{name: 'list_lists'}">{{ $t('Manage Lists') }}</router-link>
            </div>
        </div>
        <template v-if="['new_list', 'specific'].includes(selectedList?.type)">
            <div v-if="selectedList?.type === 'new_list'">
                <div class="mb-1"><label>{{ $t('Custom List Name') }}</label></div>
                <BaseInputText v-model="customListName" />
            </div>
            <div>
                <div class="mb-1"><label>{{ $t('Search user to add') }}</label></div>
                <BaseSuggest v-model="selectedUsers" :suggestions="userMentionList" multiple optionLabel="name" @complete="suggestUsersMention" :emptySearchMessage="$t('No users found')">
                    <template #option="{ option }">
                        <AutoSuggestUserItem :user="option"/>
                    </template>
                </BaseSuggest>
            </div>
        </template>
        <div>
            <div class="mb-1"><label>{{ $t('Enter message') }}</label></div>
            <BaseTextarea v-model="message" :rows="3" />
        </div>
        <BaseButton :loading="loadingSend" @click="handleSendMessage" fluid>{{$t('Send')}}</BaseButton>
    </div>
</template>

<script>
import { getSendMessageConfig, sendMessage, searchForSend } from '@/api/user_list'
import { getMentionUserList } from '@/api/user'
import debounce from 'lodash/debounce';
import BaseIcon from '@/components/icons/BaseIcon.vue';
import BaseSelect from '@/components/inputs/BaseSelect.vue'
import BaseTextarea from '@/components/inputs/BaseTextarea.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import AutoSuggestUserItem from '@/components/user/AutoSuggestUserItem.vue'
import BaseSuggest from '@/components/inputs/BaseSuggest.vue'
import BaseInputText from '@/components/inputs/BaseInputText.vue'

export default {
    components: {
        BaseIcon,
        BaseSelect,
        BaseTextarea,
        BaseButton,
        AutoSuggestUserItem,
        BaseSuggest,
        BaseInputText
    },
    inject: ['dialogRef'],
    data(){
        return{
            messageConfig: null,
            userMentionList: [],
            selectedList: null,
            message: null,
            loadingSend: false,
            type: this.dialogRef.data.type,
            subject: this.dialogRef.data.subject,
            customListName: null,
            selectedUsers: []
        }
    },
    methods:{
        async getMessageConfig(){
            try {
                const response = await getSendMessageConfig()
                this.messageConfig = [
                    ...response,
                    { type: 'new_list', name: this.$t('Custom list') },
                    { type: 'specific', name: this.$t('Specific members') }
                ]
            } catch (error) {
                this.showError(error.error)
            }
        },
        async searchForSend(query){
            try {
                this.messageConfig = await searchForSend(query)
            } catch (error) {
                this.showError(error.error)
            }
        },
        async suggestUsersMention(event) {
            try {
                this.userMentionList = await getMentionUserList(event.query, 1);
			} catch (error) {
				this.userMentionList = [];
			}  
        },
        handleFilterListsList: debounce(async function(event) {
            if (!event.value) {
                await this.getMessageConfig();
                return;
            }
            try {
                this.searchForSend(event.value)
            } catch (error) {
                this.showError(error.error)
            }
        }, 400),
        async handleSendMessage(){
            this.loadingSend = true
            try {
                await sendMessage({
                    type: this.selectedList?.type,
                    list_id: this.selectedList?.id,
                    name: this.customListName,
                    user_ids: this.selectedUsers.map(user => user.id),
                    content: this.message,
                    subject_type: this.type,
                    subject_id: this.subject.id
                })
                this.dialogRef.close()
                this.showSuccess(this.$t('Message has been sent.'))
            } catch (error) {
                this.showError(error.error)
            } finally {
                this.loadingSend = false
            }
        }
    }
}
</script>