<script setup lang="ts">
defineProps<{
    text: string,
    className?: string,
    type?: string,
}>()
</script>

<template>
    <button
        class="btn"
        :class="className"
        @click="$emit('handler')"
    >
        {{ text }}
    </button>
</template>

<style>
.btn {
    background: linear-gradient(135deg, #a1f4ff 0%, #abc8ff 100%);
    /*background: linear-gradient(135deg, #ffa75f 0%, #ffabd9 100%);*/
    color: white;
    padding: 10px 40px;
    border: none;
    border-radius: 60px;
    cursor: pointer;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    min-width: 300px;
    /*width: 100%;*/
}
</style>
