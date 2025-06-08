<script setup lang="ts">
const text = defineModel<string>('text', {
    required: true,
    default: ''
})

defineProps<{
    placeholder?: string
}>()

const resizeTextarea = (event: Event): void => {
    const textarea: HTMLTextAreaElement = event.target as HTMLTextAreaElement

    textarea.style.height = 'auto'
    if (!textarea.textContent) textarea.style.height = `${textarea.scrollHeight}px`

    // return textarea.scrollHeight
}
</script>

<template>
    <textarea
        class="focus-visible:outline-none resize-none py-4 px-8 rounded-3xl border border-secondary bg-transparent min-h-20 w-full"
        v-model="text"
        :placeholder="placeholder"
        @input="resizeTextarea"
    />
</template>
