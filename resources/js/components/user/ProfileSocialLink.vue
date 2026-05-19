<template>
    <div class="bg-white rounded-[18px] p-4 mt-4 dark:bg-dark-form-surface" v-if="owner || (localUser.links && localUser.links.length > 0)">
        <div class="flex justify-between items-center">
            <div class="flex flex-row space-x-1">
                <h3 class="font-semibold mb-2">{{ $t('Social Links') }}</h3>
                <span class="font-bold text-red-500" v-if="config.present_profile_is_active && config.present_profile_social_profiles && owner">+{{ config.present_profile_social_profiles }}%</span>
            </div>
            <button v-if="owner">
                <BaseIcon name="pencil" size="20" @click="openPopupEditSocialLink"/>
            </button>
        </div>
        <div v-if="localUser.links && localUser.links.length">
            <a v-for="(link, index) in localUser.links" :key="index" target="_blank" :href="link.link" class="group inline-flex items-center gap-base-1 text-main-color dark:text-white">
                <span class="block w-10 h-10 flex-shrink-0 bg-main-color dark:bg-white" :style="iconStyle(link.icon)"></span>
            </a>
        </div>
        <div v-else class="text-sm text-gray-600 leading-relaxed dark:text-dark-text-base-gray font-normal">
            {{  $t("Add social links")  }}
        </div> 
    </div>
</template>

<script>
import { checkPopupBodyClass } from '@/utility/index'
import { mapState, mapActions } from 'pinia'
import { useAuthStore } from '@/store/auth'
import { useAppStore } from '@/store/app'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import ProfileSocialLinkModal from '@/components/modals/ProfileSocialLinkModal.vue';

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
		...mapActions(useAuthStore, ['me']),
        openPopupEditSocialLink(){
            this.$dialog.open(ProfileSocialLinkModal, {
                data: {
                    user: this.localUser
                },
                props:{
                    header: this.$t('Add Social Link'),
                    class: 'profile-basic-info-modal',
                    modal: true,
                    draggable: false
                },
                onClose: ({ data }) => {
                    if (data) {
                        this.me();
                        this.localUser = {
                            ...this.localUser,
                            ...data
                        }
                    }
                    checkPopupBodyClass();
                }
            });
        },
        iconStyle(icon) {
            return {
                mask: `url(${icon}) center center / contain no-repeat`,
                WebkitMask: `url(${icon}) center center / contain no-repeat`,
            };
        }
    }
}
</script>