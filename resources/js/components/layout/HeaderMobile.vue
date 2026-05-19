<template>
    <div class="header-mobile flex items-center justify-between gap-base-2 lg:hidden fixed inset-x-0 top-0 bg-[#FFF9F0] px-4 py-3 z-[999] transition-all dark:bg-dark-form-base">
		<Avatar :user="user" :activePopover="false" :router="false" :size="28" @click="setIsOpenSidebar(true)"/>
		<LogoMobile :className="'max-w-[200px] max-h-6'" />
		<button v-if="$route.name === 'home'" @click="openFilterModal()" class="header-icons-list-item inline-block text-main-color dark:text-white relative">
			<BaseIcon name="filter_data" class="align-middle" :size="15"></BaseIcon>
		</button>  
		<button v-else></button>
        <Teleport to="body">
			<Transition name="fade">
				<div v-if="showSearchForm" class="global-search-header-mobile lg:hidden"> 
					<BaseIcon name="close" id="closeMobileSearchBtn" @click="closeSearchForm"/>
					<GlobalSearch :autofocus="true"/>      
				</div>
			</Transition>
			<div class="backdrop-modal" :class="{'show': isOpenSidebar}" @click="setIsOpenSidebar(false)"></div>
        </Teleport>
    </div>
</template>
<script>
import { mapState, mapActions } from 'pinia'
import { useAuthStore } from '@/store/auth'
import { useAppStore } from '@/store/app';
import { useUserStore } from '@/store/user'
import { useDatingStore } from '@/store/dating'
import BaseIcon from '@/components/icons/BaseIcon.vue';
import GlobalSearch from '@/components/layout/GlobalSearch.vue';
import LogoMobile from '@/components/utilities/LogoMobile.vue'
import Avatar from '@/components/user/Avatar.vue'
import DatingFilterModal from '@/components/modals/DatingFilterModal.vue';

export default {
    name: "HeaderMobile",
    components: { BaseIcon, GlobalSearch, LogoMobile, Avatar },
	data(){
		return{
			showSearchForm: false
		}
	},
	computed:{
        ...mapState(useAuthStore, ['user']),
		...mapState(useAppStore, ['isOpenSidebar']),
		...mapState(useDatingStore, ['originAttributes', 'originInterestAttributes']),
		...mapState(useUserStore, ['filterParams']),
    },
	watch: {
        '$route'() {
            this.showSearchForm = false
        }
    },
	methods:{
		...mapActions(useUserStore, ['updateFilters']),
		...mapActions(useAppStore, ['setIsOpenSidebar']),
		openSearchForm(){
			this.showSearchForm = true
		},
		closeSearchForm(){
			this.showSearchForm = false
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
		modifyUrl(data) {
			this.updateFilters(data)
        },
	}
}
</script>