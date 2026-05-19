<template>
    <div class="bg-white rounded-[18px] p-4 mt-4 dark:bg-dark-form-surface" v-if="owner || localUser.school_name ||  localUser.job_title || localUser.company_name">
        <div class="flex justify-between items-center">
            <div class="flex flex-row space-x-1">
                <h3 class="font-semibold mb-2">{{ $t('Work and Education') }}</h3>
                <span class="font-bold text-red-500" v-if="config.present_profile_is_active && config.present_profile_work_education && owner">+{{ config.present_profile_work_education }}%</span>
            </div>
            <button v-if="owner">
                <BaseIcon name="pencil" size="20" @click="openPopupEditWorkAndEducation"/>
            </button>
        </div>

        <ul class="space-y-2 text-sm text-gray-600 dark:text-dark-text-base-gray font-normal" v-if="localUser.school_name ||  localUser.job_title || localUser.company_name">
            <li class="space-x-2" v-if="localUser.school_name">
                <BaseIcon name="book_open" size="20"/>
                <span>{{ localUser.school_name }}</span>
            </li>
            <li class="space-x-2" v-if="localUser.job_title">
                <BaseIcon name="brief_case" size="20"/>
                <span>{{ localUser.job_title }}</span>
            </li>
            <li class="space-x-2" v-if="localUser.company_name">
                <BaseIcon name="brief_case_two" size="20"/>
                <span>{{ localUser.company_name }}</span>
            </li>
        </ul>
        <div v-else class="text-sm text-gray-600 leading-relaxed dark:text-dark-text-base-gray font-normal">
            {{  $t("Add Work and Education Info")  }}
        </div>
    </div>
</template>

<script>
import { checkPopupBodyClass } from '@/utility/index'
import { mapState } from 'pinia'
import { useAppStore } from '@/store/app'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import ProfileWorkAndEducationModal from '@/components/modals/ProfileWorkAndEducationModal.vue';

export default {
    components: { BaseIcon },
    props: {
        user: {
            type: Object,
            default: null
        },
        owner: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            localUser: window._.clone(this.user) ?? []
        };
    },
    computed: {
		...mapState(useAppStore, ['config'])
	},
    methods: {
        openPopupEditWorkAndEducation(){
            this.$dialog.open(ProfileWorkAndEducationModal, {
                data: {
                    user: this.localUser
                },
                props:{
                    header: this.$t('Work & Education'),
                    class: 'profile-basic-info-modal',
                    modal: true,
                    draggable: false
                },
                onClose: ({ data }) => {
                    if (data) {
                        this.localUser = {
                            ...this.localUser,
                            ...data
                        }
                    }
                    checkPopupBodyClass();
                }
            });
        },
    }
}
</script>