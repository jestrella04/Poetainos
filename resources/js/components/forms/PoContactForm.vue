<script setup>
import axios from 'axios'
import { inject, onMounted, reactive, ref } from 'vue'

const helper = inject('helper')
const isPosting = ref(false)
const isPosted = ref(false)
const captcha = ref({})
const errors = ref({})
const formData = reactive({
  name: '',
  email: '',
  subject: '',
  message: '',
  key: '',
  captcha: '',
})

onMounted(() => {
  reloadCaptcha()
})

async function reloadCaptcha() {
  await axios
    .get('/captcha/api/math')
    .then((response) => {
      captcha.value = response.data
      formData.captcha = ''
    })
    .catch()
    .finally()
}

function clearErrors() {
  errors.value = {}
}

function clearInputs() {
  formData.name = ''
  formData.email = ''
  formData.subject = ''
  formData.message = ''
  formData.key = ''
  formData.captcha = ''
  reloadCaptcha()
}

function resetForm() {
  isPosted.value = false
  clearInputs()
  clearErrors()
}

async function submitForm() {
  const form = document.querySelector('#contact-form')

  if (!helper.checkFormValidity(form)) {
    return
  }

  isPosting.value = true

  await axios
    .post(form.action, {
      name: formData.name,
      email: formData.email,
      subject: formData.subject,
      message: formData.message,
      key: captcha.value.key,
      captcha: formData.captcha
    })
    .then(() => {
      resetForm()
      isPosted.value = true
    })
    .catch((error) => {
      errors.value = error.response.data.errors
      reloadCaptcha()
    })
    .finally(
      setTimeout(() => { isPosting.value = false }, 1000)
    )
}
</script>

<template>
  <po-head></po-head>
  <v-card :title="$t('main.contact-form').toUpperCase()">
    <v-form id="contact-form" :action="$route('contact.store')" class="px-5 pb-5" @submit.prevent="submitForm()">
      <v-text-field v-model="formData.name" :label="$t('main.name')" :placeholder="$t('main.enter-your-name')"
        minlength="3" maxlength="40" hide-details="auto" :error-messages="errors.name" persistent-placeholder clearable
        required></v-text-field>

      <v-text-field v-model="formData.email" type="email" :label="$t('main.email')"
        :placeholder="$t('main.enter-your-email')" maxlength="45" hide-details="auto" :error-messages="errors.email"
        persistent-placeholder clearable required></v-text-field>

      <v-text-field v-model="formData.subject" :label="$t('main.subject')" minlength="3" maxlength="40"
        :placeholder="$t('main.enter-subject')" hide-details="auto" :error-messages="errors.subject"
        persistent-placeholder clearable required></v-text-field>

      <v-textarea v-model="formData.message" :label="$t('main.message')" minlength="100"
        :placeholder="$t('main.enter-your-message')" hide-details="auto" :error-messages="errors.message"
        persistent-placeholder clearable required></v-textarea>

      <div class="d-flex">
        <div>
          <img :src="captcha.img" alt="">
        </div>

        <div>
          <po-button :title="$t('main.reload-captcha')" class="ms-3" @click.prevent="reloadCaptcha">
            <v-icon icon="fas fa-rotate-right"></v-icon>
            <span class="d-sr-only">{{ $t('main.reload-captcha') }}</span>
          </po-button>
        </div>
      </div>

      <v-text-field v-model="formData.captcha" label="Captcha" :placeholder="$t('main.validate-not-robot')"
        hide-details="auto" :error-messages="errors.captcha" persistent-placeholder clearable required></v-text-field>

      <po-button type="submit" color="primary" size="large" block :disabled="isPosting">
        <template v-if="isPosting"><v-progress-circular indeterminate></v-progress-circular></template>
        <template v-else>{{ $t('main.send') }}</template>
      </po-button>
    </v-form>

    <v-alert v-if="isPosted" type="success" variant="tonal" class="mb-5 mx-auto" style="width: 85%; max-width: 600px;">
      {{ $t('main.message-scheduled') }}
    </v-alert>
  </v-card>
</template>