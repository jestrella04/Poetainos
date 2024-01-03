<script setup></script>

<template>
  <v-card :title="$t('main.contact-form').toUpperCase()">
    <v-form @submit.prevent class="px-5 pb-5">
      <v-text-field :label="$t('main.name')" :placeholder="$t('main.enter-your-name')" persistent-placeholder
        clearable></v-text-field>

      <v-text-field type="email" :label="$t('main.email')" :placeholder="$t('main.enter-your-email')"
        persistent-placeholder clearable></v-text-field>

      <v-text-field :label="$t('main.subject')" :placeholder="$t('main.enter-subject')" persistent-placeholder
        clearable></v-text-field>

      <v-textarea :label="$t('main.message')" :placeholder="$t('main.enter-your-message')" persistent-placeholder
        clearable></v-textarea>

      <po-button type="submit" color="primary" size="large" block>{{ $t('main.send') }}</po-button>
    </v-form>
  </v-card>
</template>