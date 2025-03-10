<script setup lang="ts">
defineProps<{
    text: string,
    className?: string,
    type?: 'button' | 'reset' | 'submit',
}>()
</script>

<template>
    <button
        class="btn"
        :class="className"
        @click="$emit('handler')"
        :type="type"
    >
        {{ text }}
    </button>
</template>

<style>
.btn {
    background: linear-gradient(135deg, #a1f4ff 0%, #abc8ff 100%);
    color: white;
    padding: 0.75rem 40px;
    border: none;
    border-radius: 60px;
    cursor: pointer;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    min-width: 300px;
}
</style>
