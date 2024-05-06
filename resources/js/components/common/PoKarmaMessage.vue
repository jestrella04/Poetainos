<script setup>
    defineProps({
        karma: {type: Object, required: true},
    })
</script>

<template>
  <p>
    <span>{{ $t('main.karma-points', { 'karma': karma.grade }) }}</span>
    <span v-if="'error' === karma.label">&nbsp;{{ $t('main.karma-low') }}</span>
    <span v-else-if="'warning' === karma.label">&nbsp;{{ $t('main.karma-mid') }}</span>
    <span v-else-if="'success' === karma.label">&nbsp;{{ $t('main.karma-high') }}</span>
  </p>
</template>
