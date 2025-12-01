import { useRouter } from "vue-router";
import { useAuthStore } from "../model/store";
import { useUsersStore } from "../../../entities/user/model/store";
import { ref } from "vue";

export const useLogin = () => {
    const router = useRouter();
    const authStore = useAuthStore();
    const usersStore = useUsersStore();
    const isLoading = ref<boolean>(false);

    const login = async (credentials: { login: string; password: string }) => {
        if (isLoading.value) return;

        isLoading.value = true;

        try {
            await authStore.login(credentials);
            await usersStore.fetchCurrentUser();

            const redirect = router.currentRoute.value.query.redirect;
            const redirectPath = typeof redirect === 'string' ? redirect : '/';

            await router.replace(redirectPath);
        } catch (error) {
            authStore.clearAuthData();
            usersStore.resetCurrentUser();
            throw error;
        } finally {
            isLoading.value = false;
        }
    }

    return { isLoading, login };
}