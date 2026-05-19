<template>
    <div>
        <div class="flex flex-row justify-between">
            <label class="block text-base mb-1">{{ label }}</label>
            <span class="form-medium mb-base-2" v-if="!text_min">{{ range_label }}</span>
            <span class="form-medium mb-base-2" v-else>{{ text_min }} - {{ text_max }}</span>
        </div>
        <div class="py-base-2 mx-2">
            <Slider v-model="model" range :step="step" class="w-14rem" :min="min" :max="max" v-on:slideend="emitToParent" />
        </div>
    </div>
</template>

<script>
import Slider from 'primevue/slider';

export default {
    components: { Slider },
    props: {
        label: {
            type: String,
            default: ''
        },
        prefix: {
            type: String,
            default: ''
        },
        step: {
            type: Number,
            default: 1,
        },
        min: {
            type: Number,
            default: 0
        },
        max: {
            type: Number,
            default: 500000000
        },
        value_min: {
            type: Number,
            default: 0
        },
        value_max: {
            type: Number,
            default: 0
        },
        text_min: {
            type: String,
            default: ''
        },
        text_max: {
            type: String,
            default: ''
        }
    },
    data() {
        return {
            model: [0, 0],
            price_text: '',
            value: {
                min: 0,
                max: 0
            }
        }
    },
    watch: {
        model: function (newVal) {
            this.updateValue(newVal);
        },
        value_min() {
            this.model = [this.value_min, this.value_max]
        },
        value_max() {
            this.model = [this.value_min, this.value_max]
        }
    },
    computed: {
        range_label: function () {
            return this.prefix + ' ' + this.value.min + ' - ' + this.prefix + ' ' + this.value.max;
        }
    },
    mounted() {
        if (this.value_max == 0) {
            this.model = [this.value_min, this.max];
        } else {
            this.model = [this.value_min, this.value_max];
        }
    },
    methods: {
        updateValue: function (new_model_val) {
            if (new_model_val[0] > new_model_val[1]) {
                this.value = {
                    min: new_model_val[1],
                    max: new_model_val[0]
                }
            } else {
                this.value = {
                    min: new_model_val[0],
                    max: new_model_val[1]
                }
            }
        },
        emitToParent() {
            this.$emit('childToParent', this.value);
            setTimeout(() => {
                if (this.model[0] == this.min && this.model[1] == this.min) {
                    this.model[1] += this.step;
                } else if (this.model[0] == this.max && this.model[1] == this.max) {
                    this.model[0] -= this.step;
                } else if (this.model[0] == this.model[1]) {
                    if ((this.model[0] - this.step) >= this.min) {
                        this.model[0] -= this.step;
                    } else if ((this.model[1] + this.step) <= this.max) {
                        this.model[1] += this.step;
                    }
                }
                this.updateValue(this.model);
                this.$emit('childToParent', this.value);
            }, 100);
        }
    },
    emits: ['childToParent']
}
</script>

<style scoped>
:deep(.p-slider-handle) {
    background: #fff !important;
    border: 2px solid #111;
}
</style>