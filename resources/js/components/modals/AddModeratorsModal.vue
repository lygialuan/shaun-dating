<template>
    <div>
        <div class="mb-base-2">
            <div class="mb-1"><label>{{ $t('Username') }}</label></div>
            <BaseSuggest v-model="selectedUser" :suggestions="userMentionList" optionLabel="name" @complete="suggestUsersMention" :emptySearchMessage="$t('No users found')">
                <template #option="{ option }">
                    <AutoSuggestUserItem :user="option"/>
                </template>
            </BaseSuggest>
        </div>
        <BaseButton @click="handleClickAddModerator()" fluid>{{$t('Add')}}</BaseButton>
    </div>
</template>
<script>
import BaseButton from '@/components/inputs/BaseButton.vue'
import AutoSuggestUserItem from '@/components/user/AutoSuggestUserItem.vue'
import PasswordModal from '@/components/modals/PasswordModal.vue'
import { searchUsersForPageAdmin, storeAdminPage } from '@/api/page'
import { searchUsersForGroupAdmin, storeGroupAdmin } from '@/api/group'
import { checkPopupBodyClass } from '@/utility/index'
import BaseSuggest from '@/components/inputs/BaseSuggest.vue'

export default {
    components: { BaseButton, AutoSuggestUserItem, BaseSuggest },
    data(){
        return{
            userMentionList: null,
            selectedUser: [],
            fundAmount: null,
            subjectType: this.dialogRef.data?.subject_type,
            subjectId: this.dialogRef.data?.subject_id
        }
    },
    inject: ['dialogRef'],
    methods:{
        async suggestUsersMention(event) {
            try {
                let response;
                switch (this.subjectType) {
                    case 'group':
                        response = await searchUsersForGroupAdmin(this.subjectId, event.query);
                        break;
                    default:
                        response = await searchUsersForPageAdmin(event.query);
                        break;
                }
                this.userMentionList = response;
			} catch (error) {
				this.userMentionList = [];
			}  
        },
        async handleClickAddModerator(){   
            if(this.selectedUser.length === 0){
                this.showError(this.$t('Please select user to add moderator.'));
                return;
            }   
            const passwordDialog = this.$dialog.open(PasswordModal, {
				props: {
					header: this.$t('Enter Password'),
					class: 'password-modal',
					modal: true,
					dismissableMask: true,
					draggable: false
				},
                emits: {
					onConfirm: async (data) => {            
						if (data.password) {
							try {
                                let response;
                                switch (this.subjectType) {
                                    case 'group':
                                        response = await storeGroupAdmin({
                                            id: this.subjectId,
                                            user_id: this.selectedUser[0].id,
                                            password: data.password
                                        });
                                        break;
                                    default:
                                        response = await storeAdminPage({
                                            id: this.selectedUser[0].id,
                                            password: data.password
                                        });
                                        break;
                                }
                                this.showSuccess(this.$t('Add Moderator Successfully.'))
                                passwordDialog.close()
                                checkPopupBodyClass();
                                this.dialogRef.close({adminInfo: response})
                            } catch (error) {
                                this.showError(error.error)
                                passwordDialog.close()
                                checkPopupBodyClass();
                            }
						}
					}
				}
			})
        }
    }
}
</script>