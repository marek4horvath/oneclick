<script setup lang="ts">
interface Props {
    dataJson: string
}

const props = defineProps<Props>()

const formattedJson = computed(() => {
    if (!props.dataJson) {
        console.error('data JSON is undefined or null')

        return 'Invalid or missing data JSON'
    }

    try {
        const jsonObject = JSON.parse(props.dataJson)

        if (jsonObject.dpp) {
            const { id, supplyChainTemplate, ...rest } = jsonObject.dpp

            jsonObject.dpp = {
                id,
                supplyChainTemplate,
                ...rest,
            }
        }

        let json = JSON.stringify(jsonObject, null, 2)
        json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')

        let currentKey: string | null = null

        return json.replace(
            /("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+]?\d+)?)/g,
            (match, group1, group2, colon) => {
                let cls = 'number'

                if (colon) {
                    currentKey = match.replace(/[:"]/g, '')
                    cls = 'key'
                } else if (group1.startsWith('"')) {
                    cls = 'string'
                    if (currentKey === 'companies' || currentKey === 'logistics') {
                        cls += ' custom-class'
                    }
                } else if (/true|false/.test(match)) {
                    cls = 'boolean'
                } else if (/null/.test(match)) {
                    cls = 'null'
                }

                return `<span class="${cls}">${match}</span>`
            },
        )
    } catch (error) {
        console.error('Error formatting JSON:', error)

        return 'Error parsing or formatting JSON'
    }
})
</script>

<template>
    <div class="json-box">
        <slot name="header" />
        <!-- eslint-disable-next-line vue/no-v-html -->
        <pre v-html="formattedJson" />
    </div>
</template>

<style lang="scss">
$padding: 5px;
$margin: 5px;

$colors: (
  "string": #000,
  "number": #000,
  "boolean": #000,
  "null": #000,
  "key": #26A69A,
  "custom-class": #000
);

pre {
  padding: $padding;
  margin: $margin;
}

.json-box {
    background-color: #F3F3F3;
    height: 30rem;
    overflow-y: scroll;
    overflow-x: hidden;
}

@each $type, $color in $colors {
  .#{$type} {
    color: $color !important;
    margin-left: 0px !important;
  }
}
</style>
