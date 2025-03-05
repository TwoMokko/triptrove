<template>
    <div class="px-[10%] py-10">
        <div>Logout</div>
        <button @click="logout" class="btn">do</button>
    </div>
</template>

<script setup>
import api from "@/app/api/api.js"
import { useRouter } from 'vue-router'

const router = useRouter();

const logout = async () => {
    try {
        await api.post('/logout', {}, {
            headers: {
                Authorization: `Bearer ${localStorage.getItem('auth_token')}`,
            },
        });
        localStorage.removeItem('auth_token');
        await router.push('/login');
    } catch (error) {
        console.error(error);
    }
};
</script>
