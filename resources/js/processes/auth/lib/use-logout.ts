import { useRouter } from "vue-router";
import { useAuthStore } from "../model/store";
import { useUsersStore } from "../../../entities/user/model/store";
import { ref } from "vue";

export const useLogout = () => {
    const router = useRouter();
    const authStore = useAuthStore();
    const usersStore = useUsersStore();
    const isLoadingLogout = ref<boolean>(false);

    const doLogout = async () => {
        if (isLoadingLogout.value) return;
        isLoadingLogout.value = true;

        try {
            await authStore.logout();
            usersStore.resetCurrentUser();
            await router.replace('/login');
        } finally {
            isLoadingLogout.value = false;
        }
    };

    return { isLoadingLogout, doLogout };
};