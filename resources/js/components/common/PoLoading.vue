<script setup>
</script>

<template>
  <div class="d-flex flex-column align-center justify-center h-100">
    <v-progress-circular class="mb-5" indeterminate color="primary"></v-progress-circular>
    <p class="text-disabled">{{ $t('main.loading') }}</p>
  </div>
</template>