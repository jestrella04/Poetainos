<script setup lang="ts">
import axios from 'axios'
import { onMounted, reactive, ref } from 'vue'
import { useFormErrors } from '@/composables/useFormErrors'
import { useFormSubmit } from '@/composables/useFormSubmit'
import type { LaravelValidationErrors } from '@/types/http'

interface Captcha {
  key: string
  img: string
}

const { validationErrors } = useFormErrors()
const { isPosting, errors, submitForm: postForm } = useFormSubmit<LaravelValidationErrors>({})
const isPosted = ref(false)
const captcha = ref<Captcha>({ key: '', img: '' })
const formData = reactive({
  name: '',
  email: '',
  subject: '',
  message: '',
  key: '',
  captcha: ''
})

onMounted(() => {
  void reloadCaptcha()
})

async function reloadCaptcha() {
  await axios.get<Captcha>('/captcha/api/math').then((response) => {
    captcha.value = response.data
    formData.captcha = ''
  })
}

function clearInputs() {
  formData.name = ''
  formData.email = ''
  formData.subject = ''
  formData.message = ''
  formData.key = ''
  formData.captcha = ''
  void reloadCaptcha()
}

function resetForm() {
  isPosted.value = false
  clearInputs()
  errors.value = {}
}

async function submitForm() {
  await postForm({
    formSelector: '#contact-form',
    payload: {
      name: formData.name,
      email: formData.email,
      subject: formData.subject,
      message: formData.message,
      key: captcha.value.key,
      captcha: formData.captcha
    },
    cooldown: true,
    onSuccess: () => {
      resetForm()
      isPosted.value = true
    },
    onError: (error) => {
      void reloadCaptcha()

      return validationErrors(error)
    }
  })
}
</script>

<template>
  <po-head />
  <v-card :title="$t('main.contact-form').toUpperCase()">
    <v-form
      id="contact-form"
      :action="route('contact.store')"
      class="px-5 pb-5"
      @submit.prevent="submitForm()"
    >
      <v-text-field
        v-model="formData.name"
        :label="$t('main.name')"
        :placeholder="$t('main.enter-your-name')"
        minlength="3"
        maxlength="40"
        hide-details="auto"
        :error-messages="errors.name"
        persistent-placeholder
        clearable
        required
      />

      <v-text-field
        v-model="formData.email"
        type="email"
        :label="$t('main.email')"
        :placeholder="$t('main.enter-your-email')"
        maxlength="45"
        hide-details="auto"
        :error-messages="errors.email"
        persistent-placeholder
        clearable
        required
      />

      <v-text-field
        v-model="formData.subject"
        :label="$t('main.subject')"
        minlength="3"
        maxlength="40"
        :placeholder="$t('main.enter-subject')"
        hide-details="auto"
        :error-messages="errors.subject"
        persistent-placeholder
        clearable
        required
      />

      <v-textarea
        v-model="formData.message"
        :label="$t('main.message')"
        minlength="100"
        :placeholder="$t('main.enter-your-message')"
        hide-details="auto"
        :error-messages="errors.message"
        persistent-placeholder
        clearable
        required
      />

      <div class="d-flex mb-4">
        <div>
          <img :src="captcha.img" alt="" />
        </div>

        <div>
          <po-button
            :title="$t('main.reload-captcha')"
            class="ms-3"
            color="primary"
            variant="tonal"
            size="small"
            @click.prevent="reloadCaptcha"
            icon
          >
            <v-icon icon="fas fa-rotate-right" />
            <span class="d-sr-only">{{ $t('main.reload-captcha') }}</span>
          </po-button>
        </div>
      </div>

      <v-text-field
        v-model="formData.captcha"
        :label="$t('main.captcha')"
        :placeholder="$t('main.validate-not-robot')"
        hide-details="auto"
        :error-messages="errors.captcha"
        persistent-placeholder
        clearable
        required
      />

      <po-button type="submit" color="primary" size="large" block :disabled="isPosting">
        <template v-if="isPosting"><v-progress-circular indeterminate /></template>
        <template v-else>{{ $t('main.send') }}</template>
      </po-button>
    </v-form>

    <v-alert
      v-if="isPosted"
      type="success"
      variant="tonal"
      class="mb-5 mx-auto"
      width="85%"
      max-width="600"
    >
      {{ $t('main.message-scheduled') }}
    </v-alert>
  </v-card>
</template>
