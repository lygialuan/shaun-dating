<template>
    <div v-if="complianceData" class="main-content-section">
        <div class="main-content-section-header">
            <h3 class="main-content-section-header-title">{{ complianceData?.summary?.message }}</h3>
        </div>
        <div class="space-y-base-2">
            <div class="flex gap-x-base-2">
                <Avatar :user="complianceData?.violator" />
                <div class="min-w-0">
                    <UserName :user="complianceData?.violator" />
                    <div class="text-xs text-sub-color dark:text-slate-400">{{ complianceData?.timeline?.reported_at }}</div>
                </div>
            </div>
            <div v-if="complianceData?.content?.excerpt">{{ complianceData?.content?.excerpt }}</div>
             <VueperSlides
                    v-if="complianceData?.media.length"
                    :slide-ratio="0.5625"
                    :infinite="false"
                    :arrows="true"
                    disable-arrows-on-edges
                    :touchable="false"
                    class="activity_content_photos_list no-shadow"
                >
                    <VueperSlide
                        v-for="(media, index) in complianceData?.media"
                        :key="index"
                        class="bg-no-repeat bg-black"
                        :image="media.url"
                    ></VueperSlide>
                    <template #arrow-left>
                        <button class="text-light-gray">
                            <BaseIcon name="arrow_circle_left" size="32" />
                        </button>
                    </template>
                    <template #arrow-right>
                        <button class="text-light-gray">
                            <BaseIcon name="arrow_circle_right" size="32" />
                        </button>
                    </template>
                </VueperSlides>
            <div class="text-center">
                <BaseButton :to="{name: 'contact'}">{{ $t('Contact Us') }}</BaseButton>
            </div>
        </div>
    </div>
</template>

<script>
import { getComplianceData } from '@/api/utility'
import { VueperSlides, VueperSlide } from 'vueperslides';
import BaseButton from '@/components/inputs/BaseButton.vue'
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'

export default {
    props: ['id'],
    components: {
        VueperSlides,
        VueperSlide,
        BaseButton,
        Avatar,
        UserName,
        BaseIcon
    },
    data(){
        return {
            complianceData: null
        }
    },
    mounted(){
        this.handleGetComplianceData()
    },
    methods: {
        async handleGetComplianceData(){
            try {
                this.complianceData = await getComplianceData(this.id)
            } catch (error) {
                this.showError(error.error)
            }
        }
    }
}
</script>