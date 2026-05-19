<template>
  <svg xmlns="http://www.w3.org/2000/svg" :width="size" :height="size" fill="currentColor" viewBox="0 0 256 256">
    <defs>
      <filter :id="key" x="-50%" y="-50%" width="200%" height="200%">
        <feDropShadow dx="0" dy="0" stdDeviation="12" flood-color="black" flood-opacity="0.5"/>
      </filter>
    </defs>
    <path :filter="`url(#${key})`" d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm40,112H107.31l18.35,18.34a8,8,0,0,1-11.32,11.32l-32-32a8,8,0,0,1,0-11.32l32-32a8,8,0,0,1,11.32,11.32L107.31,120H168a8,8,0,0,1,0,16Z"/>
  </svg>
</template>

<script>
import { uuidv4 } from '@/utility';

export default {
  props: {
    size: { type: String }
  },
  data(){
    return{
      key: uuidv4()
    }
  }
}
</script>