<script setup>
import { inject } from 'vue'

const karma = inject('karma')
</script>

<style scoped>
p {
  margin-bottom: 1rem;
}
</style>

<template>
  <div>
    <p>{{ $t('main.karma-inspire-1') }}</p>
    <p>{{ $t('main.karma-inspire-2') }}</p>
    <p>{{ $t('main.karma-inspire-3') }}</p>
    <p>{{ $t('main.karma-inspire-4') }}</p>
    <p>{{ $t('main.karma-inspire-5') }}</p>

    <po-button color="primary" size="large" block @click="karma.ignore = true">
      {{ $t('writings.continue-publishing') }}
    </po-button>
  </div>
</template>
