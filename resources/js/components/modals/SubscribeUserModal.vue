<template>
    <div v-if="user">
        <div class="text-center mb-base-2">
            <Avatar :user="user" :size="180" class="mx-auto mb-base-2" />
            <div class="text-xl font-semibold">{{ $t('Subscribe to') }} <router-link :to="{name: 'profile', params: { user_name: user.user_name }}">{{ mentionChar + user.user_name }}</router-link></div>
            <div class="text-sub-color dark:text-slate-400">{{ (selectedPackage ? (selectedPackage.package?.description + ' &middot; ') : '') + $t('Cancel anytime') + ' &middot; ' + $filters.numberShortener(profilePackages?.subscriber_count, $t('[number] subscriber'), $t('[number] subscribers'))}} </div>
        </div>
        <h3 class="text-main-color text-lg font-extrabold dark:text-white">{{ $t('Exclusive content') }}</h3>
        <p class="text-sub-color mb-base-1 dark:text-slate-400">{{ $filters.numberShortener(profilePackages?.post_paid_count, $t('[number] post'), $t('[number] posts')) }}</p>
        <SlimScroll v-if="profilePackages?.features.length" class="mb-base-2">
            <div 
                v-for="(feature, index) in profilePackages?.features" 
                :key="index" 
                class="w-[calc(50%-10px)] flex-[0_0_calc(50%-10px)] md:w-[calc(25%-10px)] md:flex-[0_0_calc(25%-10px)]"
                >
                <div
                    class="bg-center bg-no-repeat bg-cover pb-[150%] rounded-base-lg overflow-hidden"
                    :style="{ backgroundImage: `url(${feature})`}">
                </div>
            </div>
        </SlimScroll>
        <h3 class="text-main-color text-lg font-extrabold dark:text-white mb-base-1">{{ $t('Features') }}</h3>
        <div class="flex flex-col gap-base-2 mb-base-2">
            <div class="flex gap-base-2 items-center">
                <BaseIcon name="check" size="20" />
                <span>{{ $t("Full access to creator’s exclusive content") }}</span>
            </div>
            <div class="flex gap-base-2 items-center">
                <BaseIcon name="check" size="20" />
                <span>{{ $t("Cancel your subscription at anytime") }}</span>
            </div>
        </div>
        <BaseSelect v-model="selectedPackage" :options="profilePackages?.packages" :optionLabel="(option) => option.package.description" :optionValue="(option) => option" :error="error.id" class="mb-base-2" />
        <BaseButton @click="handleStoreSubscriberUser" :disabled="! selectedPackage" fluid>{{ $t('Subscribe') }}</BaseButton>
    </div>
</template>

<script>
import { mapActions } from 'pinia'
import { usePostStore } from '@/store/post'
import { useUtilitiesStore } from '@/store/utilities'
import { useProfileStore } from '@/store/profile'
import { getPaidContentProfilePackages } from '@/api/paid_content'
import Constant from '@/utility/constant'
import Avatar from '@/components/user/Avatar.vue'
import BaseSelect from '@/components/inputs/BaseSelect.vue'
import PasswordModal from '@/components/modals/PasswordModal.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue';
import SlimScroll from '@/components/utilities/SlimScroll.vue'

export default {
    inject: {
        dialogRef: {
            default: null
        }
    },
    components: {
        Avatar,
        BaseSelect,
        BaseButton,
        BaseIcon,
        SlimScroll
    },
    data(){
        return{
            mentionChar: Constant.MENTION,
            user: this.dialogRef.data.user,
            refCode: this.dialogRef.data.refCode,
            selectedPackage: null,
            profilePackages: null,
            error: {
                id: null
            }
        }
    },
    mounted(){
        this.getPaidContentProfilePackages(this.user.id);
    },
    methods:{
        ...mapActions(usePostStore, ['storeSubscribeUser']),
        ...mapActions(useUtilitiesStore, ['pingNotification']),
        ...mapActions(useProfileStore, ['getUserInfo']),
        async getPaidContentProfilePackages(userId){
            try {
                const response = await getPaidContentProfilePackages(userId);
                this.profilePackages = response
                this.selectedPackage = response.packages.find(item => item.is_default)
            } catch (error) {
                this.showError(error.error)
            }
        },
        handleStoreSubscriberUser(){
            const passwordDialog = this.$dialog.open(PasswordModal, {
				props: {
					header: this.$t('Enter Password'),
					class: 'password-modal',
					modal: true,
					dismissableMask: true,
					draggable: false
				},
                emits: {
					onConfirm: async (data) => {        
						if (data.password) {
							try {
                                await this.storeSubscribeUser({
                                    user: this.user,
                                    id: this.selectedPackage?.id,
                                    password: data.password,
                                    ref_code: this.refCode
                                });
                                this.showSuccess(this.$t('Subscription successful'))
                                this.dialogRef.close()
                                this.pingNotification()
                                this.getUserInfo(this.user.user_name)
                            } catch (error) {
                                this.handleApiErrors(this.error, error)
                            } finally {
                                passwordDialog.close()
                            }
						}
					}
				}
			})
        }
    }
}
</script>