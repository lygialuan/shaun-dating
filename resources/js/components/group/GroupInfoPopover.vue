<template>
    <div ref="popover_item" @mouseenter="hoverPopoverItem" @mouseleave="leavePopoverItem">
        <slot></slot>
        <Teleport to="body">
            <Transition name="fade">
                <div class="absolute max-w-xs w-full z-[1199]" ref="popover" v-if="groupInfo && isShown && !isMobile"
                @mouseenter="hoverPopover"
                @mouseleave="leavePopover"
                :style="caretPosition ?
                {
                    top: `${ caretPosition.top ? caretPosition.top + 'px' : ''}`,
                    right: `${ caretPosition.right ? caretPosition.right + 'px' : ''}`,
                    bottom: `${ caretPosition.bottom ? caretPosition.bottom + 'px' : ''}`,
                    left: `${ caretPosition.left ? caretPosition.left + 'px' : ''}`,
                } : {}">
                    <div class="header-profile m-0 p-base-2 shadow-popover dark:shadow-dark-popover">
                        <div class="flex gap-base-2 mb-base-2">
                            <div class="w-24 h-24 flex-shrink-0 rounded-full border border-divider dark:border-slate-700">
                                <img :src="groupInfo.cover" :alt="groupInfo.name" class="rounded-full w-full h-full object-cover">
                            </div>
                            <div class="flex flex-col gap-2 flex-1 min-w-0">
                                <GroupName :group="groupInfo" :active-popover="false" class="text-base-lg" />
                                <div class="flex items-center gap-base-2 text-sub-color dark:text-slate-400">
                                    <BaseIcon :name="privacyIcon(groupInfo.type)" size="20" />
                                    <span>{{ groupInfo.type_text }}</span>
                                </div>
                                <div v-if="groupInfo.members.length" class="flex gap-base-2 text-sub-color dark:text-slate-400">
                                    <BaseIcon name="users" size="20" />
                                    <div class="flex-1">
                                        <span>{{ $filters.numberShortener(groupInfo.member_count, $t('[number] member'), $t('[number] members')) }}</span>
                                        <div class="flex">
                                            <Avatar v-for="(member, index) in groupInfo.members" :key="member.id" :user="member" :border="false" :activePopover="false" :size="24" class="-ms-2 first:ms-0 relative border-2 border-white rounded-full dark:border-slate-800" :style="{ zIndex: groupInfo.members.length - index }" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <BaseButton type="outlined" :to="{ name: 'group_profile', params: { id: groupInfo.id, slug: groupInfo.slug } }" fluid>{{ $t('View Group') }}</BaseButton>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
<script>
import { mapState } from 'pinia'
import { useAuthStore } from '@/store/auth';
import { useAppStore } from '@/store/app';
import { getGroupProfile } from '@/api/group'
import BaseButton from '@/components/inputs/BaseButton.vue';
import BaseIcon from '@/components/icons/BaseIcon.vue'
import Avatar from '@/components/user/Avatar.vue'
import GroupName from '@/components/group/GroupName.vue';

export default {
    components: { BaseButton, BaseIcon, Avatar, GroupName },
    props: {
        item: {
            type: Object,
            default: null
        },
        activePopover: {
            type: Boolean,
            default: true
        },
    },
    data(){
        return{
            groupInfo: null,
            isShown: false,
			caretPosition: {
				top: null,
				right: null,
				bottom: null,
				left: null
			},
            hoverPopoverItemStatus: false,
            hoverPopoverStatus: false,        
        }
    },
    computed: {
		...mapState(useAuthStore, ['user']),
        ...mapState(useAppStore, ['isMobile'])
	},
    watch:{
        async isShown(){
            if (this.isShown) {
                const response = await getGroupProfile(this.item.id)
                this.groupInfo = response
            }
        }
    },
    methods:{
        privacyIcon(value){
            switch (value) {
                case 1:
                    return 'lock'
                default:
                    return 'earth'
            }
        },
        hoverPopoverItem() {
            if(this.activePopover){
                this.hoverPopoverItemStatus = true
                setTimeout(() => {
                    if(this.hoverPopoverItemStatus){
                        this.updateCaretPosition()
                        this.isShown = !this.isShown;
                    }
                }, 1400);
            }
        },
		leavePopoverItem() {
            if(this.activePopover){              
                this.hoverPopoverItemStatus = false
                setTimeout(() => {         
                    if(!this.hoverPopoverStatus){
                        if (this.isShown) this.isShown = false;
                    }
                }, 400); 
            }
		},
        hoverPopover(){
            if(this.activePopover){              
                this.hoverPopoverStatus = true
            }
        },
        leavePopover(){
            if(this.activePopover){            
                this.hoverPopoverStatus = false
                if (this.isShown) this.isShown = false;       
            }
        },
        updateCaretPosition () {
			const popoverItemRect = this.$refs.popover_item?.getBoundingClientRect()
		
			// set X coordinate emoji box
            if(this.user.rtl){
                if(popoverItemRect?.right > 320){
                    this.caretPosition.right = window.innerWidth - popoverItemRect?.right
                }else{
                    this.caretPosition.left = popoverItemRect?.left
                }
            }else{
                if(window.innerWidth - popoverItemRect?.left > 320){
                    this.caretPosition.left = popoverItemRect?.left
                }else{
                    this.caretPosition.right = window.innerWidth - popoverItemRect?.right
                }
            }

			// set Y coordinate emoji box
			if((window.innerHeight - popoverItemRect?.top) > 290){
				this.caretPosition.top = popoverItemRect?.top + (window.pageYOffset ? window.pageYOffset : 0) + popoverItemRect?.height
			}else{
				this.caretPosition.bottom = window.innerHeight - (window.pageYOffset ? window.pageYOffset : 0) - popoverItemRect?.top + 10
			}
		}
    }
}
</script>