<template>
	<div class="flex flex-col items-center justify-center h-full min-h-[calc(100vh-250px)] text-center gap-2">
		<h3 class="text-xl font-semibold">
			{{ $t("No matches within your preferences") }}
		</h3>
		<label class="text-sm dark:text-dark-text-base-gray">
			{{ $t("Refine your filters to discover more compatible profiles.") }}
		</label>
		<BaseButton class="mt-2 !rounded-lg dark:!bg-[#2C2C2C] dark:!border-[#2C2C2C] dark:!text-white" size="sm" @click="openFilterModal()" icon="filter_data">
			{{ $t("Refine Filters") }}
		</BaseButton>
	</div>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { useUserStore } from '@/store/user'
import { useDatingStore } from '@/store/dating'
import BaseButton from '@/components/inputs/BaseButton.vue'
import DatingFilterModal from '@/components/modals/DatingFilterModal.vue';

export default {
	components: { 
		BaseButton
	},
	computed: {
		...mapState(useUserStore, ['filterParams']),
		...mapState(useDatingStore, ['originAttributes', 'originInterestAttributes']),
    },
	methods: {
		...mapActions(useUserStore, ['updateFilters']),
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
