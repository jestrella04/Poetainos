<script setup>
defineProps({
  msgTitle: { type: String, required: true },
  msgBody: { type: String, required: true },
  icon: { type: String, default: '' },
})
</script>

<template>
  <div class="text-center">
    <p class="font-weight-bold">{{ msgTitle }}</p>
    <p class="text-medium-emphasis mb-3">{{ msgBody }}</p>
    <v-icon v-if="'' !== icon" :icon="icon" size="72" class="text-medium-emphasis"></v-icon>
  </div>
</template>