<script setup lang="ts">
import Nav from "@/widgets/nav/ui/Nav.vue"
import Logo from "@/shared/ui/Logo.vue"
</script>

<template>
    <header class="px-[10%] py-4 flex gap-4 items-center justify-between">
        <RouterLink to="/">
            <Logo />
        </RouterLink>
        <Nav />
    </header>
    <main>
        <slot />
    </main>
</template>
