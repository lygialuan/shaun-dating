<template>
	<DropdownMenu v-if="!isMobile" ref="emojiDropdown" :appendTo="appendTo" :close-when-select="false" @toggle_menu="openEmoji" @close_menu="closeEmoji">
		<template v-slot:dropdown-button>
			<BaseIcon name="emoji" :size="size" class="emoji-picker" v-tooltip.top="{value: tip, showDelay: 1000}" />
		</template>
		<template v-slot:dropdown-content>
			<div class="emoji-picker-box p-2 space-y-base-1 w-80 h-72 overflow-auto">
				<div class="emoji-picker-box-title flex flex-col" v-for="category in categories" :key="`category_${category}`">
					<span class="text-sub-color font-medium mb-4 dark:text-slate-400">{{ $t(category) }}</span>
					<div class="flex flex-wrap">
						<button class="border-0 text-2xl mb-2 w-2/12" @click="handleEmojiClick($event, emoji)" v-for="(emoji, name, index) in category_emojis(category)" :key="`emoji_${index}`" v-bind:title="name">
							{{ emoji}}
						</button>
					</div>
				</div>
			</div>
		</template>
	</DropdownMenu>
</template>

<script>
import { mapState } from 'pinia'
import data from '@/data/emojis-data.js';
import BaseIcon from '@/components/icons/BaseIcon.vue';
import DropdownMenu from '@/components/utilities/DropdownMenu.vue'
import { useAppStore } from '@/store/app';

export default {
	name: 'EmojiPicker',
	components: {
		BaseIcon, DropdownMenu
	},
	props: {
		appendTo: {
            type: String,
            default: 'body'
        },
		size: {
            type: String,
			default: '24'
        },
		tip: {
            type: String,
            default: ''
        }
	},
	computed: {
		...mapState(useAppStore, ['isMobile']),
		categories() {
			this.$t('Frequently used')
			this.$t('Nature')
			this.$t('Objects')
			this.$t('Places')
			this.$t('Symbols')
			return Object.keys(data);
		},
		category_emojis: () => (category) => {
			return data[category];
		}
	},
	methods: {
		handleEmojiClick(e, emoji){
			e.preventDefault();
			this.$emit('emoji_click', emoji);
		},
		openEmoji(){
			this.$emit('open_emoji');
		},
		closeEmoji() {
			this.$emit('close_emoji');
		},
		open(){
            this.$refs.emojiDropdown?.open()
        },
		close(){
			this.$refs.emojiDropdown?.close()
		}
	},
	emits: ['emoji_click', 'open_emoji', 'close_emoji']
}
</script>
