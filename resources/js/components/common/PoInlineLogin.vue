<script setup></script>

<template>
  <div class="text-center">
    <p class="text-medium-emphasis mb-3">
      {{ $t('accounts.login-before-comment') }}
    </p>

    <div class="d-inline-flex flex-wrap justify-center ga-2">
      <div>
        <po-button color="primary" :href="$route('social.login', 'facebook')"
          :title="$t('accounts.continue-with-facebook')">
          <v-icon icon="fab fa-facebook-f"></v-icon>
        </po-button>
      </div>

      <div>
        <po-button color="primary" :href="$route('social.login', 'twitter')"
          :title="$t('accounts.continue-with-x-twitter')">
          <v-icon icon="fab fa-x-twitter"></v-icon>
        </po-button>
      </div>

      <div>
        <po-button color="primary" :href="$route('social.login', 'google')" :title="$t('accounts.continue-with-google')">
          <v-icon icon="fab fa-google"></v-icon>
        </po-button>
      </div>

      <div>
        <po-button color="primary" :href="$route('login', { 'isEmail': true })" :title="$t('accounts.continue-with-email')"
          inertia>
          <v-icon icon="fas fa-at"></v-icon>
        </po-button>
      </div>
    </div>
  </div>
</template>