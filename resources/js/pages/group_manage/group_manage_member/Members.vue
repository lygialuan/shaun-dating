<template>
    <h3 class="text-base-lg font-extrabold mb-base-2">{{ $t('Members and pages') }}</h3>
    <BaseInputText v-model="searchMemberText" @input="handleSearchMembers" :placeholder="$t('Search members')" class="max-w-xs mb-base-2"/>
    <GroupMembersList :loading="loadingMembersList" :members-list="membersList" :has-load-more="loadmoreMembersList" @load-more="handleGetGroupMember(adminConfig.group.id, searchMemberText, currentMemberPage)">
        <template #actions="{ member }">
            <div class="flex items-center gap-base-2">
                <BaseButton size="xs" @click="handleRemoveMember(member.id)" type="danger">{{ $t('Remove') }}</BaseButton>
                <BaseButton size="xs" @click="handleBanMember(member.user.id)" type="warning">{{ $t('Ban') }}</BaseButton>
            </div>
        </template>
    </GroupMembersList>
</template>

<script>
import { getGroupMembers, storeBlockMember, removeGroupMember } from '@/api/group'
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import GroupMembersList from '@/components/lists/GroupMembersList.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'

export default {
    props: ['adminConfig'],
    components: { BaseInputText, GroupMembersList, BaseButton },
    data(){
        return{
            searchMemberText: '',
            currentMemberPage: 1,
            loadingMembersList: true,
            membersList: [],
            loadmoreMembersList: false
        }
    },
    mounted(){     
        this.handleGetGroupMember(this.adminConfig.group.id, this.searchMemberText, this.currentMemberPage)
    },
    methods: {
        async handleGetGroupMember(groupId, query, page){
            try {
                const response = await getGroupMembers(groupId, query, page)
                if (page == 1) {
                    this.membersList = [];
                }
                this.membersList = window._.concat(this.membersList, response.items)
                if(response.has_next_page){
                    this.loadmoreMembersList = true
                    this.currentMemberPage++;
                }else{
                    this.loadmoreMembersList = false
                }
            } catch (error) {
                this.showError(error.error)
            } finally {
                this.loadingMembersList = false
            }
        },
        debouncedGetGroupMembersList: window._.debounce(function() {
            this.handleGetGroupMember(this.adminConfig.group.id, this.searchMemberText, this.currentMemberPage);
        }, 500),
        handleSearchMembers(){
            this.currentMemberPage = 1
            this.debouncedGetGroupMembersList();
        },
        handleRemoveMember(memberId){
            this.$confirm.require({
                message: this.$t('Are you sure you want to remove this user from group?'),
                header: this.$t('Please confirm'),
                acceptLabel: this.$t('Ok'),
                rejectLabel: this.$t('Cancel'),
                accept: async () => {
                    try {
                        await removeGroupMember(memberId)
                        this.membersList = this.membersList.filter(member => member.id !== memberId)
                        this.$emit('updated');
                    } catch (error) {
                        this.showError(error.error)
                    }
                }
            }) 
        },
        handleBanMember(userId){
            this.$confirm.require({
                message: this.$t('Are you sure you want to ban and remove this user from group?'),
                header: this.$t('Please confirm'),
                acceptLabel: this.$t('Ok'),
                rejectLabel: this.$t('Cancel'),
                accept: async () => {
                    try {
                        await storeBlockMember(this.adminConfig.group.id, userId)
                        this.membersList = this.membersList.filter(member => member.user.id !== userId)
                        this.$emit('updated');
                    } catch (error) {
                        this.showError(error.error)
                    }
                }
            })
        }
    },
    emits: ['updated']
}
</script>