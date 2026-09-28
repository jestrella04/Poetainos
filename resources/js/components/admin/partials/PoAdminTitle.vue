<script setup lang="ts">
defineProps<{
  title: string
}>()
</script>

<template>
  <p class="po-prose text-title-large text-uppercase text-eyebrow text-primary">
    {{ title }}
  </p>
</template>
