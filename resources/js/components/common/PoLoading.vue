<script setup>
import { inject } from 'vue';

const isLoading = inject('isLoading', false);
</script>

<template>
  <v-dialog v-model="isLoading" persistent width="auto">
    <v-card color="primary">
      <v-card-text>
        <div class="text-caption">{{ $t('main.loading') }}</div>
        <v-progress-linear indeterminate color="white" class="my-3"></v-progress-linear>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>