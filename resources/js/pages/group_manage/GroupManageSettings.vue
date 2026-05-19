<template>
    <div class="main-content-section">
        <div class="flex flex-col gap-base-2">
            <h3 class="main-content-section-header-title">{{ $t('Settings') }}</h3>
            <div class="flex gap-4">
                <div class="flex-1">
                    <div class="font-bold">{{ $t('Post Approval') }}</div>
                    <div>{{ $t('Turn this on if you want admins and moderators to approve each post. This setting will not apply for post create by admins and moderators.') }}</div>
                </div>
                <BaseSwitch v-model="postApproveEnable" />
            </div>
            <div class="flex flex-wrap gap-4">
                <div class="font-bold flex-1">{{ $t('Who can post') }}</div>
                <div class="flex-3">
                    <BaseSelect v-model="whoCanPost" :options="whoCanPostOptions" optionLabel="key" optionValue="value" :error="error.who_can_post" />
                </div>
            </div>
            <div class="flex flex-wrap gap-4">
                <div class="font-bold flex-1">{{ $t('Web Address') }}</div>
                <div class="flex-3">
                    <BaseInputText v-model="slug" :error="error.slug"/>
                    <div class="text-sub-color text-xs italic mt-base-1 dark:text-slate-400 break-word">{{ baseUrl + '/groups/' + adminConfig.group.id + '/' + slug }}</div>
                </div>
            </div>
            <div class="flex gap-4">
                <div class="flex-1"></div>
                <div class="flex-3 flex flex-wrap gap-base-2">
                    <BaseButton :loading="loadingSaveButton" @click="handleSaveGroupSettings">{{ $t('Save') }}</BaseButton>
                    <template v-if="adminConfig.is_owner">
                        <BaseButton type="warning" :loading="loadingHideButton" @click="handleHideGroup">{{ $t('Hide Group') }}</BaseButton>
                        <BaseButton type="danger" :loading="loadingDeleteButton" @click="handleDeleteGroup">{{ $t('Delete Group') }}</BaseButton>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { storeGroupSettings, hideGroup, deleteGroup } from '@/api/group'
import BaseSwitch from '@/components/inputs/BaseSwitch.vue'
import BaseSelect from '@/components/inputs/BaseSelect.vue'
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import PasswordModal from '@/components/modals/PasswordModal.vue'

export default {
    props: ['adminConfig'],
    components: { BaseSwitch, BaseSelect, BaseInputText, BaseButton },
    data(){
        return{
            postApproveEnable: this.adminConfig.post_approve_enable,
            whoCanPost: this.adminConfig.who_can_post,
            slug: this.adminConfig.group.slug,
            error: {
                who_can_post: null,
                slug: null
            },
            loadingSaveButton: false,
            loadingHideButton: false,
            loadingDeleteButton: false
        }
    },
    computed:{
        whoCanPostOptions(){
            return window._.map(this.adminConfig.whoCanPostList, function(key, value) {
                return { key, value }
            });
        },
        baseUrl(){
            return window.siteConfig.siteUrl
        },
    },
    methods:{
        async handleSaveGroupSettings(){
            this.loadingSaveButton = true
            try {
                await storeGroupSettings({
                    id: this.adminConfig.group.id,
                    post_approve_enable: this.postApproveEnable,
                    who_can_post: this.whoCanPost,
                    slug: this.slug
                })
                this.showSuccess(this.$t('Your changes have been saved.'))
				this.resetErrors(this.error)
            } catch (error) {
                this.handleApiErrors(this.error, error)
            } finally {
                this.loadingSaveButton = false
            }
        },
        handleHideGroup(){
            this.$confirm.require({
                message: this.$t("Are you sure you want to hide this group? All members can't access this group until you re-open it"),
                header: this.$t('Please confirm'),
                acceptLabel: this.$t('Ok'),
                rejectLabel: this.$t('Cancel'),
                accept: () => {
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
                                    this.loadingHideButton = true
                                    try {
                                        await hideGroup({
                                            id: this.adminConfig.group.id,
                                            password: data.password
                                        })
                                        passwordDialog.close()
                                        this.$router.push({ name: 'list_groups' })
                                        this.showSuccess(this.$t('Hide Group Successfully.'))
                                    } catch (error) {
                                        this.showError(error.error)
                                        passwordDialog.close()
                                    } finally {
                                        this.loadingHideButton = false
                                    }
                                }
                            }
                        }
                    })
                }
            });
        },
        handleDeleteGroup(){
            this.$confirm.require({
                message: this.$t("Are you sure you want to delete this group?"),
                header: this.$t('Please confirm'),
                acceptLabel: this.$t('Ok'),
                rejectLabel: this.$t('Cancel'),
                accept: () => {
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
                                    this.loadingDeleteButton = true
                                    try {
                                        await deleteGroup({
                                            id: this.adminConfig.group.id,
                                            password: data.password
                                        })
                                        passwordDialog.close()
                                        this.$router.push({ name: 'groups' })
                                        this.showSuccess(this.$t('Delete Group Successfully.'))
                                    } catch (error) {
                                        this.showError(error.error)
                                        passwordDialog.close()
                                    } finally {
                                        this.loadingDeleteButton = false
                                    }
                                }
                            }
                        }
                    })
                }
            });
        }
    }
}
</script>