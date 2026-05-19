<template>
    <div class="bg-white rounded-[18px] p-4 mt-4 dark:bg-dark-form-surface" v-if="owner || localUser.about">
        <div class="flex justify-between items-center">
            <div class="flex flex-row space-x-1">
                <h3 class="font-semibold mb-2">{{ $t('About me') }}</h3>
                <span class="font-bold text-red-500" v-if="config.present_profile_is_active &&  config.present_profile_about && owner">+{{ config.present_profile_about }}%</span>
            </div>
            <button v-if="owner">
                <BaseIcon name="pencil" size="20" @click="openPopupEditAboutMe"/>
            </button>
        </div>
        <p class="text-sm text-gray-600 leading-relaxed dark:text-dark-text-base-gray font-normal">
            {{ localUser.about ? localUser.about : $t("Add about me info") }}
        </p>
    </div>
</template>

<script>
import { checkPopupBodyClass } from '@/utility/index'
import { mapState } from 'pinia'
import { useAppStore } from '@/store/app'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import ProfileAboutMeModal from '@/components/modals/ProfileAboutMeModal.vue';

export default {
    components: { 
        BaseIcon
    },
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
        openPopupEditAboutMe(){
            this.$dialog.open(ProfileAboutMeModal, {
                data: {
                    user: this.localUser
                },
                props:{
                    header: this.$t('About Me'),
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