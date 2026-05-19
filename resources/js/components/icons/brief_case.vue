<template>
  <svg :width="size" :height="size"  viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M10.6663 14V3.33333C10.6663 2.97971 10.5259 2.64057 10.2758 2.39052C10.0258 2.14048 9.68663 2 9.33301 2H6.66634C6.31272 2 5.97358 2.14048 5.72353 2.39052C5.47348 2.64057 5.33301 2.97971 5.33301 3.33333V14M2.66634 4.66667H13.333C14.0694 4.66667 14.6663 5.26362 14.6663 6V12.6667C14.6663 13.403 14.0694 14 13.333 14H2.66634C1.92996 14 1.33301 13.403 1.33301 12.6667V6C1.33301 5.26362 1.92996 4.66667 2.66634 4.66667Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
  </svg>
</template>

<script>
export default {
  props: {
    size: { type: String }
  }
}
</script>