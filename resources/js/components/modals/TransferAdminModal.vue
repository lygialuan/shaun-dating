<template>
    <div class="space-y-base-2">
        <div>
            <div class="mb-1"><label>{{ $t('Username') }}</label></div>
            <BaseSuggest v-model="selectedUser" optionLabel="name" :suggestions="userMentionList" @complete="suggestUsersMention" :emptySearchMessage="$t('No users found')">
                <template #option="{ option }">
                    <AutoSuggestUserItem :user="option"/>
                </template>
            </BaseSuggest>
        </div>
        <BaseButton @click="handleClickAddModerator()" fluid>{{$t('Transfer')}}</BaseButton>
    </div>
</template>
<script>
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseSuggest from '@/components/inputs/BaseSuggest.vue'
import AutoSuggestUserItem from '@/components/user/AutoSuggestUserItem.vue'
import PasswordModal from '@/components/modals/PasswordModal.vue'
import { getSuggestUsersForTransfer, transferPageOwner } from '@/api/page'
import { searchUsersForGroupAdmin, transferGroupOwner } from '@/api/group'
import { checkPopupBodyClass } from '@/utility/index'

export default {
    components: { BaseButton, BaseSuggest, AutoSuggestUserItem },
    data(){
        return{
            disableAutoComplete: false,
            userMentionList: null,
            selectedUser: null,
            fundAmount: null,
            onlySearchUser: true,
            notSearchMyself: true,
            subjectType: this.dialogRef.data?.subject_type,
            subject: this.dialogRef.data?.subject
        }
    },
    inject: ['dialogRef'],
    methods:{
        async suggestUsersMention(event) {
            try {
                let response;
                switch (this.subjectType) {
                    case 'group':
                        response = await searchUsersForGroupAdmin(this.subject.id, event.query);
                        break;
                    default:
                        response = await getSuggestUsersForTransfer(event.query);
                        break;
                }
                this.userMentionList = response;
			} catch (error) {
				this.userMentionList = [];
			}  
        },
        async handleClickAddModerator(){      
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
                                        response = await transferGroupOwner({
                                            id: this.subject.id,
                                            user_id: this.selectedUser[0].id,
                                            password: data.password
                                        })
                                        break;
                                    default:
                                        response = await transferPageOwner({
                                            id: this.selectedUser[0].id,
                                            password: data.password
                                        })
                                        break;
                                }
                                this.dialogRef.close({adminInfo: response})
                                passwordDialog.close()
                                checkPopupBodyClass();
                                this.showSuccess(this.$t('Transfer Admin Successfully.'))
                                switch (this.subjectType) {
                                    case 'group':
                                        this.$router.push({ name: 'group_profile', params: { id: this.subject.id, slug: this.subject.slug }})
                                        break;
                                    default:
                                        window.location.href = window.siteConfig.siteUrl
                                        break;
                                }
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