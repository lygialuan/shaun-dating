<template>
	<div class="bg-input-background-color border border-input-border-color text-input-text-color px-base-2 py-[0.375rem] rounded-base w-full dark:bg-slate-800 dark:border-white/10 dark:text-white" :class="{'!border-invalid-color': error}" v-bind="$attrs">
		<div class="relative leading-none ">
			<textarea ref="textarea" v-model="content" :placeholder="placeholder" :rows="rows" :maxlength="maxlength" :autofocus="autofocus" :disabled="disabled" class="bg-transparent text-base leading-normal w-full outline-none resize-none scrollbar-hidden overflow-hidden" :class="{'pe-base-2': maxRows}" :style="maxRows ? {maxHeight: maxHeightStyle} : ''" @paste="onPasteContent($event)" @focus="handleFocusTextarea($event)" @blur="handleBlurTextarea($event)" />
			<div v-if="maxRows" ref="yBar" @mousedown="onYBarMouseDown" class="absolute end-0 w-base-1 bg-scrollbar-thumb rounded-lg dark:bg-scrollbar-thumb-dark"></div>
		</div>
		<slot></slot>
	</div>
	<small v-if="error" class="p-error">{{error}}</small>
	<Teleport to="body">
		<div ref="dropdownMention" class="dropdown-menu-box-mention overflow-auto p-2 z-[9998] flex flex-col max-w-[18rem] max-h-48 bg-white shadow-2xl rounded-lg fixed border dark:bg-slate-800 dark:border-white/10 scrollbar-thin" v-if="isShown" v-click-outside="closeMentionBox"
		:style="caretPosition ? {
			left: `${caretPosition.left}`,
			right: `${caretPosition.right}`,
			top: `${caretPosition.top}`,
			bottom: `${caretPosition.bottom}`,
			width: `${caretPosition.width}`,
		} : {}">
			<template v-if="userMentionList.length > 0">		
				<div v-for="(userMentionItem, index) in userMentionList" class="flex items-center p-base-1 cursor-pointer rounded-md" :class="{'mention-selected': selectedIndex === index}" :key="index" :id="`mention_${index}`" @mouseover="selectedIndex = index" @mousedown="applyMention($event, userMentionItem.user_name)">
					<Avatar :user="userMentionItem" :size="50" :activePopover="false"/>
					<div class="flex-1 ms-base-2 truncate">
						<UserName :user="userMentionItem" :activePopover="false" class="dropdown-menu-box-mention-title text-base-sm font-bold" />
						<div class="dropdown-menu-box-mention-sub-title text-xs text-sub-color dark:text-slate-400 truncate">{{ mentionChar + userMentionItem.user_name }}</div>
					</div>
				</div>
			</template>
			<template v-if="hashtagMentionList.length > 0">			
				<div v-for="(hashtagMentionItem, index) in hashtagMentionList" class="flex items-center p-base-1 cursor-pointer rounded-md" :class="{'mention-selected': selectedIndex === index}" :key="index" :id="`mention_${index}`" @mouseover="selectedIndex = index" @mousedown="applyMention($event, hashtagMentionItem.name)">
					<div>
						<div class="dropdown-menu-box-mention-title text-base-sm font-bold break-word">{{hashtagChar + hashtagMentionItem.name}}</div>
						<div class="dropdown-menu-box-mention-sub-title text-xs text-sub-color dark:text-slate-400 break-word">{{ $filters.numberShortener(hashtagMentionItem.post_count, $t('[number] post'), $t('[number] posts')) }}</div>
					</div>
				</div>
			</template>
		</div>
	</Teleport>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { getMentionUserList } from '@/api/user'
import { getMentionHashtagsList } from '@/api/hashtag'
import { useAuthStore } from '@/store/auth'
import { useAppStore } from '@/store/app'
import { useDraftStore } from '@/store/draft'
import Constant from '@/utility/constant'
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'
var typingTimer = null

export default {
	components: { Avatar, UserName },
    data(){
        return{
            isShown: false,
            userMentionList: [],
			hashtagMentionList: [],
			content: this.modelValue,
			searchText: '',
			caretPosition: null,
			position: 'bottom',
			mentionChar: Constant.MENTION,
			hashtagChar: Constant.HASHTAG,
			cancelKeyUp: null,
			selectedIndex: 0,
			lastPageY: null,
			documentMouseUpListener: null,
			documentMouseMoveListener: null,
			maxHeightStyle: 0
        }
    },
	props: {
		modelValue: {
            type: String || Number,
            default: null
        },
        placeholder: {
            type: String,
            default: ''
        },
		rows: {
            type: [String, Number],
            default: 3
        },
		autofocus: {
            type: Boolean,
			default: false
        },
		disabled: {
			type: Boolean,
			default: false
		},
		error: {
			type: String,
			default: null
		},
		maxlength: {
			type: [String, Number],
			default: null
		},
		maxRows: {
            type: [String, Number],
            default: 0
        },
		draftId: {
			type: String,
			default: ''
		}

    },
	watch: {
        modelValue(newValue){
            this.content = newValue
			newValue ? this.setDraft(this.draftId, newValue) : this.removeDraft(this.draftId)
        }
    },
	mounted() {
		if (this.$refs.textarea.offsetParent) {
            this.resize();
			this.moveBar();
        }
		this.attach()
		window.addEventListener('scroll', this.onScroll)
		if(this.draftId){
			const draftContent = this.getDraft(this.draftId)
			if(draftContent){
				this.setContent(draftContent)
			}
		}
    },
	unmounted(){
		this.detach()
	},
	updated() {
        if (this.$refs.textarea.offsetParent) {
            this.resize();
			this.moveBar();
        }
    },
	computed:{
		...mapState(useAuthStore, ['user']),
		...mapState(useAppStore, ['isMobile']),
		hasEnterListener() {
			return !!(this.$.vnode && this.$.vnode.props && this.$.vnode.props.onEnter);
		}
	},
    methods:{
		...mapActions(useDraftStore, ['getDraft', 'setDraft', 'removeDraft']),
		attach () {
			if (this.$refs.textarea) {
				this.$refs.textarea.addEventListener('input', this.onInput)
				this.$refs.textarea.addEventListener('keydown', this.onKeyDown)
				this.$refs.textarea.addEventListener('keyup', this.onKeyUp)
				this.$refs.textarea.addEventListener('scroll', this.onScroll)
				this.$refs.textarea.addEventListener('blur', this.onBlur)
			}
		},
		detach () {
			if (this.$refs.textarea) {
				this.$refs.textarea.removeEventListener('input', this.onInput)
				this.$refs.textarea.removeEventListener('keydown', this.onKeyDown)
				this.$refs.textarea.removeEventListener('keyup', this.onKeyUp)
				this.$refs.textarea.removeEventListener('scroll', this.onScroll)
				this.$refs.textarea.removeEventListener('blur', this.onBlur)
			}
		},
		moveBar(){
			let totalHeight = this.$refs.textarea?.scrollHeight;
			let ownHeight = this.$refs.textarea?.clientHeight;

			this.scrollYRatio = ownHeight / totalHeight;
			if(this.$refs.yBar){
				this.$refs.yBar.style.cssText = 'height: 0';
				if(this.scrollYRatio < 1){
					this.$refs.yBar.style.cssText = 'height:' + (this.scrollYRatio.toFixed(2) * 100) + '%; top: calc(' + (this.$refs.textarea.scrollTop / totalHeight) * 100 + '% )';
				}
			}

		},
		bindDocumentMouseListeners() {
            if (!this.documentMouseMoveListener) {
                this.documentMouseMoveListener = (e) => {
                    this.onDocumentMouseMove(e);
                };

                document.addEventListener('mousemove', this.documentMouseMoveListener);
            }

            if (!this.documentMouseUpListener) {
                this.documentMouseUpListener = (e) => {
                    this.onDocumentMouseUp(e);
                };

                document.addEventListener('mouseup', this.documentMouseUpListener);
            }
        },
		unbindDocumentMouseListeners() {
            if (this.documentMouseMoveListener) {
                document.removeEventListener('mousemove', this.documentMouseMoveListener);
                this.documentMouseMoveListener = null;
            }

            if (this.documentMouseUpListener) {
                document.removeEventListener('mouseup', this.documentMouseUpListener);
                this.documentMouseUpListener = null;
            }
        },
		onDocumentMouseMove(e) {
            this.onMouseMoveForYBar(e);
        },
		onDocumentMouseUp() {
            this.unbindDocumentMouseListeners();
        },
		onYBarMouseDown(e){
			this.bindDocumentMouseListeners();
			e.preventDefault();
			this.lastPageY = e.pageY;
		},
		requestAnimationFrame(f) {
            let frame = window.requestAnimationFrame || this.timeoutFrame;

            return frame(f);
        },
		onMouseMoveForYBar(e) {
            let deltaY = e.pageY - this.lastPageY;

            this.lastPageY = e.pageY;

            this.frame = this.requestAnimationFrame(() => {
                this.$refs.textarea.scrollTop += deltaY / this.scrollYRatio;
            });
        },
        async suggestUsersMention(text){
			try {
				const response = await getMentionUserList(text);
				if(response.length > 0){
					this.openMentionBox();
					this.userMentionList = response;
					this.hashtagMentionList = [];
				}else{
					this.closeMentionBox();
				}
			} catch (error) {
				this.showError(error.message)
			}  
			
		},
		async suggestHashtagsMention(text){
			try {
				const response = await getMentionHashtagsList(text);
				if(response.length > 0){
					this.openMentionBox();
					this.hashtagMentionList = response;
					this.userMentionList = [];
				}else{
					this.closeMentionBox();
				}
			} catch (error) {
				this.showError(error.message)
			}  
			
		},
		getLastSearchText(caretIndex, keyIndex) {
			if (keyIndex !== -1) {
				const text = this.content.substring(keyIndex + 1, caretIndex)
				// If there is a space we close the menu
				if (!/\s/.test(text)) {
					return text
				}
			}
			return null
		},
		checkMention(index){
			// check mention user
			var keyIndexUser = this.content.lastIndexOf(this.mentionChar, index - 1);
			const textUser = this.getLastSearchText(index, keyIndexUser)
			if (!(keyIndexUser < 1 || /\s/.test(this.content[keyIndexUser - 1]))) {
				return false
			}
			if (textUser) {
				this.searchText = textUser;
				typingTimer = setTimeout(() => this.suggestUsersMention(textUser), 500);
			}
		},
		checkHashTag(index) {
			// check mention hashtag
			var keyIndexHashtag = this.content.lastIndexOf(this.hashtagChar, index - 1);
			const textHashtag = this.getLastSearchText(index, keyIndexHashtag)
			if (!(keyIndexHashtag < 1 || /\s/.test(this.content[keyIndexHashtag - 1]))) {
				return false
			}
			if (textHashtag) {
				this.searchText = textHashtag;
				typingTimer = setTimeout(() => this.suggestHashtagsMention(textHashtag), 500);
			}
		},
		onInput(){
			this.emitContentChange();
			this.searchText = '';
			clearTimeout(typingTimer);
			const index = this.$refs.textarea.selectionStart
			if (index >= 0) {
				this.checkMention(index)
				this.checkHashTag(index)
			}
			this.closeMentionBox();
		},
		onBlur(){
			this.closeMentionBox();
		},
		onKeyDown (e){
			if(this.isShown){
				if (e.key === 'ArrowDown') {
					this.selectedIndex++
					if(this.userMentionList.length > 0){
						if (this.selectedIndex >= this.userMentionList.length) {
							this.selectedIndex = 0 
						}
					}else if(this.hashtagMentionList.length > 0){
						if (this.selectedIndex >= this.hashtagMentionList.length) {
							this.selectedIndex = 0 
						}
					}
					this.cancelEvent(e)
					this.scrollInView()
				}
				if (e.key === 'ArrowUp') {
					this.selectedIndex--
					if (this.selectedIndex < 0) {
						if(this.userMentionList.length > 0){
							this.selectedIndex = this.userMentionList.length - 1
						}else if(this.hashtagMentionList.length > 0){
							this.selectedIndex = this.hashtagMentionList.length - 1
						}
					}
					this.cancelEvent(e)
					this.scrollInView()
				}
				if (e.key === 'Escape') {
					this.closeMentionBox()
					this.cancelEvent(e)
				}
				if ((e.key === 'Enter' || e.key === 'Tab') && this.userMentionList.length > 0) {
					this.applyMention(e, this.userMentionList[this.selectedIndex].user_name)
					this.cancelEvent(e)
				}
				else if((e.key === 'Enter' || e.key === 'Tab') && this.hashtagMentionList.length > 0) {
					this.applyMention(e, this.hashtagMentionList[this.selectedIndex].name)
					this.cancelEvent(e)
				}
			} else {
				if (e.key === 'Enter') {
					if (this.isMobile) {
						return;
					}
					if(!e.shiftKey){
						if(this.hasEnterListener){
							e.preventDefault()
						}
						this.$emit('enter', e)
					}
				}
			}
		},
		onKeyUp (e){
			if (this.cancelKeyUp && e.key === this.cancelKeyUp) {
				this.cancelEvent(e)
			}
			this.cancelKeyUp = null
		},
		onScroll () {
			this.updateCaretPosition()
			this.moveBar()
		},
		cancelEvent (e) {
			e.preventDefault()
			e.stopPropagation()
			this.cancelKeyUp = e.key
		},
		scrollInView() {
            const element = document.getElementById(`mention_${this.selectedIndex}`)
            if (element) {
                element.scrollIntoView && element.scrollIntoView({ block: 'nearest', inline: 'start' });
            } 
        },
		applyMention(e, selectedSuggestionText){
			e.preventDefault();
			this.closeMentionBox();
			const index = this.$refs.textarea.selectionStart
			this.content = this.content.slice(0, index - this.searchText.length) + selectedSuggestionText + ' ' + this.content.slice(index - this.searchText.length + this.searchText.length);
			setTimeout(() => {
				const curPos = index - this.searchText.length + selectedSuggestionText.length + 1;
				this.$refs.textarea.setSelectionRange(curPos, curPos);
				this.focus();
			});
			this.emitContentChange();
		},
        openMentionBox(){
			if (!this.isShown) this.isShown = true;
			this.updateCaretPosition()
		},
		closeMentionBox() {
			if (this.isShown) this.isShown = false;
			this.selectedIndex = 0
			setTimeout(() => {
				this.userMentionList = []
				this.hashtagMentionList = []
			});
		},
		addContent(content){
			const el = this.$refs.textarea	
			let curPos = el.selectionStart;
            this.content = this.content ? this.content.slice(0, curPos) + content + this.content.slice(curPos) : content;
			this.$nextTick(() => {
				curPos = curPos + content.length;
				el.setSelectionRange(curPos, curPos);
				this.focus();
			});	
			this.emitContentChange();
			this.resize();
		},
		setContent(content) {
			this.content = content
			this.focus()
			this.emitContentChange()
		},
		updateCaretPosition () {
			if(this.isShown){
				const rect = this.getCaretPosition(this.$refs.textarea, this.$refs.textarea.selectionStart)
				const inputRect = this.$refs.textarea.getBoundingClientRect()
				const dropdownBoxWidth = 288
				const dropdownBoxHeight = 192

				var leftOffset = 'unset',
					rightOffset = 'unset',
					topOffset = 'unset',
					bottomOffset = 'unset',
					widthElement = '100%'
	
				// set X coordinate
				if(window.innerWidth - rect.left - inputRect.left > dropdownBoxWidth){
					leftOffset = rect.left + inputRect.left + 'px'
				}else{
					rightOffset = inputRect.left + 'px'
					widthElement = (window.innerWidth - inputRect.left) + 'px'
				}
				
				// set Y coordinate
				if(window.innerHeight - (rect.top - this.$refs.textarea.scrollTop + inputRect.top + rect.height) > dropdownBoxHeight){
					bottomOffset = 'unset'
					topOffset = rect.top - this.$refs.textarea.scrollTop + inputRect.top + rect.height + 'px'
				}else{
					topOffset = 'unset'
					bottomOffset = window.innerHeight - inputRect.top - inputRect.height + rect.height + 'px'
				}
	
				this.caretPosition = {
					left: leftOffset,
					right: rightOffset,
					top: topOffset,
					bottom: bottomOffset,
					width: widthElement
				}
			}
		},
		emitContentChange(){
			this.$emit('update:modelValue', this.content)
		},
		onPasteContent(event){
			this.$emit('paste', event)
		},
		getCaretPosition(element, position, options) {
			var properties = [
				'direction',  // RTL support
				'boxSizing',
				'width',  // on Chrome and IE, exclude the scrollbar, so the mirror div wraps exactly as the textarea does
				'height',
				'overflowX',
				'overflowY',  // copy the scrollbar for IE

				'borderTopWidth',
				'borderRightWidth',
				'borderBottomWidth',
				'borderLeftWidth',
				'borderStyle',

				'paddingTop',
				'paddingRight',
				'paddingBottom',
				'paddingLeft',

				// https://developer.mozilla.org/en-US/docs/Web/CSS/font
				'fontStyle',
				'fontVariant',
				'fontWeight',
				'fontStretch',
				'fontSize',
				'fontSizeAdjust',
				'lineHeight',
				'fontFamily',

				'textAlign',
				'textTransform',
				'textIndent',
				'textDecoration',  // might not make a difference, but better be safe

				'letterSpacing',
				'wordSpacing',

				'tabSize',
				'MozTabSize'

			];		
			var isBrowser = (typeof window !== 'undefined');
			var isFirefox = (isBrowser && window.mozInnerScreenX != null);
			if (!isBrowser) {
				throw new Error('textarea-caret-position#getCaretCoordinates should only be called in a browser');
			}

			var debug = options && options.debug || false;
			if (debug) {
				var el = document.querySelector('#input-textarea-caret-position-mirror-div');
				if (el) el.parentNode.removeChild(el);
			}

			// The mirror div will replicate the textarea's style
			var div = document.createElement('div');
			div.id = 'input-textarea-caret-position-mirror-div';
			document.body.appendChild(div);

			var style = div.style;
			var computed = window.getComputedStyle ? window.getComputedStyle(element) : element.currentStyle;  // currentStyle for IE < 9
			var isInput = element.nodeName === 'INPUT';

			// Default textarea styles
			style.whiteSpace = 'pre-wrap';
			if (!isInput)
				style.wordWrap = 'break-word';  // only for textarea-s

			// Position off-screen
			style.position = 'absolute';  // required to return coordinates properly
			if (!debug)
				style.visibility = 'hidden';  // not 'display: none' because we want rendering

			// Transfer the element's properties to the div
			properties.forEach(function (prop) {
				if (isInput && prop === 'lineHeight') {
				// Special case for <input>s because text is rendered centered and line height may be != height
				style.lineHeight = computed.height;
				} else {
				style[prop] = computed[prop];
				}
			});

			if (isFirefox) {
				// Firefox lies about the overflow property for textareas: https://bugzilla.mozilla.org/show_bug.cgi?id=984275
				if (element.scrollHeight > parseInt(computed.height))
				style.overflowY = 'scroll';
			} else {
				style.overflow = 'hidden';  // for Chrome to not render a scrollbar; IE keeps overflowY = 'scroll'
			}

			div.textContent = element.value.substring(0, position);
			if (isInput)
				div.textContent = div.textContent.replace(/\s/g, '\u00a0');

			var span = document.createElement('span');
			span.textContent = element.value.substring(position) || '';
			div.appendChild(span);
			var coordinates = {
				top: span.offsetTop + parseInt(computed['borderTopWidth']),
				left: span.offsetLeft + parseInt(computed['borderLeftWidth']),
				height: parseInt(computed['lineHeight'])
			};

			if (debug) {
				span.style.backgroundColor = '#aaa';
			} else {
				document.body.removeChild(div);
			}

			return coordinates;
		},
		handleFocusTextarea(event){
			this.$emit('focus', event)
		},
		handleBlurTextarea(event){
			this.$emit('blur', event)
		},
		focus() {
			this.$refs.textarea.focus()
		},
		resize() {
			const textAreaStyle = window.getComputedStyle(this.$refs.textarea);

			this.$refs.textarea.style.height = 'auto';
			this.$refs.textarea.style.height = `calc(${textAreaStyle.borderTopWidth} + ${textAreaStyle.borderBottomWidth} + ${this.$refs.textarea.scrollHeight}px)`;

			if (parseInt(textAreaStyle.height) >= parseInt(textAreaStyle.maxHeight)) {
				this.$refs.textarea.style.overflowY = 'scroll';
				this.$refs.textarea.style.height = textAreaStyle.maxHeight;
			}else{
				this.$refs.textarea.style.overflowY = 'hidden';
			}

			let maxHeight = textAreaStyle.maxHeight;
			if (this.maxRows) {
				const lineHeight = parseInt(textAreaStyle.lineHeight) || parseInt(textAreaStyle.fontSize) * 1.2;
				const maxHeightInPx = lineHeight * this.maxRows + parseInt(textAreaStyle.borderTopWidth) + parseInt(textAreaStyle.borderBottomWidth) + parseInt(textAreaStyle.paddingTop) + parseInt(textAreaStyle.paddingBottom);
				maxHeight = `${maxHeightInPx}px`;
			}
			this.maxHeightStyle = maxHeight
        }
    },
	emits: ['update:modelValue', 'paste', 'enter', 'focus', 'blur']
}
</script>