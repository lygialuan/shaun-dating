import { defineStore } from 'pinia'

export const useVideoStore = defineStore('video', {

    state: () => ({
        currentlyPlaying: null,
        muteVideo: true,
        volumeValue: 0,
        volumeCurrentValue: 0,
        currentPlayedTime: 0,
        currentPipVideo: null
    }),
    actions: {
        setCurrentlyPlaying(videoId) {
            this.currentlyPlaying = videoId;
        },
        setMuteVideo(payload){
            this.muteVideo = payload
        },
        setVolumeValue(value){
            this.volumeValue = value
        },
        setVolumeCurrentValue(value){
            this.volumeCurrentValue = value
        },
        setCurrentPlayedTime(value){
            this.currentPlayedTime = value
        },
        setCurrentPipVideo(value){
            this.currentPipVideo = value
        }
    },
    persist: false
  })