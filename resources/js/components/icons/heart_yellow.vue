<template>
  <svg :width="size" :height="size" viewBox="0 0 31 28" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M25.5656 1.24605C28.8129 3.14538 31.0975 7.00159 30.9968 11.5003C30.7829 21 17.825 28 15.5 28C13.175 28 0.215553 21 0.0032049 11.5003C-0.0975444 7.00159 2.18714 3.14694 5.43437 1.24605C8.47235 -0.528835 12.2884 -0.539724 15.5 2.08138C18.7116 -0.539724 22.5276 -0.530391 25.5656 1.24605Z" fill="url(#paint0_linear_245_6127)"/>
    <defs>
    <linearGradient id="paint0_linear_245_6127" x1="0" y1="14" x2="31" y2="14" gradientUnits="userSpaceOnUse">
    <stop stop-color="#FF8008"/>
    <stop offset="1" stop-color="#FFC837"/>
    </linearGradient>
    </defs>
  </svg>
</template>

<script>
export default {
  props: {
    size: { type: String }
  }
}
</script>