<template> 
    <button v-for="(privacy, index) in visiblePrivaciesList" :key="index" class="flex gap-base-2 items-center py-base-1 w-full" @click="handleSelectPrivacy(privacy.value)">
        <div class="btn-primary w-10 h-10 flex items-center justify-center bg-primary-color text-white rounded-full dark:bg-dark-primary-color">
            <BaseIcon :name="privacy.icon"/>
        </div>
        <div class="font-semibold flex-1 text-start">{{ privacy.name }}</div>
        <button v-if="isSelected(privacy.value)" class="text-primary-color">
            <BaseIcon name="check"/>
        </button>
    </button>
</template>
<script>
import { mapState } from 'pinia'
import { useAppStore } from '@/store/app'
import { changeCommentPrivacy } from '@/api/posts'
import Constant from '@/utility/constant';
import BaseIcon from '@/components/icons/BaseIcon.vue';

export default {
    components: { BaseIcon },
    inject: ['dialogRef'],
    data(){
        return{
            post: this.dialogRef.data.post
        }
    },
    computed: {
        ...mapState(useAppStore, ['config']),
        privaciesList(){
            return [
                { icon: 'globe', name: this.$t('Everyone'), value: 'everyone' },
                { icon: 'user_check', name: this.$t('Accounts you follow'), value: 'following' },
                { icon: 'seal_check', name: this.$t('Verified accounts'), value: 'verified', isShow: this.config?.userVerifyEnable },
                { icon: 'at', name: this.$t('Only accounts you mention'), value: 'mentioned' },
            ]
        },
        visiblePrivaciesList() {
            return this.privaciesList.filter(privacy => privacy.isShow || typeof(privacy.isShow) == 'undefined');
        }
    },
    methods:{
        async handleSelectPrivacy(privacy){
            try {
                await changeCommentPrivacy({
                    id: this.post.id,
                    comment_privacy: privacy
                })
                this.post.comment_privacy = privacy
                this.dialogRef.close()
                this.showSuccess(this.$t('Change Successfully.'))
            } catch (error) {
                if (error.error.code == Constant.RESPONSE_CODE_MEMBERSHIP_PERMISSION) {
					this.showPermissionPopup('post',error.error.message)
				} else {
					this.showError(error.error)
				}
            }
            
        },
        isSelected(privacy){
            return this.post.comment_privacy === privacy
        }
    }
}
</script>