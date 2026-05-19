<template>
    <Loading v-if="!locationReady"/>
    <div v-show="locationReady">
        <div class="flex flex-col space-y-2 mb-4"> 
            <label>{{$t('Name')}}</label>
            <BaseInputText v-model="name" :error="error.name"/>
        </div>
        <div class="flex flex-col space-y-2 mb-4"> 
            <label>{{$t('Gender')}}</label>
            <BaseSelect v-model="gender_id" :options="genders" optionLabel="name" optionValue="id" :error="error.gender_id" />
        </div>
        <div class="flex flex-col space-y-2 mb-4"> 
            <label>{{$t('DOB')}}</label>
            <BaseCalendar v-model="birthday" :error="error.birthday"/>
        </div>
        <div class="flex flex-col space-y-2 mb-4"> 
            <label>{{$t('Location')}}</label>
            <BaseSelectLocation @ready="locationReady = true" v-model="location" :show-label="false" :error="error"/>
        </div>
        <div class="w-full space-y-2"> 
            <BaseButton :loading="loadingSave" @click="saveProfileSettings()" fluid>{{$t('Save')}}</BaseButton>
            <BaseButton type="transparent" @click="cancel()" fluid>{{$t('Cancel')}}</BaseButton>
        </div>
    </div>
</template>

<script>
import { mapActions } from 'pinia'
import { useAuthStore } from '@/store/auth'
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import BaseSelect from '@/components/inputs/BaseSelect.vue'
import BaseCalendar from '@/components/inputs/BaseCalendar.vue'
import BaseSelectLocation from '@/components/inputs/BaseSelectLocation.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import Loading from '@/components/utilities/Loading.vue'

export default {
	components: { 
        BaseInputText,
        BaseSelect,
        BaseCalendar,
        BaseSelectLocation,
        BaseButton,
        Loading
    },
    inject: {
        dialogRef: {
            default: null
        }
    },
    data() {
        return{
            name: null,
			gender_id: null,
			genders: [],
			birthday: null,
            location: {
				country_id: null,
				state_id: null,
				city_id: null,
				zip_code: null,
				address: null
			},
            originalName: null,
            timezone: null,
			timezones: [],
            error: {
				name: null,
				gender_id: null,
				birthday: null,
				location: null,
				timezone: null,
			},
			loadingSave: false,
            locationReady: false,
        }
    },
    computed: {
        localUser(){
            return this.dialogRef?.data?.user || {}
        }
    },
    watch: {
        localUser: {
            immediate: true,
            handler(user){
                if (!user || !user.id) return
                this.setBasicInfoUser()
            }
        }
    },
    methods: {
		...mapActions(useAuthStore, ['storeProfileSettings', 'updateUserMeInfo', 'me']),
        setBasicInfoUser(){
            const user = this.localUser
            this.name = user.name
            this.gender_id = user.gender_id
            this.genders = [
                { id: 0, name: this.$t('Prefer not to say') },
                ...(user.genders || [])
            ]
            this.birthday = user.birthday ? new Date(user.birthday) : null
            this.location = {
                country_id: user.country_id,
                state_id: user.state_id,
                city_id: user.city_id,
                zip_code: user.zip_code,
                address: user.address
            }
            this.originalName = user.name
            this.timezone = user.timezone
            this.timezones = window._.map(user.timezones || {}, (key, value) => {
                return { key, value }
            })
        },
        async saveProfileSettings(){
			if (this.loadingSave) {
				return
			}
			this.loadingSave = true
			try {
				const saveSettings = async() => {
                    const response = await this.storeProfileSettings({
                        name: this.name,
                        gender_id: this.gender_id == 0 ? 0 : this.gender_id,
                        birthday: this.formatDateTime(this.birthday),
                        country_id: this.location.country_id ?? 0,
                        state_id: this.location.state_id ?? 0,
                        city_id: this.location.city_id ?? 0,
                        zip_code: this.location.zip_code,
                        address: this.location.address,
                        timezone: this.timezone,
                    })
					this.showSuccess(this.$t('Your changes have been saved.'))
					this.resetErrors(this.error)
                    this.me()
                    this.dialogRef.close(response)
				}

                if(this.localUser.is_verify){
					const isNameChanged = (this.name && this.name !== this.originalName) || (this.pageName && this.pageName !== this.originalName);
					if(isNameChanged){
						this.$confirm.require({
							message: this.$t('You will lose your profile verification badge if you change your name. Do you want to continue?'),
							header: this.$t('Please confirm'),
							acceptLabel: this.$t('Ok'),
							rejectLabel: this.$t('Cancel'),
							accept: async() => {
                                try {
                                    await saveSettings()
                                    this.updateUserMeInfo({
                                        ...this.localUser,
                                        is_verify: false
                                    })
                                } catch (error) {
                                    this.handleApiErrors(this.error, error)
                                }
							},
							reject: () => {
								this.name = this.originalName
								this.pageName = this.originalName
							}
						});
					} else {
						await saveSettings()
					}
				} else {
					await saveSettings()
				}
			} catch (error) {
				this.handleApiErrors(this.error, error)
			} finally {
				this.loadingSave = false
			}
		},
        cancel(){
            this.dialogRef.close()
        },
    }
}
</script>