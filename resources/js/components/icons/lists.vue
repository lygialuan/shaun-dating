<template>
    <svg :width="size" :height="size" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M12 12H20.25" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
      <path d="M12 6H20.25" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
      <path d="M12 18H20.25" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
      <path d="M8.625 4.5L5.37187 7.5L3.75 6" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
      <path d="M8.625 10.5L5.37187 13.5L3.75 12" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
      <path d="M8.625 16.5L5.37187 19.5L3.75 18" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
</template>

<script>
export default {
  props: {
    size: { type: String }
  }
}
</script>