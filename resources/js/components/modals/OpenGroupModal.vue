<template>
    <div class="text-center">
        <div class="mb-base-2">{{ $t('This group is hidden now') }}</div>
        <BaseButton @click="handleOpenGroup">{{ $t('Open Now') }}</BaseButton>
    </div>
</template>

<script>
import { openGroup } from '@/api/group';
import PasswordModal from '@/components/modals/PasswordModal.vue'
import BaseButton from '@/components/inputs/BaseButton.vue';

export default {
    components: { BaseButton },
    inject: {
        dialogRef: {
            default: null
        }
    },
    data(){
        return{
            group: this.dialogRef.data.group
        }
    },
    methods: {
        async handleOpenGroup(){
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
                                await openGroup({
                                    id: this.group.id,
                                    password: data.password
                                })
                                this.dialogRef.close()
                                passwordDialog.close()
                                this.showSuccess(this.$t('Open Group Successfully.'))
                                this.$router.push({name: 'group_profile', params: { id: this.group.id, slug: this.group.slug }})
                            } catch (error) {
                                this.showError(error.error)
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