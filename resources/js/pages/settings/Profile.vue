<template>
	<div v-if="user.is_page" class="space-y-3">
		<div class="space-y-2"> 
			<div><label>{{$t('Page Name')}}</label></div>
			<div>
				<BaseInputText v-model="pageName" :error="error.name"/>
			</div>  
		</div>
		<div class="space-y-2"> 
			<div><label>{{$t('Page Alias')}}</label></div>
			<div>
				<BaseInputText v-model="pageUserName" :error="error.user_name"/>
			</div>  
		</div>
	</div>
	<template v-else>
		<Loading v-if="loadingStatus" />
		<div v-else class="space-y-3">
			<div class="flex flex-col items-center mb-2"> 
				<Avatar :user="user" :activePopover="false" :border="false" :size="120" class="mb-2" />
				<UserName :user="user" />
				<button class="text-primary-color dark:text-dark-primary-color" @click="$refs.avatar.click()">{{$t('Change profile photo')}}</button>
				<input type="file" ref="avatar" @change="uploadAvatar($event)" @click="onInputClick($event)" accept="image/*" class="hidden">
			</div>
			<div class="space-y-2"> 
				<div><label>{{$t('Display Name')}}</label></div>
				<div class="pe-[46px]">
					<BaseInputText v-model="name" :error="error.name"/>
				</div>
			</div>
			<div class="space-y-2"> 
				<div><label>{{$t('Bio')}}</label></div>
				<div class="flex gap-x-4 items-start">
					<div class="flex-1">
						<BaseTextarea v-model="bio" autoResize :error="error.bio"/>
					</div>
					<BaseSelectPrivacy v-model="privacyField['bio']" :options="privaciesList" />
				</div>
			</div>
			<div class="space-y-2"> 
				<div><label>{{$t('About')}}</label></div>
				<div class="flex gap-x-4 items-start">
					<div class="flex-1">
						<BaseTextarea v-model="about" autoResize :error="error.about"/>
					</div>
					<BaseSelectPrivacy v-model="privacyField['about']" :options="privaciesList" />
				</div>
			</div>
			<div class="space-y-2"> 
				<div><label>{{$t('Location')}}</label></div>
				<div class="flex items-start gap-x-4">
					<div class="flex-1">
						<BaseSelectLocation v-model="location" :show-label="false" :error="error"/>
					</div>
					<BaseSelectPrivacy v-model="privacyField['location']" :options="privaciesList" />
				</div>  
			</div>
			<div class="space-y-2"> 
				<div><label>{{$t('Gender')}}</label></div>
				<div class="flex gap-x-4 items-start">
					<div class="flex-1">
						<BaseSelect v-model="gender_id" :options="genders" optionLabel="name" optionValue="id" :error="error.gender_id" />
					</div>
					<BaseSelectPrivacy v-model="privacyField['gender_id']" :options="privaciesList" />
				</div>  
			</div>
			<div class="space-y-2"> 
				<div><label>{{$t('Birthday')}}</label></div>
				<div class="flex gap-x-4 items-start">
					<div class="flex-1">
						<BaseCalendar v-model="birthday" :error="error.birthday"/>
					</div>
					<BaseSelectPrivacy v-model="privacyField['birthday']" :options="privaciesList" />
				</div>  
			</div>
			<div class="space-y-2"> 
				<div><label>{{$t('Timezone')}}</label></div>
				<div class="pe-[46px]">
					<BaseSelect v-model="timezone" :options="timezones" optionLabel="key" optionValue="value" :error="error.timezone" />
				</div>
			</div>
			<div class="space-y-2"> 
				<div><label>{{$t('Link')}}</label></div>
				<div class="flex items-start gap-x-4">
					<div class="flex-1">
						<div v-for="(link, index) in links" :key="index" class="flex items-center gap-base-2 mb-3">
							<BaseInputText class="flex-1" v-model="links[index].title" :placeholder="$t('Title')" />
							<BaseInputText class="flex-2" v-model="links[index].link" :placeholder="$t('URL')" />
							<button v-if="links.length > 1" @click="removeMoreLink(index)"><BaseIcon name="close" class="text-base-red"/></button>
						</div>
						<small v-if="error.links" class="block p-error mb-2">{{error.links}}</small>
						<button class="block text-xs font-bold text-primary-color dark:text-dark-primary-color" @click="addMoreLink">{{$t('Add more link')}}</button>
					</div>
					<BaseSelectPrivacy v-model="privacyField['link']" :options="privaciesList" />
				</div>  
			</div>
			<div class="space-y-2">
				<div class="md:flex-1 md:text-end w-full mb-1"></div>
				<div class="text-sub-color text-xs italic dark:text-default-tertiary">{{ $t("* Field privacy setting will apply for the 'Info' tab in your profile. If it's Only me, it does not show there.") }}</div>
			</div>
		</div>
	</template>
	<BaseButton :loading="loadingSave" @click="saveProfileSettings()" fluid class="mt-3">{{$t('Save')}}</BaseButton>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { defineAsyncComponent } from 'vue'
import { getProfileSettings } from '@/api/setting'
import { checkPopupBodyClass } from '@/utility/index'
import { useAuthStore } from '@/store/auth'
import { useAppStore } from '@/store/app'
import Loading from '@/components/utilities/Loading.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import BaseSelect from '@/components/inputs/BaseSelect.vue'
import BaseTextarea from '@/components/inputs/BaseTextarea.vue'
import BaseCalendar from '@/components/inputs/BaseCalendar.vue'
import BaseSelectLocation from '@/components/inputs/BaseSelectLocation.vue'
import BaseSelectPrivacy from "@/components/inputs/BaseSelectPrivacy.vue"
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'

export default {
	components: { Loading, BaseButton, BaseIcon, BaseInputText, BaseSelect, BaseTextarea, BaseCalendar, BaseSelectLocation, BaseSelectPrivacy, Avatar, UserName },
    data(){
		return {
			loadingStatus: true,
			about: null,
			avatar: null,
			bio: null,
			gender_id: null,
			genders: [],
			links: null,
			name: null,
			timezone: null,
			timezones: [],
			birthday: null,
			error: {
				about: null,
				bio: null,
				gender_id: null,
				links: null,
				location: null,
				name: null,
				user_name: null,
				timezone: null,
				birthday: null,
				country_id: null,
				state_id: null,
				city_id: null,
				zip_code: null,
				address: null
			},
			privacyField: null,
			loadingSave: false,
			pageName: null,
			pageUserName: null,
			location: {
				country_id: null,
				state_id: null,
				city_id: null,
				zip_code: null,
				address: null
			},
			privaciesList: [
				{ icon: 'globe', name: this.$t('Everyone'), value: 1 },
				{ icon: 'users', name: this.$t('My followers'), value: 2 },
				{ icon: 'lock', name: this.$t('Only me'), value: 3 }
			],
			originalName: ''
		}
	},
	computed: {
		...mapState(useAuthStore, ['user']),
		...mapState(useAppStore, ['config'])
	},
	mounted(){
		this.getProfileSettings()
	},
	methods:{
		...mapActions(useAuthStore, ['storeProfileSettings', 'storeProfilePageSettings', 'updateUserMeInfo']),
		async getProfileSettings(){
			if(this.user.is_page){
				this.pageName = this.user.name
				this.pageUserName = this.user.user_name
				this.originalName = this.user.name
			} else {
				try {
					const response = await getProfileSettings()
					this.avatar = response.avatar
					this.name = response.name
					this.bio = response.bio
					this.about = response.about
					this.links = response.links.length ? response.links : [{ title: '', link: '' }]
					this.gender_id = response.gender_id
					this.genders = [{'id': 0, 'name': this.$t('Prefer not to say')}, ...response.genders]
					this.timezone = response.timezone
					this.timezones = window._.map(response.timezones, function(key, value) {
						return { key, value }
					});
					this.birthday = response.birthday
					this.location.country_id = response.country_id
					this.location.state_id = response.state_id
					this.location.city_id = response.city_id
					this.location.zip_code = response.zip_code
					this.location.address = response.address
					this.privacyField = response.privacyField
					this.originalName = response.name
				} catch (error) {
					this.showError(error.error)
				} finally {
					this.loadingStatus = false
				}
			}
		},
		async saveProfileSettings(){
			if (this.loadingSave) {
				return
			}
			this.loadingSave = true
			try {
				const saveSettings = async() => {
					if(this.user.is_page){
						await this.storeProfilePageSettings({
							name: this.pageName,
							user_name: this.pageUserName
						})
					} else {
						// Check if links is only [{title: "", link: ""}]
						let linksToSave = this.links;
						if (
							Array.isArray(this.links) &&
							this.links.length === 1 &&
							!this.links[0].title &&
							!this.links[0].link
						) {
							linksToSave = "";
						}
						await this.storeProfileSettings({
							name: this.name,
							bio: this.bio,
							about: this.about,
							links: linksToSave,
							gender_id: this.gender_id == 0 ? null : this.gender_id,
							timezone: this.timezone,
							birthday: this.formatDateTime(this.birthday),
							country_id: this.location.country_id,
							state_id: this.location.state_id,
							city_id: this.location.city_id,
							zip_code: this.location.zip_code,
							address: this.location.address,
							privacyField: this.privacyField
						})
		
						// Remove empty link input
						if(this.links.filter(element => {if (Object.keys(element).length !== 0) {return true;}return false;}).length > 0){
							this.links = this.links.filter(element => {if (Object.keys(element).length !== 0) {return true;}return false;})
						}
					}	
					this.showSuccess(this.$t('Your changes have been saved.'))
					this.resetErrors(this.error)
				}

				if(this.user.is_verify){
					const isNameChanged = (this.name && this.name !== this.originalName) || (this.pageName && this.pageName !== this.originalName);
					if(isNameChanged){
						this.$confirm.require({
							message: this.$t('You will lose your profile verification badge if you change your name. Do you want to continue?'),
							header: this.$t('Please confirm'),
							acceptLabel: this.$t('Ok'),
							rejectLabel: this.$t('Cancel'),
							accept: async() => {
								await saveSettings()
								this.updateUserMeInfo({
									...this.user,
									is_verify: false
								})
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
		uploadAvatar(event){
			var input = event.target;
			// Ensure that you have a file before attempting to read it
			if (input.files && input.files[0]) {
				// create a new FileReader to read this image and convert to base64 format
				var reader = new FileReader();
				// Define a callback function to run, when FileReader finishes its job
				reader.onload = e => {
					// Note: arrow function used here, so that "this.imageData" refers to the imageData of Vue component
					// Read image as base64 and set to imageData
					// Open modal to crop cover image
					const UploadAvatarModal = defineAsyncComponent(() =>
						import('@/components/modals/UploadAvatarModal.vue')
					)
					this.$dialog.open(UploadAvatarModal, {
						data: {
							imageData: e.target.result
						},
						props:{
							header: this.$t('Crop Avatar'),
							class: 'crop-avatar-modal p-dialog-md',
							modal: true
						},
						onClose: (options) => {
							if(options.data){
								this.avatar = options.data.avatar
							}
							checkPopupBodyClass();
						}
					})
				};
				// Start the reader job - read file as a data url (base64 format)
				reader.readAsDataURL(input.files[0]);

			}
		},
		onInputClick(event){
			event.target.value = null
		},
		addMoreLink(){
			this.error.links = null
			this.links.push({ title: '', link: '' });
		},
		removeMoreLink(id){
			this.links = this.links.filter((link, index) => index != id)

			if (this.links.length == 0) {
				this.links.push({ title: '', link: '' });
			}
		}
    }
}
</script>