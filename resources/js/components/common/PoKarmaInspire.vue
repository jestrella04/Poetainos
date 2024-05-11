<script setup>
import { inject } from 'vue'

defineProps({
  tooltip: {
    type: Boolean,
    default: false,
  }
})

const karma = inject('karma')
</script>

<style scoped>
p {
  margin-bottom: 1rem;
}
</style>

<template>
  <template v-if="tooltip">
    <span>{{ $t('main.karma-points', { 'karma': karma.grade }) }}&nbsp;</span>
    <template v-if="'error' !== karma.label">
      <span>{{ $t('main.karma-inspire') }}&nbsp;</span>
    </template>
  </template>

  <template v-else>
    <p>
      <span>{{ $t('main.karma-inspire') }}&nbsp;</span>
      <span v-if="'error' == karma.label">{{ $t('main.karma-low-action') }}</span>
      <span v-if="'warning' == karma.label">{{ $t('main.karma-mid-action') }}</span>
      <span>&nbsp;{{ $t('main.karma-actions') }}</span>
    </p>
  </template>
</template>
