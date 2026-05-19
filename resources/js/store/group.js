import { defineStore } from 'pinia'
import { setTitlePage } from '@/utility'
import { useAppStore } from '@/store/app'
import { getGroupProfile, getGroupRules, getAllGroups, getSuggestGroups, removeJoinRequest, storeJoinGroup, storeLeaveGroup, getJoinedGroups, getManagedGroups } from '@/api/group'
import { getSearchResults } from '@/api/search';

export const useGroupStore = defineStore('group', {
    state: () => ({
        loadingGroupsList: true,
        groupsList: [],
        groupInfo: null,
        groupRulesList: []
    }),
    actions: {
        doPushGroupsList({groupsList, page}){
            if (page == 1) {
                this.groupsList = [];
            }
            this.groupsList = window._.concat(this.groupsList, groupsList)
        },
        unsetGroupsList(){
            this.loadingGroupsList = true
            this.groupsList = [];
        },
        async handleGetGroupDetail(groupId){
            try {
				const response = await getGroupProfile(groupId)
				this.groupInfo = response
                setTitlePage(this.groupInfo.name);
                return response
			} catch (error) {
				useAppStore().setErrorLayout(true)
			}
        },
        setGroupInfo(value = null) {
            this.groupInfo = value
        },
        async handleGetGroupRules(groupId){
            try {
                const response = await getGroupRules(groupId)
                this.groupRulesList = response
            } catch (error) {
                this.showError(error.error)
            }
        },
        setGroupRulesList(value = []) {
            this.groupRulesList = value
        },
        async getAllGroupsList(page, keyword, category){
            try {
				const response = await getAllGroups(page, keyword, category)
                this.doPushGroupsList({groupsList: response.items, page})
                return response
			} catch (error) {
				this.showError(error.error)
			} finally {
                this.loadingGroupsList = false
            }
        },
        async getSuggestGroupsList(page){
            try {
				const response = await getSuggestGroups(page)
                this.doPushGroupsList({groupsList: response.items, page})
                return response
			} catch (error) {
				this.showError(error.error)
			} finally {
                this.loadingGroupsList = false
            }
        },
        async getJoinedGroupsList(page){
            try {
				const response = await getJoinedGroups(page)
                this.doPushGroupsList({groupsList: response.items, page})
                return response
			} catch (error) {
				this.showError(error.error)
			} finally {
                this.loadingGroupsList = false
            }
        },
        async getManagedGroupsList(status, page){
            try {
				const response = await getManagedGroups(status, page)
                this.doPushGroupsList({groupsList: response.items, page})
                return response
			} catch (error) {
				this.showError(error.error)
			} finally {
                this.loadingGroupsList = false
            }
        },
        async getSearchGroupsList(search_type, query, type, page){
            try {
				const response = await getSearchResults(search_type, query, type, page)
                this.doPushGroupsList({groupsList: response, page})
                return response
			} catch (error) {
				this.showError(error.error)
			} finally {
                this.loadingGroupsList = false
            }
        },
        async handleDeleteJoinRequest(groupData){
            if(!groupData.request_id){
                return;
            }
            await removeJoinRequest(groupData.request_id)

            let group = this.groupsList.find(group => group.id === groupData.id);

            if (typeof group === 'undefined') {
                group = this.groupInfo;
            }

            if(group.id === groupData.id){
                group.request_id = 0
                group.canJoin = true
            }
        },
        async joinGroup(groupData){
            const response = await storeJoinGroup(groupData.id)
            return response
        },
        async leaveGroup(groupData){
            const response = await storeLeaveGroup(groupData.id)
            return response
        }
    },
    persist: false
})