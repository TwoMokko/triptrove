<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/processes/auth/model/store';
import { useUsersStore } from "@/entities/user/model/store";
import InputCustom from "@/shared/ui/InputCustom.vue";
import ButtonCustom from "@/shared/ui/ButtonCustom.vue";

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const usersStore = useUsersStore();

const code = ref('');
const isLoading = ref(false);
const isResending = ref(false);
const message = ref('');
const error = ref('');
const attemptsLeft = ref<number | null>(null);

const userLogin = ref(route.query.login as string);

// Определяем откуда пришли
const accessType = computed(() => {
  if (authStore.tempToken) return 'after_register';
  if (userLogin.value) return 'after_login';
  return 'invalid';
});

onMounted(() => {
  // Разные проверки для разных сценариев
  if (accessType.value === 'after_register') {
    // После регистрации - проверяем tempToken
    if (!authStore.tempToken || !userLogin.value) {
      router.push({ name: 'register' });
    }
  } else if (accessType.value === 'after_login') {
    // После логина - проверяем только логин
    if (!userLogin.value) {
      router.push({ name: 'register' });
    }
  } else {
    // Нет ни tempToken ни логина
    router.push({ name: 'register' });
  }

  if (route.query.resend === 'true') {
    resendCode();
  }
});

const verify = async () => {
  if (!code.value || code.value.length !== 6) {
    error.value = 'Please enter a valid 6-digit code';
    return;
  }

  isLoading.value = true;
  error.value = '';
  message.value = '';

  try {
    // Для обоих сценариев используем verifyEmail
    // tempToken автоматически добавится через интерцептор если есть
    await authStore.verifyEmail({
      code: code.value,
      login: userLogin.value
    });

    // После успешной верификации
    await usersStore.fetchCurrentUser();
    await router.replace('/');

  } catch (err: any) {
    if (err.response?.data?.error) {
      error.value = err.response.data.error;
      attemptsLeft.value = err.response.data.attempts_left;
    } else {
      error.value = 'Подтверждение проавлилось. Попробуйте еще раз.';
    }
  } finally {
    isLoading.value = false;
  }
};

const resendCode = async () => {
  if (isResending.value) return;

  isResending.value = true;
  message.value = '';
  error.value = '';

  try {
    await authStore.resendVerificationCode(userLogin.value);
    message.value = `На указанный email для login ${userLogin.value} был отправлен новый код верификации`;
    code.value = '';
    attemptsLeft.value = null;
  } catch (err: any) {
    error.value = err.response?.data?.error || 'Failed to resend verification code';
  } finally {
    isResending.value = false;
  }
};
</script>

<template>
  <div>


    <div class="text-center pb-4">
      <h1 class="text-primary text-xl pb-8">Подтвердите свой Email</h1>
      <p v-if="accessType === 'after_register'">
        Добро пожаловать! Подтвердите email, чтобы начать пользоваться аккаунтом.
      </p>
      <p v-else-if="accessType === 'after_login'">
        Для входа необходимо подтвердить email.
      </p>

      <p>Мы отправили 6-значный код на вашу почту:</p>
    </div>


    <form @submit.prevent="verify" class="flex flex-col gap-2">
      <InputCustom
          v-model:value="code"
          :placeholder="'Введите 6-значный код'"
          :disabled="isLoading"
      />
      <ButtonCustom
          :text="isLoading ? 'Подтверждение...' : 'Подтвердить Email'"
          :type="'submit'"
          :disabled="isLoading || code.length !== 6"
        />
    </form>

    <div class="text-center pt-4">
      <button
          @click="resendCode"
          :disabled="isLoading"
          class="underline"
      >
        Resend Code
      </button>
    </div>

    <div v-if="message" class="text-secondary text-center pt-4">
      {{ message }}
    </div>

    <div v-if="error" class="text-error text-center pt-4">
      {{ error }}

      <div v-if="attemptsLeft !== null">
        Осталось попыток: {{ attemptsLeft }}
      </div>
    </div>
  </div>
</template>