<template>
    <div class="grid-item rounded-lg border border-divider dark:border-white/10 bg-white h-full dark:bg-slate-800">
        <div @click="handleClickGroup" class="block pb-[60%] bg-cover bg-center bg-no-repeat rounded-t-lg cursor-pointer" :style="{ backgroundImage: `url(${item.cover})`}"></div>
        <div class="pt-3 px-4 pb-4">
            <div @click="handleClickGroup" class="grid-item-title block font-semibold mb-1 cursor-pointer truncate">{{ item.name }}</div>
            <div v-if="showBadge" class="mb-2 leading-none">
                <div class="inline-block px-base-1 py-1 rounded text-xs leading-none" :class="statusInfo.badgeClass">{{ statusInfo.text }}</div>
            </div>
            <div v-if="item.categories.length" class="grid-item-sub text-xs truncate text-sub-color dark:text-slate-400 mb-base-1">
                <span v-for="(category, index) in item.categories" :key="category.id">
                    <router-link :to="{name: 'groups', params: { tab: 'all' }, query: {category_id: category.id}}" class="text-inherit">{{ category.name }}</router-link>
                    {{ (index === item.categories.length - 1) ? '' : ' · '}}
                </span>
            </div>
            <div v-if="item.members.length" class="flex items-center">
                <Avatar v-for="(member, index) in item.members" :key="member.id" :user="member" :border="false" :size="24" class="-ms-2 first:ms-0 relative border-2 border-white rounded-full dark:border-slate-800" :style="{ zIndex: item.members.length - index }" />
                <span class="grid-item-sub text-base-xs text-sub-color ms-2 dark:text-slate-400">{{ $filters.numberShortener(item.member_count, $t('[number] member'), $t('[number] members')) }}</span>
            </div>
            <BaseButton v-if="showButton" @click="handleJoinGroup(item)" :type="joinButtonType" fluid class="mt-base-2">{{ joinLabel }}</BaseButton>
        </div>
    </div>
</template>

<script>
import { mapActions } from 'pinia'
import { useGroupStore } from '@/store/group'
import Avatar from '@/components/user/Avatar.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import ContactAdminModal from '@/components/modals/ContactAdminModal.vue'
import BasicModal from '@/components/modals/BasicModal.vue'
import OpenGroupModal from '@/components/modals/OpenGroupModal.vue'

export default {
    props: {
        item: Object,
        showButton: {
            type: Boolean,
            default: true
        },
        showBadge: {
            type: Boolean,
            default: false
        }
    },
    components: { Avatar, BaseButton },
    computed: {
        joinButtonType(){
            if(this.item.request_id){
                return 'danger-outlined'
            } else {
                return 'outlined'
            }
        },
        joinLabel(){
            if(this.item.request_id){
                return this.$t('Cancel Request')
            } else if(this.item.canJoin){
                return this.$t('Join Group')
            } else {
                return this.$t('View Group')
            }
        },
        statusInfo(){
            let badgeClass, text;
            switch(this.item.status){
                case 'pending':
                    badgeClass = 'bg-base-yellow text-main-color';
                    text = this.$t('Pending Approval');
                    break;
                case 'hidden':
                    badgeClass = 'bg-invalid-color text-white';
                    text = this.$t('Hidden');
                    break;
                case 'disable':
                    badgeClass = 'bg-web-wash text-sub-color';
                    text = this.$t('Disabled');
                    break;
                default:
                    badgeClass = 'bg-base-green text-white';
                    text = this.$t('Active');
            }
            return { badgeClass, text };
        }
    },
    methods: {
        ...mapActions(useGroupStore, ['handleDeleteJoinRequest']),
        handleJoinGroup(group){
            if(group.request_id){
                this.$confirm.require({
                    message: this.$t('Do you want to cancel your join request?'),
                    header: this.$t('Please confirm'),
                    acceptLabel: this.$t('Ok'),
                    rejectLabel: this.$t('Cancel'),
                    accept: async () => {
                        try {
                            await this.handleDeleteJoinRequest(group)
                        } catch (error) {
                            this.showError(error.error)
                        }
                    }
                });
            } else {
                this.$router.push({ name: 'group_profile', params: { id: group.id, slug: group.slug } })
            }
        },
        handleClickGroup(){
            switch(this.item.status){
                case 'hidden':
                    if(this.item.is_owner){
                        this.$dialog.open(OpenGroupModal, {
                            data:{
                                group: this.item
                            },
                            props:{
                                showHeader: false,
                                class: 'p-dialog-no-header',
                                modal: true,
                                dismissableMask: true,
                                draggable: false
                            }
                        });
                    } else if(this.item.is_admin){
                        this.$dialog.open(BasicModal, {
                            data:{
                                content: this.$t('This group is hidden now')
                            },
                            props:{
                                showHeader: false,
                                class: 'p-dialog-no-header',
                                modal: true,
                                dismissableMask: true,
                                draggable: false
                            }
                        });
                    }
                    break;
                case 'disable':
                    this.$dialog.open(ContactAdminModal, {
                        data:{
                            content: this.$t('This group is disabled by admin')
                        },
                        props:{
                            showHeader: false,
                            class: 'p-dialog-no-header',
                            modal: true,
                            dismissableMask: true,
                            draggable: false
                        }
                    });
                    break;
                case 'pending':
                    this.$dialog.open(ContactAdminModal, {
                        data:{
                            content: this.$t('This group is pending for approval')
                        },
                        props:{
                            showHeader: false,
                            class: 'p-dialog-no-header',
                            modal: true,
                            dismissableMask: true,
                            draggable: false
                        }
                    });
                    break;
                default:
                    this.$router.push({name: 'group_profile', params: { id: this.item.id, slug: this.item.slug }})
            }
        }
    }
}
</script>