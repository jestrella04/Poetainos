<script setup lang="ts">
withDefaults(
  defineProps<{
    msgTitle: string
    msgBody: string
    icon?: string
  }>(),
  {
    icon: ''
  }
)
</script>

<template>
  <div class="text-center">
    <p class="font-weight-bold">{{ msgTitle }}</p>
    <p class="text-medium-emphasis">{{ msgBody }}</p>

    <v-icon v-if="'' !== icon" :icon="icon" size="96" class="text-disabled my-10" />

    <slot />
  </div>
</template>
