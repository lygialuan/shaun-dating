<template>
    <div v-if="content" ref="content" class="whitespace-pre-wrap break-word">
        <div v-if="readMore" class="readMore" v-html="shortContentHtml"></div>
        <div v-else v-html="contentHtml" class="readLess"></div>
        <button v-if="canTranslate" @click="toggleTranslateContent" :disabled="isTranslating" class="text-sm text-sub-color dark:text-slate-400">{{ translatedContent ? $t('Hide translation') : $t('See translation') }}</button>
        <ContentHtml v-if="translatedContent" :content="translatedContent" :mentions="mentions" class="border-s-4 border-divider ps-4 ms-2 my-base-1 dark:border-white/10"/>
    </div>
</template>

<script>
import Constant from '@/utility/constant'
import {uuidv4} from '@/utility/index'
import { translateContent } from '@/api/utility'

export default {
    props: {
		content: {
			type: String,
			default: ''
		},
		mentions: {
			type: Array,
			default: null
		},
        limit: {
			type: Number,
			default: null
		},
        contentKey: {
            type: Number,
			default: null
        },
        showReadLess: {
            type: Boolean,
            default: true
        },
        canTranslate: {
            type: Boolean,
            default: false
        },
        subjectType: {
            type: String,
            default: ''
        },
        subjectId: {
            type: [String, Number],
            default: null
        },
        field: {
            type: String,
            default: 'content'
        }
	},
    data() {
        return {
            readMore: false,
            contentHtml : '',
            shortContentHtml: '',
            limitCharacter: 0,
            isTranslating: false,
            translatedContent: ''
        }
    },
    mounted () {
        this.updateContent()
    },
    updated () {
        this.bindEventHtml()
        this.addReadMoreButton()
    },
    watch: {
        content() {
            this.updateContent()
        },
        contentKey(){ // Fix show more bug when story same content
            this.updateContent()
        }
    },
    methods: {
        addReadMoreButton() {
            const moreElement = this.$refs.content?.querySelector('.readMore');
            if (moreElement) {
                if (moreElement.querySelector('.hasMore') == null) {
                    const more = document.createElement('a');
                    more.innerHTML = ' ' + this.$t('See more');
                    more.setAttribute('href', 'javascript:void(0)');
                    more.classList.add('hasMore', 'inline-block');
                    more.addEventListener('click', (event) => {
                        this.readMore = false;
                        event.preventDefault();
                        this.$emit('click_read_more');
                    });
                    moreElement.appendChild(more);
                }

            }

            if(this.showReadLess){
                const lessElement = this.$refs.content?.querySelector('.readLess');
                if(lessElement){
                    if (lessElement.querySelector('.hasLess') == null && (this.contentHtml.length > this.limitCharacter)) {
                        const less = document.createElement('a');
                        less.innerHTML = ' ' + this.$t('See less');
                        less.setAttribute('href', 'javascript:void(0)');
                        less.classList.add('hasLess', 'inline-block');
                        less.addEventListener('click', (event) => {
                            this.readMore = true;
                            event.preventDefault();
                            this.$emit('click_read_less');
                        });
                        lessElement.appendChild(less);
                    }
                }
            }
        },
        renderFullContent(){
            const LIMIT = this.limit || Constant.LIMIT_CHARACTER;
            var result = this.content
            var userMentions = this.mentions
            var self = this
            this.limitCharacter = this.limit || Constant.LIMIT_CHARACTER;

            //link 
            var tmp_links = [];
            if (result) {
                var links = result.match(Constant.LINK_REGEX)
                if (links && links.length > 0)  {
                    links.map((link, key) => {
                        key = uuidv4()
                        var link_result = '<a class="break-word external-link" target="_blank" href="' + link + '">' + link + '</a>'
                        tmp_links[key] = link.replace(link, link_result)
                        result = result.replace(link, key)
                        let positionLink = this.content.indexOf(link);
                        if(positionLink < LIMIT){
                            self.limitCharacter += link_result.length - link.length;
                        }
                    });                    
                }            
            }

            //mention
            var tmp_mentions = [];
            var mentions = result.match(Constant.MENTION_REGEX);
            if (mentions && mentions.length > 0) {                
                mentions.map((mention ,key) => {
                    var user = window._.find(userMentions, {user_name: mention.replace('@', '')});
                    if (user ) {
                        var profileUrl = self.$router.resolve({
                            name: 'profile',
                            params: {user_name: mention.replace('@', '')}                       
                        })
                        key = uuidv4()
                        var mention_result = '<a class="inline-block internal-link break-word" data-internal_href="'+profileUrl.fullPath+'" href="' + profileUrl.href + '">' + user.name + '</a>'
                        tmp_mentions[key] = mention.replace(mention, mention_result)
                        result = result.replace(mention, key)
                        let positionMention = this.content.indexOf(mention);
                        if(positionMention < LIMIT){
                            self.limitCharacter += mention_result.length - user.name.length
                        }
                    }
                });
            }

            //hashtag
            var tmp_hashtags = [];
            if (result) {
                var hashtags = result.match(Constant.HASHTAG_REGEX);
                if (hashtags && hashtags.length > 0) {
                    hashtags.map((hashtag ,key) => {
                        var hashtagUrl = self.$router.resolve({
                            name: 'search',
                            params: {'search_type': 'hashtag', 'type' : 'post'},
                            query: {
                                q: hashtag.replace('#', '')
                            }
                        })
                        key = uuidv4()
                        var hashtag_result = '<a class="inline-block internal-link" data-internal_href="'+hashtagUrl.fullPath+'" href="' + hashtagUrl.href + '">' + hashtag + '</a>';
                        tmp_hashtags[key] = hashtag.replace(hashtag, hashtag_result)
                        result = result.replace(hashtag, key)
                        let positionHashtag = this.content.indexOf(hashtag);
                        if(positionHashtag < LIMIT){
                            self.limitCharacter += hashtag_result.length - hashtag.length
                        }
                    });                   
                }
            }
            window._.forIn(tmp_links, function(tmp_link, key) {
                result = result.replace(key, tmp_link)
            });
            window._.forIn(tmp_hashtags, function(tmp_hashtag, key) {
                result = result.replace(key, tmp_hashtag)
            });
            window._.forIn(tmp_mentions, function(tmp_mention, key) {
                result = result.replace(key, tmp_mention)
            });
            return result
        },
        renderShortContent(){
            return this.contentHtml.length > this.limitCharacter
                ? this.contentHtml.slice(0, this.limitCharacter).trim() + ' ... '
                : this.contentHtml
        },
        bindEventHtml() {
            var links = this.$refs.content?.querySelectorAll('a') || []
            if(links.length){
                for (let i = 0; i < links.length; i++) {
                    links[i].addEventListener('click', (event) => {
                        var target = event.target
                        var link = target.getAttribute('data-internal_href')
                        if (target.classList.contains('external-link')) {
                            return
                        } else {
                            this.$router.push(link)
                        }
                        event.preventDefault()
                    })
                }
            }
        },
        updateContent(){
            this.contentHtml = this.renderFullContent(),
            this.shortContentHtml = this.renderShortContent(),
            this.readMore = this.contentHtml.length > this.limitCharacter
        },
        decodeHtmlEntities(encodedString) {
            const txt = document.createElement('textarea');
            txt.innerHTML = encodedString;
            return txt.value;
        },
        async toggleTranslateContent(){
            if(this.translatedContent){
                this.translatedContent = ''
            } else {
                if (this.isTranslating) return;
                this.isTranslating = true
                try {
                    const response = await translateContent({
                        subject_type: this.subjectType,
                        subject_id: this.subjectId,
                        field: this.field
                    })
                    this.translatedContent = this.decodeHtmlEntities(response.content)
                } catch (error) {
                    this.showError(error.error)
                } finally {
                    this.isTranslating = false
                }
            }
        }
    },
    emits: ['click_read_more', 'click_read_less']
}
</script>