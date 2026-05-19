<template>
	<div class="h-full">
		<div class="flex h-full">
			<div ref="parentContent" class="flex-1 bg-gray-300 lg:rounded-base-lg bg-cover bg-center relative">
				<span class="absolute top-3 left-3 right-3">
					<div class="flex gap-2 justify-between items-center">
						<div class="flex gap-2 relative z-10">
							<button class="flex justify-center items-center bg-white shadow-md h-8 w-8 rounded-full dark:bg-slate-800 rtl:rotate-180" @click="closeCreateStoryModal()">
								<BaseIcon name="arrow_left" />                  
							</button>	
							<button class="flex justify-center items-center bg-white shadow-md h-8 w-8 rounded-full dark:bg-slate-800" @click="toggleSongsList()">
								<BaseIcon name="music_notes" />                
							</button>
							<button class="flex justify-center items-center bg-white shadow-md h-8 w-8 rounded-full dark:bg-slate-800" @click="toggleAddTextarea()">
								<BaseIcon name="character"/>                  
							</button>	
						</div>
						<BaseButton :loading="loading" @click="postStory()" class="relative z-10">{{ $t('Post Story') }}</BaseButton>
					</div>
					<span v-if="selected_song" class="bg-black text-white px-2 py-1 text-xs rounded-lg inline-flex items-center gap-1 relative mt-4 z-10">
						<BaseIcon name="music_note" size="16" />
						{{ selected_song.name }}
						<button @click="removeSong" class="absolute -top-2 -end-2 bg-red-600 text-white rounded-full">
							<BaseIcon name="close" size="16" />
						</button>
					</span>
				</span>
				<div v-if="showAddTextarea" class="absolute top-14 left-3 right-3 z-10">
					<div class="bg-white rounded-base-lg p-base-2 dark:bg-slate-800">
						<div class="relative mb-base-1">
							<BaseTextarea :maxlength="1024" v-model="content" :placeholder="$t('Enter Text')" autofocus classInput="!pe-10" @input="doActionWithText('edit')" /> 
							<span class="absolute bottom-3 end-3"><BaseColorPicker v-model="color" @change="doActionWithText('color')"/></span>				
						</div>
						<div class="flex gap-1 justify-end">
							<BaseButton v-if="focusTextBox" type="danger" @click="doActionWithText('remove')">{{ $t('Remove') }}</BaseButton>	
							<BaseButton v-else @click="addTextBox(content)">{{ $t('Add') }}</BaseButton>
						</div>
					</div>
				</div>
				<Songs v-if="showSongsList" class="absolute top-14 left-3 right-3 z-10" v-click-outside="toggleSongsList" @select_song="selectSong"/>
				<div class="absolute inset-0 w-full">
					<canvas ref="canvasRef"></canvas>
				</div>
			</div>		
		</div>
	</div>
	<CloseButton @click="closeCreateStoryModal()" />
</template>

<script>
import { mapState } from 'pinia'
import { fabric } from "fabric"
import { storeStory } from '@/api/stories'
import BaseButton from "@/components/inputs/BaseButton.vue"
import BaseIcon from '@/components/icons/BaseIcon.vue'
import BaseColorPicker from '@/components/inputs/BaseColorPicker.vue'
import BaseTextarea from '@/components/inputs/BaseTextarea.vue'
import Songs from '@/components/stories/Songs.vue'
import { useAppStore } from '../../store/app'
import constant from '@/utility/constant'
import CloseButton from '@/components/utilities/CloseButton.vue'
var canvas = null

export default {
	components: { BaseButton, BaseIcon, BaseColorPicker, BaseTextarea, Songs, CloseButton },
	inject: ['dialogRef'],
	data() {
		return {
			type: 'photo',
			content: null,
			color: '#ffffff',
			image: this.dialogRef.data.imageData,
			focusTextBox: false,
			selected_song: null,
			showSongsList: false,
			showAddTextarea: false,
			loading: false
		}
	},
	watch: {
        '$route'(){
            this.dialogRef.close()
        }
    },
    mounted(){
		var width = this.$refs.parentContent.clientWidth;
		var height = this.$refs.parentContent.clientHeight;
		canvas = new fabric.Canvas(this.$refs.canvasRef, {
			isDrawingMode: false,
			preserveObjectStacking: true,
			width: width,
            height: height
		})
		const _self = this
		canvas.on('mouse:dblclick', function() {
			const activeObj = canvas.getActiveObject()
			if (activeObj) {
				if (activeObj.get('type') == 'textbox') {
					_self.focusInTextBox()
				}
			}
		})
		canvas.on('mouse:up', function() {
			const activeObj = canvas.getActiveObject()
			if (activeObj) {
				if (activeObj.get('type') != 'textbox') {
					_self.focusOutTextBox()
				}
				if (activeObj.get('type') == 'textbox') {
					_self.content = activeObj.text
					_self.color = activeObj.fill
					_self.focusInTextBox()
				}
			}else{
				_self.focusOutTextBox()
			}
		})
		this.addImage()
    },
	computed:{
        ...mapState(useAppStore, ['isMobile'])
    },
	methods: {
		addImage() {
			var image = new Image();
			image.src = this.image;
			const _self = this
			image.onload = function () {
				var selectable = true
				if(_self.isMobile){
					selectable = false
				}
				var img = new fabric.Image(image, {
					angle: 0,
					left: 0,
					top: 0,
					selectable: selectable,
					cornerSize: 12,
					transparentCorners: false
				});

				var width = canvas.getWidth()
				var height = canvas.getHeight();
				const scaleFactor = Math.min(
					Math.min(width / img.width),
					Math.min(height / img.height)
				);
				img.scale(scaleFactor);

				img.set({
					top: Math.max(height - img.height * scaleFactor, 0)/2,
					left: Math.max(width - img.width * scaleFactor, 0)/2,
				});
				canvas.add(img)
				if(! _self.isMobile){
					canvas.setActiveObject(img)
				}
				canvas.renderAll();
			}
		},
		DataURIToBlob(dataURI) {
			const splitDataURI = dataURI.split(',')
			const byteString = splitDataURI[0].indexOf('base64') >= 0 ? atob(splitDataURI[1]) : decodeURI(splitDataURI[1])
			const mimeString = splitDataURI[0].split(':')[1].split(';')[0]

			const ia = new Uint8Array(byteString.length)
			for (let i = 0; i < byteString.length; i++)
				ia[i] = byteString.charCodeAt(i)

			return new Blob([ia], { type: mimeString })
		},
		addTextBox(content) {
			var width = this.$refs.parentContent.clientWidth;

			var textBox = new fabric.Textbox(content, {
				top: width,
				left: 100,
				width: width - 200,
				fontSize: 26,
				textAlign: 'center', 
				cornerSize: 12,
				editable: false
			})
			canvas.add(textBox)
			canvas.setActiveObject(textBox)
			textBox.enterEditing()
			this.focusInTextBox()
		},
		doActionWithText(type){
			const activeObj = canvas.getActiveObject()
			if (activeObj) {
				if (activeObj.get('type') == 'textbox') {
					switch (type) {
						case 'color':
							activeObj.set("fill", this.color);
							break;
						case 'edit':
							activeObj.set("text", this.content);
							break;
						case 'remove':
							canvas.remove(activeObj)
							this.focusOutTextBox()
							break;
						default:
							break;
					}
					canvas.renderAll();
				}
			}
		},
		focusInTextBox(){
			this.focusTextBox = true
			this.showAddTextarea = true
			const activeObj = canvas.getActiveObject()
			if (activeObj) {
				if (activeObj.get('type') == 'textbox') {
					activeObj.set("fill", this.color);
					canvas.renderAll();
				}
			}
		},
		focusOutTextBox(){
			this.focusTextBox = false
			this.showAddTextarea = false
			this.content = null
			this.color = '#ffffff'
		},
		toggleSongsList(){
			this.showSongsList = !this.showSongsList;
			this.showAddTextarea = false
		},
		selectSong(song){
			this.selected_song = song
			this.toggleSongsList()
		},
		removeSong(){
			this.selected_song = null
		},
		async postStory(){
			var canvasTmp = document.createElement('canvas')
			var ctx = canvasTmp.getContext("2d")			
			canvasTmp.width = constant.STORY_WIDTH
			canvasTmp.height = constant.STORY_HEIGHT
			ctx.fillStyle = constant.STORY_BACKGROUND
			ctx.fillRect(0, 0, canvasTmp.width, canvasTmp.height);
			const base64 = canvas.toDataURL({
				type : 'image/jpeg'
			});
			var image = new Image();
			var self = this
			image.onload = function() {
				// get the scale
				// it is the min of the 2 ratios
				var scale_factor
				if (window.innerHeight / window.innerWidth > 16 / 9) {
					scale_factor = Math.max(canvasTmp.width / image.width, canvasTmp.height / image.height);
				} else {
					scale_factor = Math.min(canvasTmp.width / image.width, canvasTmp.height / image.height);
				}
				
				
				// Lets get the new width and height based on the scale factor
				let newWidth = image.width * scale_factor;
				let newHeight = image.height * scale_factor;
					
				// get the top left position of the image
				// in order to center the image within the canvas
				let x = (canvasTmp.width / 2) - (newWidth / 2);
				let y = (canvasTmp.height / 2) - (newHeight / 2);
				
				// When drawing the image, we have to scale down the image
				// width and height in order to fit within the canvas
				ctx.drawImage(image, x, y, newWidth, newHeight);
				canvasTmp.toBlob(async blob => {
					let file = new File([blob], "fileName.jpeg", { type: "image/jpeg" })	
					const formData = new FormData();
					formData.append('type', self.type);
					formData.append('song_id', self.selected_song ? self.selected_song.id : 0);
					formData.append('photo', file);

					if (self.loading) {
						return
					}
					self.loading = true

					try {
						await storeStory(formData)
						self.showSuccess(self.$t('Your story has been posted.'))
						self.dialogRef.close()

						self.$router.push({'name' : 'home'})
					} catch (error) {
						if (error.error.code == constant.RESPONSE_CODE_MEMBERSHIP_PERMISSION) {
							self.showPermissionPopup('story', error.error.message);
						} else {
							self.showError(error.error)
						}
					} finally {
						self.loading = false
					}
				})
			};
			image.src = base64
			
		},
		closeCreateStoryModal(){
            this.dialogRef.close()
        },
		toggleAddTextarea(){
			this.showAddTextarea = !this.showAddTextarea;
		}
	}
}
</script>