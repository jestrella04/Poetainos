<script setup>
import { inject } from 'vue'

const snackBar = inject('snackBar')
</script>

<style scoped>
.v-snackbar {
  width: 100%;
  max-width: 400px;
  margin: 1rem auto;
}
</style>

<template>
  <v-snackbar v-model="snackBar.active" :timeout="snackBar.timeout" :color="snackBar.color" elevation="24" location="top"
    multi-line rounded timer="blue-grey-lighten-1">
    <div class="d-flex ga-4">
      <div>
        <v-avatar size="48" color="primary" :image="snackBar.avatar" />
      </div>

      <div class="flex-grow-1">
        <p class="text-caption">{{ $t(snackBar.message) }}</p>
      </div>
    </div>
  </v-snackbar>
</template>