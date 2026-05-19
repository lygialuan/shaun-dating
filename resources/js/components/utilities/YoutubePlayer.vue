<template>
	<div class="relative pb-[56.25%]">
		<div ref="youtubePlayer" class="absolute inset-0 w-full h-full"></div>
	</div>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { useVideoStore } from '@/store/video'
import { uuidv4 } from '@/utility/index'

export default {
    props: {
        videoId: {
            type: String,
            required: true,
        },
    },
    data() {
        return {
            player: null,
			videoKey: uuidv4()
        };
    },
	computed:{
		...mapState(useVideoStore, ['currentlyPlaying']),
	},
	watch:{
		currentlyPlaying(newVal){
            if (newVal !== this.videoKey) {
                this.pause();
            }
        },
	},
    mounted() {
        if (!window.YT && !document.getElementById('youtube-iframe-api')) {
            const tag = document.createElement("script");
            tag.src = "https://www.youtube.com/iframe_api";
            tag.id = "youtube-iframe-api"; // Add an ID for easy checking
            const firstScriptTag = document.getElementsByTagName("script")[0];
            firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

            // Wait for the API to load before initializing the player
            window.onYouTubeIframeAPIReady = this.initializePlayer;
        } else if (window.YT && window.YT.Player) {
            this.initializePlayer();
        } else {
            // If script is loading, wait for API to be ready
            const checkYT = setInterval(() => {
                if (window.YT && window.YT.Player) {
                    clearInterval(checkYT);
                    this.initializePlayer();
                }
            }, 100);
        }
    },
    methods: {
		...mapActions(useVideoStore, ['setCurrentlyPlaying']),
        initializePlayer() {
            this.player = new window.YT.Player(this.$refs.youtubePlayer, {
                videoId: this.videoId,
                events: {
                    onStateChange: this.onPlayerStateChange,
                },
            });
        },
        onPlayerStateChange(event) {
            const state = event.data;
            switch (state) {
                case window.YT.PlayerState.PLAYING:
                    this.setCurrentlyPlaying(this.videoKey)
                    break;
            }
        },
		play() {
			if (this.player) this.player.playVideo();
		},
		pause() {
			if (this.player) this.player.pauseVideo();
		},
		stop() {
			if (this.player) this.player.stopVideo();
		}
    },
};
</script>
