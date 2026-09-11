<script setup lang="ts">
import { useFormatting } from '@/composables/useFormatting'

withDefaults(
  defineProps<{
    message?: string
  }>(),
  {
    message: ''
  }
)

const { asset } = useFormatting()
</script>

<template>
  <div class="text-center">
    <p v-if="'' !== message" class="text-medium-emphasis mb-5">
      {{ message }}
    </p>

    <div class="d-inline-flex flex-wrap justify-center ga-4">
      <div>
        <po-button
          icon
          color="primary"
          :href="route('social.login', { service: 'facebook', redirect: asset($page.url) })"
          :title="$t('accounts.continue-with-facebook')"
        >
          <v-icon icon="fab fa-facebook-f"></v-icon>
        </po-button>
      </div>

      <div>
        <po-button
          icon
          color="primary"
          :href="route('social.login', { service: 'twitter', redirect: asset($page.url) })"
          :title="$t('accounts.continue-with-x-twitter')"
        >
          <v-icon icon="fab fa-x-twitter"></v-icon>
        </po-button>
      </div>

      <div>
        <po-button
          icon
          color="primary"
          :href="route('social.login', { service: 'google', redirect: asset($page.url) })"
          :title="$t('accounts.continue-with-google')"
        >
          <v-icon icon="fab fa-google"></v-icon>
        </po-button>
      </div>

      <div>
        <po-button
          icon
          color="primary"
          :href="route('login', { isEmail: true, redirect: asset($page.url) })"
          :title="$t('accounts.continue-with-email')"
          inertia
        >
          <v-icon icon="fas fa-at"></v-icon>
        </po-button>
      </div>
    </div>
  </div>
</template>
