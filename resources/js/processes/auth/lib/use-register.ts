import { useRouter } from "vue-router";
import { useAuthStore } from "../model/store";
import { ref } from "vue";

export const useRegister = () => {
    const router = useRouter();
    const authStore = useAuthStore();
    const isLoading = ref<boolean>(false);

    const register = async (userData: {
        name: string;
        email: string;
        password: string;
        password_confirmation: string;
    }) => {
        if (isLoading.value) return;

        isLoading.value = true;

        try {
            const response = await authStore.register(userData);

            // После регистрации переходим на страницу верификации
            await router.push({
                name: 'verify',
                query: {
                    // user_id: response.user_id,
                    login: response.login
                }
            });

        } catch (error) {
            throw error;
        } finally {
            isLoading.value = false;
        }
    };

    return { isLoading, register };
};