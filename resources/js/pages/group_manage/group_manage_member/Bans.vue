<template>
    <h3 class="text-base-lg font-extrabold mb-1">{{ $t('Banned') }}</h3>
    <p class="text-base-xs text-sub-color dark:text-slate-400 mb-base-2">{{ $t("People who are banned can't find the group in search, see any of the content within the group or receive new invitations to the group.") }}</p>
    <BaseInputText v-model="searchBlockedMemberText" @input="handleSearchBlockedMembers" :placeholder="$t('Search banned members')" class="max-w-xs mb-base-2"/>
    <GroupMembersList :loading="loadingBlockedMembersList" :members-list="blockedMembersList" :has-load-more="loadmoreBlockedMembersList" @load-more="handleGetGroupMember(adminConfig.group.id, searchBlockedMemberText, currentBlockedMemberPage)">
        <template #actions="{ member }">
            <div class="flex items-center gap-base-2">
                <BaseButton size="xs" @click="handleRemoveBlockedMember(member.id)">{{ $t('Unban') }}</BaseButton>
            </div>
        </template>
        <template #empty>{{ $t('No blocked members') }}</template>
    </GroupMembersList>
</template>

<script>
import { getBlockedGroupMembers, removeBlockMember } from '@/api/group'
import GroupMembersList from '@/components/lists/GroupMembersList.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseInputText from '@/components/inputs/BaseInputText.vue'

export default {
    props: ['adminConfig'],
    components: { BaseInputText, GroupMembersList, BaseButton },
    data(){
        return{
            searchBlockedMemberText: '',
            currentBlockedMemberPage: 1,
            loadingBlockedMembersList: true,
            blockedMembersList: [],
            loadmoreBlockedMembersList: false
        }
    },
    mounted(){
        this.handleGetBlockedMembers(this.adminConfig.group.id, this.searchBlockedMemberText, this.currentBlockedMemberPage)
    },
    methods: {
        async handleGetBlockedMembers(groupId, query, page){
            try {
                const response = await getBlockedGroupMembers(groupId, query, page)
                if (page == 1) {
                    this.blockedMembersList = [];
                }
                this.blockedMembersList = window._.concat(this.blockedMembersList, response.items)
                if(response.has_next_page){
                    this.loadingBlockedMembersList = true
                    this.currentBlockedMemberPage++;
                }else{
                    this.loadingBlockedMembersList = false
                }
            } catch (error) {
                this.showError(error.error)
            } finally {
                this.loadingBlockedMembersList = false
            }
        },
        debouncedGetBlockedMembersList: window._.debounce(function() {
            this.handleGetBlockedMembers(this.adminConfig.group.id, this.searchBlockedMemberText, this.currentBlockedMemberPage);
        }, 500),
        handleSearchBlockedMembers(){
            this.currentBlockedMemberPage = 1
            this.debouncedGetBlockedMembersList();
        },
        handleRemoveBlockedMember(blockId){
            this.$confirm.require({
                message: this.$t('Do you want to unban this member?'),
                header: this.$t('Please confirm'),
                acceptLabel: this.$t('Ok'),
                rejectLabel: this.$t('Cancel'),
                accept: async () => {
                    try {
                        await removeBlockMember(blockId)
                        this.blockedMembersList = this.blockedMembersList.filter(member => member.id !== blockId)
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