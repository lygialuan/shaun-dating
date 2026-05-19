<template>
    <div class="flex items-center justify-between gap-3 mb-5">
        <div class="flex items-center gap-base-2">
            <button @click="handleBack">
                <BaseIcon name="caret_left" class="align-middle" />
            </button>
            <h2 class="text-xl font-bold">{{ item.name }}</h2>
        </div>
        <BaseButton @click="handleAddMember">{{ $t('Add Member') }}</BaseButton>
    </div>
    <div>
        <BaseInputText v-model="keyword" :placeholder="$t('Search')" right_icon="search" @input="handleSearchMembers" class="max-w-xs mb-5"/>
        <GroupMembersList :loading="loadingMembers" :members-list="membersList" :has-load-more="hasMoreMembers" @load-more="handleGetMembersList(item.id, page, keyword)">
            <template #actions="{ member }">
                <button class="text-base-red" @click="handleRemoveMember(member.id)">{{ $t('Remove') }}</button>
            </template>   
        </GroupMembersList>
    </div>
</template>

<script>
import { getMembersList, deleteMemberList } from '@/api/user_list'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import GroupMembersList from '@/components/lists/GroupMembersList.vue'
import BaseInputText from '@/components/inputs/BaseInputText.vue';
import AddMembersToListModal from '@/components/modals/AddMembersToListModal.vue'

export default{
    props: ['item'],
    components:{
        BaseIcon,
        BaseButton,
        GroupMembersList,
        BaseInputText
    },
    data(){
        return{
            page: 1,
            keyword: '',
            loadingMembers: true,
            membersList: [],
            hasMoreMembers: false,
            itemInfo: this.item
        }
    },
    mounted(){
        this.handleGetMembersList(this.item.id, this.page, this.keyword)
    },
    methods:{
        async handleGetMembersList(listId, page, query){
            try {
                const response = await getMembersList(listId, page, query)
                if(page === 1){
                    this.membersList = []
                }
                this.membersList = window._.concat(this.membersList, response.items);
                if(response.has_next_page){
                    this.hasMoreMembers = true
                    this.page++;
                }else{
                    this.hasMoreMembers = false
                }
            } catch (error) {
                this.showError(error.error)
            } finally {
                this.loadingMembers = false
            }
        },
        debouncedSearchMembersList: window._.debounce(function() {
            this.handleGetMembersList(this.item.id, this.page, this.keyword)
        }, 500),
        handleSearchMembers(){
            this.page = 1
            this.debouncedSearchMembersList();
        },
        handleAddMember(){
            this.$dialog.open(AddMembersToListModal, {
                data:{
                    id: this.item.id
                },
                props:{
                    header: this.$t('Add Members'),
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
                onClose: (options) => {
                    const response = options.data?.response;
                    if (response) {
                        this.page = 1
                        this.handleGetMembersList(this.item.id, this.page, this.keyword)
                        this.itemInfo = response
                    }
                }
            });
        },
        handleRemoveMember(memberId){
            this.$confirm.require({
                message: this.$t('Do you want to remove this member?'),
                header: this.$t('Please confirm'),
                acceptLabel: this.$t('Ok'),
                rejectLabel: this.$t('Cancel'),
                accept: async () => {
                    try {
                        await deleteMemberList(memberId)
                        this.membersList = this.membersList.filter(member => member.id !== memberId)
                        this.itemInfo.member_count--
                    } catch (error) {
                        this.showError(error.error)
                    }
                }
            }) 
        },
        handleBack(){
            this.$emit('back', this.itemInfo)
        }
    },
    emits: ['back']
}
</script>