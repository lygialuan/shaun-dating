<template>
  <svg :width="size" :height="size" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M1 6.83333H12.6667M12.6667 6.83333L6.83333 1M12.6667 6.83333L6.83333 12.6667" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
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