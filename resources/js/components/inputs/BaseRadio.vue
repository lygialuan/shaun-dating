<template>
    <RadioButton :class="{'p-invalid': error}" />
 </template>
 
 <script>
import RadioButton from 'primevue/radiobutton';
 
 export default {
     components: { RadioButton },
     props:{
        error: {
			type: String,
			default: null
		}
     }
 }
 </script>