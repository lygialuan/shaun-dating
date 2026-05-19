<template>
	<template v-if="appendTo === 'body'">
        <div ref="dropdown_btn" @click="toggleDropdownMenu" role="button" v-bind="$attrs">
            <slot name="dropdown-button">
                <BaseIcon name="more_horiz_outlined" size="20"/>
            </slot>
        </div>
        <Popover class="dropdown-menu-box shadow-sidebar-more rounded-base" :style="{'marginStart': offsetX+'px', 'marginTop': offsetY+'px'}" ref="dropdown_menu" @click="handleClickOverlayPanel" @hide="closeDropdownMenu">        
            <slot name="dropdown-content"></slot>
        </Popover>
    </template>
	<div v-else class="relative">
		<div ref="dropdown_btn" @click="toggleDropdownMenu" role="button">
			<slot name="dropdown-button">
				<BaseIcon name="more_horiz_outlined" size="20"/>
			</slot>
		</div>
		<div
			v-if="isShown"
			ref="dropdown_menu"
			class="dropdown-menu-box bg-white border border-divider p-2 shadow-sidebar-more rounded-md z-10 absolute dark:bg-dark-web-wash dark:border-white/10"
			:class="dropdownBoxClass"
			v-click-outside="closeDropdownMenu"
			@click.stop="handleClickOverlayPanel"
			:style="{
				top: caretPosition.top !== null ? `${caretPosition.top}px` : 'auto',
				right: caretPosition.right !== null ? `${caretPosition.right}px` : 'auto',
				bottom: caretPosition.bottom !== null ? `${caretPosition.bottom}px` : 'auto',
				left: caretPosition.left !== null ? `${caretPosition.left}px` : 'auto',
			}"
		>
			<slot name="dropdown-content"></slot>
		</div>
	</div>
</template>

<script>
import Popover from 'primevue/popover';
import BaseIcon from '@/components/icons/BaseIcon.vue';

export default {
	components: { Popover, BaseIcon },
	data() {
		return {
			isShown: false,
			caretPosition: {
				top: null,
				right: null,
				bottom: null,
				left: null
			},
			measuredWidth: null,
			measuredHeight: null
		}
	},
	props: {
		offsetX: {
            type: Number,
            default: 0
        },
        offsetY: {
            type: Number,
            default: 5
        },
		appendTo: {
            type: String,
            default: 'body'
        },
		closeWhenSelect: {
			type: Boolean,
			default: true
		},
		dropdownBoxClass: {
			type: String,
			default: ''
		}
	},
	methods: {
		measureDropdown() {
			const dropdown = this.$refs.dropdown_menu;
			this.measuredWidth = dropdown.offsetWidth;
			this.measuredHeight = dropdown.offsetHeight;
		},
		toggleDropdownMenu(e){
			if(this.appendTo === 'body'){
				this.$refs.dropdown_menu.toggle(e);
			} else {
				this.isShown = !this.isShown;
				this.$nextTick(() => {
					if (this.isShown && !this.measuredWidth) {
						this.measureDropdown();
					}
					this.updateCaretPosition();
				});
			}
			this.$emit('toggle_menu');
		},
		closeDropdownMenu(e) {
			if(this.appendTo === 'body'){
				this.$refs.dropdown_menu.hide(e);
			} else {
				if (this.$refs.dropdown_btn && e?.target && this.$refs.dropdown_btn.contains(e.target)) {
					return;
				}
				this.isShown = false;
			}
			this.$emit('close_menu');
		},
		updateCaretPosition () {
			const dropdownButtonRect = this.$refs.dropdown_btn.getBoundingClientRect()

			// set X coordinate overlay panel
			const viewportWidth = window.innerWidth
			const buttonRect = dropdownButtonRect

			const spaceRight = viewportWidth - buttonRect.right
			const spaceLeft = buttonRect.left

			if (spaceRight >= this.measuredWidth) {
				this.caretPosition.left = this.offsetX
				this.caretPosition.right = null

			} else if (spaceLeft >= this.measuredWidth) {
				this.caretPosition.right = this.offsetX
				this.caretPosition.left = null
			} else {
				this.caretPosition.right = null
				this.caretPosition.left = null
			}

			// set Y coordinate overlay panel
			if((window.innerHeight - dropdownButtonRect.top) > this.measuredHeight){
				this.caretPosition.top = dropdownButtonRect.height + Number(this.offsetY)
				this.caretPosition.bottom = null
			}else{
				this.caretPosition.bottom = dropdownButtonRect.height + Number(this.offsetY)
				this.caretPosition.top = null
			}
		},
		handleClickOverlayPanel(){
			this.closeWhenSelect && this.closeDropdownMenu()
		},
		open(){
			setTimeout(() => {
				this.$refs.dropdown_btn.click()
			}, 100);
		},
		close(){
			this.closeDropdownMenu()
		}
	},
	emits: ['toggle_menu', 'close_menu']
}
</script>
