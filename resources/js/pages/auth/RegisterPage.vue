<script setup lang="ts">
import { ref } from "vue";
import { useRegister } from "@/processes/auth/lib/use-register";
import ButtonCustom from "@/shared/ui/ButtonCustom.vue";
import InputCustom from "@/shared/ui/InputCustom.vue";

interface FormDataType {
  name: string,
  email: string,
  login: string,
  password: string,
  password_confirmation: string,
  avatar: string,
}

const form = ref<FormDataType>({
  name: '',
  email: '',
  login: '',
  password: '',
  password_confirmation: '',
  avatar: 'users/avatars/default-user.svg',
});

const { isLoading, register } = useRegister();
const message = ref('');
const errors = ref<Record<string, string[]>>({});

const validateForm = (): boolean => {
  errors.value = {};


  if (!form.value.name) {
    errors.value.name = ['Name is required'];
    return false;
  }

  if (!form.value.login) {
    errors.value.login = ['Login is required'];
    return false;
  }

  if (!form.value.email) {
    errors.value.email = ['Email is required'];
    return false;
  }

  if (!form.value.password) {
    errors.value.password = ['Password is required'];
    return false;
  }

  if (form.value.password !== form.value.password_confirmation) {
    errors.value.password_confirmation = ['Passwords do not match'];
    return false;
  }

  return true;
}
const submitForm = async (): Promise<void> => {
  message.value = '';

  try {
    await register(form.value);
    // После успешной регистрации произойдет переход на страницу верификации
  } catch (error: any) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else if (error.response?.data?.message) {
      message.value = error.response.data.message;
    } else {
      message.value = 'Registration failed. Please try again.';
    }
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
      <h1 class="text-primary text-xl pb-8">Регистрация</h1>
      <RouterLink :to="{ name: 'login' }" >Уже есть аккаунт? Авторизоваться</RouterLink>
    </div>
    <form class="flex flex-col gap-4" @submit.prevent="handleSubmit">
      <InputCustom
          v-model:value="form.name"
          :placeholder="'Имя'"
          :name="'name'"
          :error="!!errors.name"
          :disabled="isLoading"
      />
      <InputCustom
          v-model:value="form.login"
          :placeholder="'Логин'"
          :name="'login'"
          :error="!!errors.login"
          :disabled="isLoading"
      />
      <InputCustom
          v-model:value="form.email"
          :placeholder="'Email'"
          :name="'email'"
          :error="!!errors.email"
          :disabled="isLoading"
      />
      <InputCustom
          v-model:value="form.password"
          :placeholder="'Пароль'"
          :name="'password'"
          :error="!!errors.password"
          :type="'password'"
          :disabled="isLoading"
      />
      <InputCustom
          v-model:value="form.password_confirmation"
          :placeholder="'Повторите пароль'"
          :name="'confirm_password'"
          :error="!!errors.password_confirmation"
          :type="'password'"
          :disabled="isLoading"
      />

      <ButtonCustom
          :text="isLoading ? '...' : 'Зарегистрироваться'"
          :type="'submit'"
          :disabled="isLoading"
      />
    </form>

    <div v-if="message" class="message" :class="{ 'error-message': errors }">
      {{ message }}
    </div>
</template>
