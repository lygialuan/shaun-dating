<template>
    <WidgetContainer>
        <template v-if="data.enable_title" v-slot:title>{{ data.title }}</template>
        <template v-slot:body><div v-html="data.content" ref="content"></div></template>
    </WidgetContainer>
</template>

<script>
import WidgetContainer from '@/components/article/WidgetContainer.vue';

export default {
    components: { WidgetContainer },
    props: ["data", "params", "position"],
    mounted() {
        this.executeScriptElements(this.$refs.content);
    },
    methods: {
        executeScriptElements(containerElement) {
            const scriptElements = containerElement.querySelectorAll("script");

            Array.from(scriptElements).forEach((scriptElement) => {
                const clonedElement = document.createElement("script");

                Array.from(scriptElement.attributes).forEach((attribute) => {
                    clonedElement.setAttribute(attribute.name, attribute.value);
                });

                clonedElement.text = scriptElement.text;

                scriptElement.parentNode.replaceChild(
                    clonedElement,
                    scriptElement
                );
            });
        },
    },
};
</script>
