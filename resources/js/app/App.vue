<script setup lang="ts">
import Logo from "@/shared/ui/Logo.vue"
import NavigateHeader from "@/widgets/nav/ui/Nav.vue"
</script>

<template>
    <header class="px-[10%] py-4 flex gap-4 items-center justify-between">
        <RouterLink to="/">
            <Logo />
        </RouterLink>
        <NavigateHeader />
    </header>
    <main>
        <RouterView />
    </main>
</template>
