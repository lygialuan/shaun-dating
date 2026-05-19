<template>
    <div ref="feedShareLink">
        <EmbedplayTikTokVue
            :postId="videoId" 
            :autoplay="false" 
            :progressBar="true" 
            :playButton="true"
            :volumeControl="true" 
            :fullscreenButton="true" 
            :timestamp="true"
            :loop="false"
            :musicInfo="true"
            :description="false"
            :rel="true"
            :nativeContextMenu="false"
            :width="this.videoWidth" 
            :height="this.videoHeight"
        />
    </div>
</template>

<script>
import EmbedplayTikTokVue  from 'embedplay-tiktok-vue';

export default {
    components: { EmbedplayTikTokVue },
    props: {
        post: Object,
        videoId: {
            type: String,
            required: true,
        },
    },
    data() {
        return {
            videoWidth: 0,
            videoHeight: 0
        };
    },
    mounted() {
        this.calculateVideoWidth();
        window.addEventListener("resize", this.calculateVideoWidth)
    },
    unmounted(){
        window.removeEventListener("resize", this.calculateVideoWidth)
    },
    methods:{
        calculateVideoWidth(){
            this.videoWidth = this.$refs.feedShareLink?.offsetWidth
            this.videoHeight = this.videoWidth * 9 / 16
        }
    }
};
</script>
