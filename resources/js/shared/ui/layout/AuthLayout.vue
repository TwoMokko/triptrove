<script setup lang="ts">
import Logo from "@/shared/ui/Logo.vue"
</script>

<template>
<main class="flex h-full">
    <div class="flex-[50%] px-[10%] py-10">
        <div>
            <router-link :to="{ name: 'home' }">
                <Logo />
            </router-link>
        </div>
        <div class="mt-40">
            <slot />
        </div>
    </div>
    <div class="test flex-[50%] rounded-3xl m-2">
        <div class="test-text">text anything</div>
    </div>
</main>
</template>

<style>
.test {
    background: linear-gradient(135deg, #abc8ff 0%, #a1f4ff 100%);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.test-text {
    padding: 40px 60px;
    //border-radius: 20px;
    background-color: #ffffff40;
    color: white;
    font-size: 3rem;
    //box-shadow: 0 0 20px rgba(0, 0, 0, 0.06);
}
</style>
