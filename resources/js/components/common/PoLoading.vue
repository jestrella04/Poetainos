<script setup>
</script>

<template>
  <po-wrapper class="d-flex flex-column align-center justify-center w-100">
    <v-progress-circular class="mb-5" indeterminate color="primary"></v-progress-circular>
    <p class="text-disabled">{{ $t('main.loading') }}</p>
  </po-wrapper>
</template>