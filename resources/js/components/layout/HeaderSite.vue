<template>
	<header class="main-header hidden lg:block bg-body sticky top-0 z-[998] dark:bg-dark-body">
		<Container class="p-base-2 lg:pt-6 lg:px-6 lg:pb-4">
			<div class="flex flex-wrap justify-between items-center">
				<Logo class="w-max" :className="'max-w-[140px] items-center'" />
				<div class="header-icons-list flex justify-center items-center space-s-4">
					<button v-if="$route.name === 'home'" @click="openFilterModal()" class="header-icons-list-item inline-block text-main-color dark:text-white relative">
						<BaseIcon name="filter_data" class="align-middle" :size="15"></BaseIcon>
					</button>
					<DropdownMenu v-if="enableDarkmode" appendTo="self" class="header-icons-list-item">     
						<template v-slot:dropdown-button>
							<BaseIcon :name="appearanceIcon" class="align-middle" :size="20"></BaseIcon>
						</template>
						<template v-slot:dropdown-content>
							<div class="w-36">
								<ul class="flex flex-col gap-base-1">
									<li class="flex items-center gap-3 p-base-1 rounded-md cursor-pointer" :class="darkmode === 'off' ? 'active text-primary-color dark:text-dark-primary-color' : ''" @click="toggleDarkmode('off')">
										<BaseIcon name="sun" :size="20"></BaseIcon>  
										{{ $t('Light') }}
									</li>
									<li class="flex items-center gap-3 p-base-1 rounded-md cursor-pointer" :class="darkmode === 'on' ? 'active text-primary-color dark:text-dark-primary-color' : ''" @click="toggleDarkmode('on')">
										<BaseIcon name="moon" :size="20"></BaseIcon>  
										{{ $t('Dark') }}
									</li>
									<li class="flex items-center gap-3 p-base-1 rounded-md cursor-pointer" :class="darkmode === 'auto' ? 'active text-primary-color dark:text-dark-primary-color' : ''" @click="toggleDarkmode('auto')">
										<BaseIcon name="desktop" :size="20"></BaseIcon>  
										{{ $t('System') }}
									</li>
								</ul>
							</div>
						</template>                   
					</DropdownMenu>
					<DropdownMenu appendTo="self" class="header-icons-list-item">     
						<template v-slot:dropdown-button>
							<BaseIcon name="bell" :size="20"></BaseIcon>
							<span v-if="pingNotificationCount > 0" class="header-icons-badge absolute -top-1 -right-1 flex items-center justify-center w-[18px] h-[18px] bg-base-red rounded-full text-[10px] text-white cursor-pointer">{{pingNotificationCount > 9 ? '9+' : pingNotificationCount}}</span>
						</template>
						<template v-slot:dropdown-content>
							<div class="dropdown-menu-box-notification p-3 w-96 max-h-[80vh] overflow-auto">
								<NotificationsList :viewAllBtn="true"/>
							</div>
						</template>               
					</DropdownMenu>
					<button @click="clickChat" class="header-icons-list-item inline-block text-main-color dark:text-white relative">
						<BaseIcon name="message" class="align-middle" :size="20"></BaseIcon>
						<span v-if="pingChatCount > 0" class="header-icons-badge absolute -top-1 -right-1 flex items-center justify-center w-[18px] h-[18px] bg-base-red rounded-full text-[10px] text-white cursor-pointer">{{pingChatCount > 9 ? '9+' : pingChatCount}}</span>
					</button>
				</div>  
			</div>
		</Container>
	</header>
</template>

<script>
import { mapState, mapActions } from 'pinia';
import { storeDarkmode } from '@/api/user'
import { useUtilitiesStore } from '@/store/utilities';
import { useAppStore } from '@/store/app';
import { useUserStore } from '@/store/user'
import { useDatingStore } from '@/store/dating'
import { useAuthStore } from '@/store/auth'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import DropdownMenu from '@/components/utilities/DropdownMenu.vue'
import NotificationsList from '@/components/notifications/NotificationsList.vue'
import Container from '@/components/article/Container.vue';
import Constant from '@/utility/constant'
import Logo from '@/components/utilities/Logo.vue'
import DatingFilterModal from '@/components/modals/DatingFilterModal.vue';


export default {
	components: {
		BaseIcon,
		DropdownMenu,
		NotificationsList,
		Container,
		Logo
	},
	data(){
		return {
			enableDarkmode: Constant.ENABLE_DARKMODE,
		}
	},
	mounted() {
		this.handleGetInterestAttributes()
		this.handleGetAttributes()
		this.updateSystemMode()
		window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', this.updateSystemMode)
		if(this.user.address_full && !this.user.dating_search_history){
			this.setLocationFromUser(this.user)
		}
		if(this.user.dating_search_history){
			this.updateFilters(this.user.dating_search_history)
		}
    },
	beforeUnmount() {
		window.matchMedia('(prefers-color-scheme: dark)').removeEventListener('change', this.updateSystemMode)
	},
	watch: {
		darkmode() {
			if (this.darkmode === 'on') {
				document.documentElement.classList.add('dark')
			}else if (this.darkmode === 'off') {
				document.documentElement.classList.remove('dark')
			}else{
				if (this.systemMode === 'dark') {
					document.documentElement.classList.add('dark')
				} else if (this.systemMode === 'light') {
					document.documentElement.classList.remove('dark')
				}
			}
		},
		systemMode() {
			if (this.darkmode === 'auto'){
				if (this.systemMode === 'dark') {
					document.documentElement.classList.add('dark')
				} else if (this.systemMode === 'light') {
					document.documentElement.classList.remove('dark')
				}
			}
		},
		filterParams: {
			deep: true,
			handler() {
				this.resetUsers()
				this.loadUsers()
			}
		}
	},
	computed:{
		...mapState(useUtilitiesStore, ['pingNotificationCount', 'pingChatCount']),
		...mapState(useAppStore, ['config', 'darkmode', 'systemMode']),
		...mapState(useDatingStore, ['originAttributes', 'originInterestAttributes']),
		...mapState(useAuthStore, ['user']),
		...mapState(useUserStore, ['filterParams']),
		appearanceIcon() {
			if (this.darkmode === 'on') {
				return 'moon';
			} else if (this.darkmode === 'off') {
				return 'sun';
			} else {
				return this.systemMode === 'dark' ? 'moon' : 'sun';
			}
		}
	},
	methods: {
		...mapActions(useDatingStore, ['handleGetAttributes', 'handleGetInterestAttributes']),
		...mapActions(useUserStore, ['updateFilters', 'setLocationFromUser', 'resetUsers', 'loadUsers']),
		...mapActions(useAppStore, ['setDarkmode', 'updateSystemMode']),
		async toggleDarkmode(status){
            try {
                await storeDarkmode(status)
                this.setDarkmode(status);
            } catch (error) {
                this.showError(error.error)
            }
        },
		clickChat() {
			let permission = 'chat.allow'
			if(this.checkPermission(permission)){
				this.$router.push({ name: 'chat', force: true})
			}
		},
		openFilterModal() {
            this.$dialog.open(DatingFilterModal, {
                data: {
                    filterParams: this.filterParams,
                    ageRange: {
                        min: 18,
                        max: 80
                    },
					originAttributes: this.originAttributes,
                    originInterestAttributes: this.originInterestAttributes,
                },
                props: {
					class: 'comment-report-modal p-dialog-sm p-dialog-no-header-title',
					modal: true,
					draggable: false,
					showHeader: false,
                },
                onClose: (options) => {
                    const data = options.data;
                    if (data) {
						this.modifyUrl(data)
                    }
                }
            })
        },
		makeQuery(data) {
			let query = {}
			if(data.age.min){
				query.ageMin = data.age.min
			}
			if(data.age.max){
				query.ageMax = data.age.max
			}
			if(data.location){
				query.locationText = data.location[0]?.name
				query.locationId = data.location[0]?.id
			}
			if(data.gender){
				query.gender = data.gender
			}
			if(data.verifiedProfiles){
				query.verifiedProfiles = data.verifiedProfiles
			}
			if(data.isAdvancedFilter){
				if (data.attributeValues.length > 0) {
					query.attributeValues = data.attributeValues;
				}
				if (data.interestAttributeValues.length > 0) {
					query.interestAttributeValues = data.interestAttributeValues;
				}
				query.isAdvancedFilter = data.isAdvancedFilter;
			}
			return query
		},
		modifyUrl(data) {
			this.updateFilters(data)
        },
	}
}
</script>