<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useForm, useHttp } from '@inertiajs/vue3'
import { useFormValidation } from '@/composables/useFormValidation'
import { useRequestFailure } from '@/composables/useRequestFailure'

interface Captcha {
  key: string
  img: string
}

const { isSubmittedFormValid } = useFormValidation()
const { onHttpException, onNetworkError, whenSettled } = useRequestFailure()
const captchaRequest = useHttp<Record<string, never>, Captcha>()
const captcha = ref<Captcha>({ key: '', img: '' })
const form = useForm({
  name: '',
  email: '',
  subject: '',
  message: '',
  captcha: ''
})

onMounted(() => {
  void reloadCaptcha()
})

async function reloadCaptcha(): Promise<void> {
  await whenSettled(
    captchaRequest.get('/captcha/api/math', {
      onSuccess: (data) => {
        captcha.value = data
        form.captcha = ''
      }
    })
  )
}

function submitForm(event: Event): void {
  if (isSubmittedFormValid(event) === false) {
    return
  }

  // Each captcha answers once, so a fresh one is needed whatever the outcome
  form
    .transform((data) => ({ ...data, key: captcha.value.key }))
    .post(route('contact.store'), {
      preserveScroll: true,
      onHttpException,
      onNetworkError,
      onSuccess: () => {
        form.reset()
      },
      onFinish: () => {
        void reloadCaptcha()
      }
    })
}
</script>

<template>
  <po-head />
  <v-card :title="$t('main.contact-form').toUpperCase()">
    <v-form id="contact-form" class="px-5 pb-5" @submit.prevent="submitForm">
      <v-text-field
        v-model="form.name"
        :label="$t('main.name')"
        :placeholder="$t('main.enter-your-name')"
        minlength="3"
        maxlength="40"
        hide-details="auto"
        :error-messages="form.errors.name"
        persistent-placeholder
        clearable
        required
      />

      <v-text-field
        v-model="form.email"
        type="email"
        :label="$t('main.email')"
        :placeholder="$t('main.enter-your-email')"
        maxlength="45"
        hide-details="auto"
        :error-messages="form.errors.email"
        persistent-placeholder
        clearable
        required
      />

      <v-text-field
        v-model="form.subject"
        :label="$t('main.subject')"
        minlength="3"
        maxlength="40"
        :placeholder="$t('main.enter-subject')"
        hide-details="auto"
        :error-messages="form.errors.subject"
        persistent-placeholder
        clearable
        required
      />

      <v-textarea
        v-model="form.message"
        :label="$t('main.message')"
        minlength="100"
        :placeholder="$t('main.enter-your-message')"
        hide-details="auto"
        :error-messages="form.errors.message"
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
        v-model="form.captcha"
        :label="$t('main.captcha')"
        :placeholder="$t('main.validate-not-robot')"
        hide-details="auto"
        :error-messages="form.errors.captcha"
        persistent-placeholder
        clearable
        required
      />

      <po-button type="submit" color="primary" size="large" block :disabled="form.processing">
        <template v-if="form.processing"><v-progress-circular indeterminate /></template>
        <template v-else>{{ $t('main.send') }}</template>
      </po-button>
    </v-form>

    <v-alert
      v-if="form.wasSuccessful"
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
