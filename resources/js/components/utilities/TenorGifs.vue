<template>
    <DropdownMenu :appendTo="appendTo" @toggle_menu="toggleGifsBox" ref="tenorGifsDropdown" :close-when-select="false">
        <template v-slot:dropdown-button>
            <div v-tooltip.top="{ value: tip, showDelay: 1000 }">
                <slot><BaseIcon name="gif" :size="size"/></slot>
            </div>
        </template>
        <template v-slot:dropdown-content>
            <div class="flex flex-col w-60 h-80">
                <div class="pb-2">
                    <BaseInputText v-model="gifSearchKeyword" @input="searchGifs()" :placeholder="$t('Search GIFs across apps...')"/>
                </div>
                <div class="flex-1 overflow-auto scrollbar-hidden pb-1">
                    <Loading v-if="loading"/>
                    <div v-else>
                        <div v-if="gifs.length" class="flex flex-col">
                            <button v-for="gif in gifs" :key="gif.id" :style="`background-image: url('${gif.media[0].gif.url}'); padding-bottom: ${this.gifFrame(gif.media[0].gif)}%`" class="w-full bg-center bg-cover" @click="handleSelectGif(gif.media[0].gif.url)"></button>
                            <InfiniteLoading @infinite="getGifs(this.gifSearchKeyword)">	
                                <template #spinner>
                                    <Loading />
                                </template>
                                <template #complete><span></span></template>
                            </InfiniteLoading>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </DropdownMenu>
</template>

<script>
import { getApiData } from "@/api/gif";
import BaseIcon from "@/components/icons/BaseIcon.vue"
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import Loading from '@/components/utilities/Loading.vue';
import DropdownMenu from '@/components/utilities/DropdownMenu.vue'
import InfiniteLoading from 'v3-infinite-loading'
var typingTimer = null;

export default {
    components: { BaseIcon, BaseInputText, Loading, DropdownMenu, InfiniteLoading },
    data(){
        return{
            gifSearchKeyword: null,
            gifs: [],
            loading: false,
            offset: null,
        }
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
    methods:{
         async getGifs(keyword) {
            try {
                const offset = this.offset;
                if (!offset) this.loading = true;

                const fetchResponse = await getApiData(keyword, offset);
                const data = await fetchResponse?.json();

                this.offset = data?.next
                let gifs = data?.results;
                if (offset) gifs = [...this.gifs, ...gifs];

                if (gifs?.length > 0) {
                    this.gifs = gifs
                } else {
                    this.gifs = []
                }
                this.loading = false;
            } catch (e) {
                this.loading = false;
                this.offset = null
            }
        },
        searchGifs(){
			clearTimeout(typingTimer);
			typingTimer = setTimeout(() => {
                this.gifs = []
                this.offset = null
				this.getGifs(this.gifSearchKeyword)
            }
			, 400);
		},
        gifFrame(gif){
            return gif.dims[1] / gif.dims[0] * 100
        },
        toggleGifsBox(){
            this.getGifs()
        },
        async handleSelectGif(gifUrl) {
            this.close();
            try {
                const response = await fetch(gifUrl);
                
                if (!response.ok) {
                    throw new Error(`Failed to fetch GIF: ${response.statusText}`);
                }

                const blob = await response.blob();
                const file = new File([blob], 'uploaded-gif.gif', { type: blob.type });
                
                this.$emit('upload', file);
            } catch (error) {
                console.error('Error uploading GIF:', error);
            }
        },
        open(){
            this.$refs.tenorGifsDropdown?.open()
        },
        close(){
			this.$refs.tenorGifsDropdown?.close()
		}
    },
    emits: ['upload']
}
</script>