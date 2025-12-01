<script setup lang="ts">
import { ref } from "vue";
import { useRouter } from "vue-router";
import ButtonCustom from "@/shared/ui/ButtonCustom.vue";
import InputCustom from "@/shared/ui/InputCustom.vue";
import { useLogin } from "@/processes/auth/lib/use-login";

interface FormDataType {
  login: string
  password: string
}

const form = ref<FormDataType>({
  login: '',
  password: ''
});

const router = useRouter();
const { isLoading, login } = useLogin();


const message = ref<string>('');
const isNotVerify = ref<boolean>(false);
const errors = ref<Record<string, string[]>>({});

const doVerify = async () => {
  await router.push({
    name: 'verify',
    query: {
      login: form.value.login,
      resend: 'true' // Флаг для автоматической отправки
    }
  });
}
const validateForm = (): boolean => {
  errors.value = {};

  if (!form.value.login) {
    errors.value.login = ['Login is required'];
    return false;
  }
  if (!form.value.password) {
    errors.value.password = ['Password is required'];
    return false;
  }

  return true;
}
const submitForm = async (): Promise<void> => {
  message.value = '';
  isNotVerify.value = false;

  try {
    await login(form.value);
  } catch (error) {
    message.value = error.response?.data?.message || 'Не удалость авторизоваться';
    if (error.response?.data?.message == 'Email не подтвержден') isNotVerify.value = true;
  } finally {
    isLoading.value = false;
  }
}

const handleSubmit = async (): Promise<void> => {
  const isValid = validateForm();
  if (!isValid) return;

  await submitForm();
}
</script>

<template>
  <div class="text-center mb-4">
    <h1 class="text-primary text-xl pb-8">Авторизация</h1>
    <RouterLink :to="{ name: 'register' }" >Нет аккаунта? Зарегистрироваться</RouterLink>
  </div>
  <form class="flex flex-col gap-2" @submit.prevent="handleSubmit">
    <InputCustom
        v-model:value="form.login"
        :placeholder="'Логин'"
        :name="'login'"
        :error="!!errors.login"
        :disabled="isLoading"
    />
    <InputCustom
        v-model:value="form.password"
        :placeholder="'Пароль'"
        :type="'password'"
        :name="'password'"
        :error="!!errors.password"
        :disabled="isLoading"
    />
    <ButtonCustom
        :text="isLoading ? '...' : 'Войти'"
        :type="'submit'"
        :disabled="isLoading"
    />
  </form>

  <div class="text-center pt-4">
    <div v-if="message">{{ message }}</div>
    <button type="button" class="underline" v-if="isNotVerify" @click="doVerify">Подтвердить</button>
  </div>
</template>