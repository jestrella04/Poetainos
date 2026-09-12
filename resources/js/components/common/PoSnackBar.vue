<script setup lang="ts">
import { snackBarKey } from '@/composables/keys'
import { injectStrict } from '@/composables/injectStrict'

const snackBar = injectStrict(snackBarKey)
</script>

<template>
  <v-snackbar
    v-model="snackBar.active"
    :timeout="snackBar.timeout"
    :color="snackBar.color"
    elevation="5"
    location="top"
    min-height="68"
    width="100%"
    max-width="400"
    rounded
    timer
    timer-color="blue-grey-lighten-1"
  >
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
