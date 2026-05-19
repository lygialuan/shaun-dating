<template>
    <Error v-if="error">{{error}}</Error>
    <div class="py-5">
        <p class="text-center mb-base-2">{{$t("Here's a list of the people that have been invited to join thru email and list of people has signed up using your referral link/code")}}</p>
        <div v-if="inviteInfo" class="flex justify-between items-center text-center border border-secondary-box-color mb-base-2 dark:border-white/10">
            <div class="px-2 py-5 flex-1">
                <div class="text-3xl md:text-base-4xl py-3">{{inviteInfo.total_sent}}</div>
                <div class="text-base-xs uppercase">{{$t('Sent')}}</div>
            </div>
            <div class="px-2 py-5 flex-1 border-s border-secondary-box-color dark:border-white/10">
                <div class="text-3xl md:text-base-4xl py-3">{{inviteInfo.total_referral}}</div>
                <div class="text-base-xs uppercase">{{$t('Joined')}}</div>
            </div>
        </div>
        <Loading v-else/>
        <div class="tabs-list flex gap-base-2 mb-4">
            <button @click="filterReferralsList('invite')" class="tabs-list-item p-base-2 rounded-base-lg" :class="type === 'invite' ? 'active bg-primary-color text-white dark:bg-dark-primary-color' : 'bg-web-wash dark:bg-dark-web-wash'">{{ $t('Invited email list') }}</button>
            <button @click="filterReferralsList('referral')" class="tabs-list-item p-base-2 rounded-base-lg" :class="type === 'referral' ? 'active bg-primary-color text-white dark:bg-dark-primary-color' : 'bg-web-wash dark:bg-dark-web-wash'">{{ $t('Your referrals') }}</button>
        </div>
        <form class="flex gap-base-2 mb-base-2" @submit.prevent="searchInvitesList">
            <BaseInputText v-model="searchKeyword" :placeholder="$t('Keyword')" class="max-w-[240px]"/>
            <BaseButton>{{$t('Search')}}</BaseButton>
        </form>
        <template v-if="type === 'invite'">
            <div class="relative overflow-x-auto border border-b-0 border-table-border-color md:rounded-md dark:border-white/10">
                <table class="s-table w-full text-sm whitespace-nowrap text-center">
                    <thead class="s-table-header text-xs uppercase bg-table-header-color dark:bg-dark-web-wash">
                        <tr>
                            <th scope="col" class="p-3 text-start">{{$t('Email')}}</th>
                            <th scope="col" class="p-3">{{$t('Last Updated')}}</th>
                            <th scope="col" class="p-3">{{$t('Status')}}</th>
                        </tr>
                    </thead>
                    <tbody class="s-table-body">
                        <tr v-if="loadingInvites">
                            <td colspan="3"><Loading class="mt-base-2"/></td>
                        </tr>
                        <template v-else>
                            <template v-if="invitesList.length">                 
                                <tr v-for="(inviteItem, index) in invitesList" :key="index" class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b border-table-border-color dark:border-gray-700">
                                    <td class="p-3 text-start">{{ inviteItem.email }}</td>
                                    <td class="p-3">{{ inviteItem.sent_at }}</td>
                                    <td class="p-3">{{ labelText(inviteItem.status) }}</td>
                                </tr>
                            </template>
                            <tr v-else class="border-b border-table-border-color dark:border-gray-700">
                                <td colspan="3" class="p-3">{{$t('No Invites Sent')}}</td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <div v-if="loadmoreInvites" class="text-center my-base-2">
                <BaseButton @click="getReferralsList(type, page, searchKeyword)">{{$t('View more')}}</BaseButton>
            </div>
        </template>
        <UsersList v-else-if="type === 'referral'" :loading="loadingInvites" :usersList="invitesList" :has-load-more="loadmoreInvites" @load-more="getReferralsList(type, page, searchKeyword)" />
    </div> 
</template>

<script>
import { getInviteInfo, getReferralsList } from '@/api/invite';
import Error from '@/components/utilities/Error.vue';
import Loading from '@/components/utilities/Loading.vue';
import BaseButton from '@/components/inputs/BaseButton.vue';
import BaseInputText from '@/components/inputs/BaseInputText.vue';
import UsersList from '@/components/lists/UsersList.vue';

export default {
    components: { Error, Loading, BaseButton, BaseInputText, UsersList },
    data() {
        return {
            error: null,
            inviteInfo: null,
            invitesList: null,
            loadingInvites: true,
            loadmoreInvites: false,
            page: 1,
            searchKeyword: '',
            type: 'invite'
        };
    },
    mounted(){
        this.getInviteInfo()
        this.getReferralsList(this.type, this.page, this.searchKeyword)
    },
    methods: {
        labelText(name){
            switch(name){
                case 'sent':
                    return this.$t('Sent')
                case 'joined':
                    return this.$t('Joined')
            }
        },
        async getInviteInfo() {
            try {
                const response = await getInviteInfo();
                this.inviteInfo = response
            }
            catch (error) {
                this.error = error;
            }
        },
        async getReferralsList(type, page, keyword) {
            try {
                const response = await getReferralsList(type, page, keyword)
                if(page === 1){
                    this.invitesList = response.items
                }else{
                    this.invitesList = window._.concat(this.invitesList, response.items);
                }
                if(response.has_next_page){
                    this.loadmoreInvites = true
                    this.page++;
                }else{
                    this.loadmoreInvites = false
                }
            }
            catch (error) {
                this.error = error
            } finally {
                this.loadingInvites = false
            }
        },
        resetReferralsList(){
            this.loadingInvites = true
            this.invitesList = []
            this.loadmoreInvites = false
            this.page = 1
            this.getReferralsList(this.type, this.page, this.searchKeyword)
        },
        searchInvitesList(){
            this.resetReferralsList()
        },
        filterReferralsList(type){
            this.type = type
            this.searchKeyword = ''
            this.resetReferralsList()
        }
    }
}
</script>