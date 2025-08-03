<script setup lang="ts">
import Nav from "@/widgets/nav/ui/Nav.vue"
import Logo from "@/shared/ui/Logo.vue"
</script>

<template>
    <header class="px-[4%] md:px-[10%] py-4 flex flex-col md:flex-row gap-4 items-center justify-between">
        <RouterLink to="/">
            <Logo />
        </RouterLink>
        <Nav />
    </header>
    <main>
        <slot />
    </main>
</template>
