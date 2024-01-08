<script setup>
function submitForm() {
  const form = document.querySelector('#contact-form')

  if (!form.checkValidity()) {
    form.reportValidity()
    return
  }

  // magic goes here
}
</script>

<template>
  <v-card :title="$t('main.contact-form').toUpperCase()">
    <v-form id="contact-form" class="px-5 pb-5" @submit.prevent="submitForm()">
      <v-text-field :label="$t('main.name')" :placeholder="$t('main.enter-your-name')" hide-details="auto"
        persistent-placeholder clearable required></v-text-field>

      <v-text-field type="email" :label="$t('main.email')" :placeholder="$t('main.enter-your-email')" hide-details="auto"
        persistent-placeholder clearable required></v-text-field>

      <v-text-field :label="$t('main.subject')" :placeholder="$t('main.enter-subject')" hide-details="auto"
        persistent-placeholder clearable required></v-text-field>

      <v-textarea :label="$t('main.message')" :placeholder="$t('main.enter-your-message')" hide-details="auto"
        persistent-placeholder clearable required></v-textarea>

      <po-button type="submit" color="primary" size="large" block>{{ $t('main.send') }}</po-button>
    </v-form>
  </v-card>
</template>