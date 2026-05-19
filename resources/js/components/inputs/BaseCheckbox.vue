<template>
    <Checkbox :binary="binary" />
 </template>
 
 <script>
import Checkbox from 'primevue/checkbox';
 
 export default {
     components: { Checkbox },
     props:{
        binary: Boolean,
     }
 }
 </script>