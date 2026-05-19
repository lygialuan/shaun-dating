<template>
  <svg :width="size" :height="size" fill="none"  viewBox="0 0 24 24"  xmlns="http://www.w3.org/2000/svg">
    <path d="M18.8439 7.875C18.7065 9.78141 17.2923 11.25 15.7501 11.25C14.2079 11.25 12.7914 9.78188 12.6564 7.875C12.5157 5.89172 13.8925 4.5 15.7501 4.5C17.6078 4.5 18.9845 5.92781 18.8439 7.875Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M15.7498 14.25C12.695 14.25 9.75731 15.7673 9.02138 18.7223C8.92388 19.1133 9.16903 19.5 9.57075 19.5H21.9293C22.3311 19.5 22.5748 19.1133 22.4787 18.7223C21.7428 15.72 18.8051 14.25 15.7498 14.25Z" stroke="currentColor" stroke-width="2" stroke-miterlimit="10"/>
    <path d="M9.37496 8.71594C9.26527 10.2384 8.12246 11.4375 6.89058 11.4375C5.65871 11.4375 4.51402 10.2389 4.40621 8.71594C4.29418 7.13203 5.40652 6 6.89058 6C8.37465 6 9.48699 7.16109 9.37496 8.71594Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M9.65622 14.3438C8.81013 13.9562 7.87825 13.8071 6.8906 13.8071C4.4531 13.8071 2.10466 15.0188 1.51638 17.379C1.43903 17.6912 1.63497 18.0001 1.9556 18.0001H7.21872" stroke="currentColor" stroke-width="2" stroke-miterlimit="10" stroke-linecap="round"/>
  </svg>
</template>

<script>
export default {
  props: {
    size: { type: String }
  }
}
</script>