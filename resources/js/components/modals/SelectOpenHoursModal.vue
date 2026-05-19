<template>
    <form @submit.prevent="handleSelectOpenHours(selectedOpenHours.type.value)">
        <div v-if="openHoursList.length" class="space-y-base-2">
            <div v-for="openHours in openHoursList" :key="openHours.value" class="flex items-center gap-base-2">
                <BaseRadio :value="openHours" v-model="selectedOpenHours.type" :inputId="openHours.value" name="price_range" />
                <label :for="openHours.value">
                    <div>
                        <div class="font-bold">{{ openHours.title }}</div>
                        <div class="text-base-xs">{{ openHours.description }}</div>
                    </div>
                </label>
            </div>
            <div v-if="selectedOpenHours.type.value === 'hours'" class="space-y-base-2">
                <div class="flex flex-col md:flex-row items-start md:items-center gap-x-base-2 gap-y-base-1">
                    <span class="font-bold flex-1">{{ $t('Monday') }}</span>
                    <div class="flex gap-base-2 flex-3">
                        <BaseCalendar v-model="openingMonday" timeOnly :placeholder="$t('Opening')"/>
                        <BaseCalendar v-model="closingMonday" timeOnly :placeholder="$t('Closing')"/>
                    </div>
                </div>
                <div class="flex flex-col md:flex-row items-start md:items-center gap-x-base-2 gap-y-base-1">
                    <span class="font-bold flex-1">{{ $t('Tuesday') }}</span>
                    <div class="flex gap-base-2 flex-3">
                        <BaseCalendar v-model="openingTuesday" timeOnly :placeholder="$t('Opening')"/>
                        <BaseCalendar v-model="closingTuesday" timeOnly :placeholder="$t('Closing')"/>
                    </div>
                </div>
                <div class="flex flex-col md:flex-row items-start md:items-center gap-x-base-2 gap-y-base-1">
                    <span class="font-bold flex-1">{{ $t('Wednesday') }}</span>
                    <div class="flex gap-base-2 flex-3">
                        <BaseCalendar v-model="openingWednesday" timeOnly :placeholder="$t('Opening')"/>
                        <BaseCalendar v-model="closingWednesday" timeOnly :placeholder="$t('Closing')"/>
                    </div>
                </div>
                <div class="flex flex-col md:flex-row items-start md:items-center gap-x-base-2 gap-y-base-1">
                    <span class="font-bold flex-1">{{ $t('Thursday') }}</span>
                    <div class="flex gap-base-2 flex-3">
                        <BaseCalendar v-model="openingThursday" timeOnly :placeholder="$t('Opening')"/>
                        <BaseCalendar v-model="closingThursday" timeOnly :placeholder="$t('Closing')"/>
                    </div>
                </div>
                <div class="flex flex-col md:flex-row items-start md:items-center gap-x-base-2 gap-y-base-1">
                    <span class="font-bold flex-1">{{ $t('Friday') }}</span>
                    <div class="flex gap-base-2 flex-3">
                        <BaseCalendar v-model="openingFriday" timeOnly :placeholder="$t('Opening')"/>
                        <BaseCalendar v-model="closingFriday" timeOnly :placeholder="$t('Closing')"/>
                    </div>
                </div>
                <div class="flex flex-col md:flex-row items-start md:items-center gap-x-base-2 gap-y-base-1">
                    <span class="font-bold flex-1">{{ $t('Saturday') }}</span>
                    <div class="flex gap-base-2 flex-3">
                        <BaseCalendar v-model="openingSaturday" timeOnly :placeholder="$t('Opening')"/>
                        <BaseCalendar v-model="closingSaturday" timeOnly :placeholder="$t('Closing')"/>
                    </div>
                </div>
                <div class="flex flex-col md:flex-row items-start md:items-center gap-x-base-2 gap-y-base-1">
                    <span class="font-bold flex-1">{{ $t('Sunday') }}</span>
                    <div class="flex gap-base-2 flex-3">
                        <BaseCalendar v-model="openingSunday" timeOnly :placeholder="$t('Opening')"/>
                        <BaseCalendar v-model="closingSunday" timeOnly :placeholder="$t('Closing')"/>
                    </div>
                </div>
            </div>
            <BaseButton fluid>{{ $t('Save') }}</BaseButton>
        </div>
    </form>
</template>

<script>
import BaseRadio from '@/components/inputs/BaseRadio.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseCalendar from '@/components/inputs/BaseCalendar.vue'
import { getOpenHoursPage, storeOpenHoursPage } from '@/api/page'

export default {
    components: { BaseRadio, BaseButton, BaseCalendar },
    inject: ['dialogRef'],
    data(){
        return{
            openHoursList: [],
            selectedOpenHours: window._.cloneDeep(this.dialogRef.data.selectedOpenHours),
            openingMonday: this.dialogRef.data.selectedOpenHours.values ? this.formatTimeInput(this.dialogRef.data.selectedOpenHours.values.mon?.start) : null,
            closingMonday: this.dialogRef.data.selectedOpenHours.values ? this.formatTimeInput(this.dialogRef.data.selectedOpenHours.values.mon?.end) : null,
            openingTuesday: this.dialogRef.data.selectedOpenHours.values ? this.formatTimeInput(this.dialogRef.data.selectedOpenHours.values.tue?.start) : null,
            closingTuesday: this.dialogRef.data.selectedOpenHours.values ? this.formatTimeInput(this.dialogRef.data.selectedOpenHours.values.tue?.end) : null,
            openingWednesday: this.dialogRef.data.selectedOpenHours.values ? this.formatTimeInput(this.dialogRef.data.selectedOpenHours.values.wed?.start) : null,
            closingWednesday: this.dialogRef.data.selectedOpenHours.values ? this.formatTimeInput(this.dialogRef.data.selectedOpenHours.values.wed?.end) : null,
            openingThursday: this.dialogRef.data.selectedOpenHours.values ? this.formatTimeInput(this.dialogRef.data.selectedOpenHours.values.thu?.start) : null,
            closingThursday: this.dialogRef.data.selectedOpenHours.values ? this.formatTimeInput(this.dialogRef.data.selectedOpenHours.values.thu?.end) : null,
            openingFriday: this.dialogRef.data.selectedOpenHours.values ? this.formatTimeInput(this.dialogRef.data.selectedOpenHours.values.fri?.start) : null,
            closingFriday: this.dialogRef.data.selectedOpenHours.values ? this.formatTimeInput(this.dialogRef.data.selectedOpenHours.values.fri?.end) : null,
            openingSaturday: this.dialogRef.data.selectedOpenHours.values ? this.formatTimeInput(this.dialogRef.data.selectedOpenHours.values.sat?.start) : null,
            closingSaturday: this.dialogRef.data.selectedOpenHours.values ? this.formatTimeInput(this.dialogRef.data.selectedOpenHours.values.sat?.end) : null,
            openingSunday: this.dialogRef.data.selectedOpenHours.values ? this.formatTimeInput(this.dialogRef.data.selectedOpenHours.values.sun?.start) : null,
            closingSunday: this.dialogRef.data.selectedOpenHours.values ? this.formatTimeInput(this.dialogRef.data.selectedOpenHours.values.sun?.end) : null,
        }
    },
    mounted(){
        this.fetchOpenHoursPage()
    },
    computed:{
        isAnyTimeSet() {
            return (
                this.openingMonday || this.closingMonday ||
                this.openingTuesday || this.closingTuesday ||
                this.openingWednesday || this.closingWednesday ||
                this.openingThursday || this.closingThursday ||
                this.openingFriday || this.closingFriday ||
                this.openingSaturday || this.closingSaturday ||
                this.openingSunday || this.closingSunday
            );
        }
    },
    methods: {
        async fetchOpenHoursPage(){
            try {
                const response = await getOpenHoursPage()
                this.openHoursList = response
            } catch (error) {
                console.log(error)
            }
        },
        formatTimeInput(time){
            if(time){
                const [hours, minutes] = time.split(':').map(Number);
                return new Date(0, 0, 0, hours, minutes);
            }
            return null
        },
        formatTimeOutput(time){
            if(time){
                if(typeof(time) === 'object'){
                    return this.formatTimeOnly(time)
                } else {
                    return time
                }
            }
            return null
        },
        async handleSelectOpenHours(openHoursType){
            if (openHoursType === 'hours' && !this.isAnyTimeSet) {
                this.showError(this.$t('Please provide opening and closing times for at least one day.'));
                return;
            }
            try {
                const values = {
                    ...((this.openingMonday || this.closingMonday) && 
                    {
                        "mon": {
                            "start": this.formatTimeOutput(this.openingMonday),
                            "end": this.formatTimeOutput(this.closingMonday)
                        }
                    }),
                    ...((this.openingTuesday || this.closingTuesday) && 
                    {
                        "tue": {
                            "start": this.formatTimeOutput(this.openingTuesday),
                            "end": this.formatTimeOutput(this.closingTuesday)
                        }
                    }),
                    ...((this.openingWednesday || this.closingWednesday) && 
                    {
                        "wed": {
                            "start": this.formatTimeOutput(this.openingWednesday),
                            "end": this.formatTimeOutput(this.closingWednesday)
                        }
                    }),
                    ...((this.openingThursday || this.closingThursday) && 
                    {
                        "thu": {
                            "start": this.formatTimeOutput(this.openingThursday),
                            "end": this.formatTimeOutput(this.closingThursday)
                        }
                    }),
                    ...((this.openingFriday || this.closingFriday) && 
                    {
                        "fri": {
                            "start": this.formatTimeOutput(this.openingFriday),
                            "end": this.formatTimeOutput(this.closingFriday)
                        }
                    }),
                    ...((this.openingSaturday || this.closingSaturday) && 
                    {
                        "sat": {
                            "start": this.formatTimeOutput(this.openingSaturday),
                            "end": this.formatTimeOutput(this.closingSaturday)
                        }
                    }),
                    ...((this.openingSunday || this.closingSunday) && 
                    {
                        "sun": {
                            "start": this.formatTimeOutput(this.openingSunday),
                            "end": this.formatTimeOutput(this.closingSunday)
                        }
                    })                
                }
                await storeOpenHoursPage(openHoursType, JSON.stringify(values))
                this.selectedOpenHours.values = values
                this.dialogRef.close({open_hours: this.selectedOpenHours});
                this.showSuccess(this.$t('Your changes have been saved.'))
            } catch (error) {
                this.showError(error.error)
            }
        }
    }
}
</script>