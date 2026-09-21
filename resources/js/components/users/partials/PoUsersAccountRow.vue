<script setup lang="ts">
defineProps<{
  title: string
  subtitle: string
  href?: string
  disabled?: boolean
}>()
</script>

<template>
  <po-card
    :href="href"
    :disabled="disabled"
    :inertia="href !== undefined"
    variant="text"
    class="w-100 border-b-md"
  >
    <v-card-item class="pa-4">
      <p class="text-title-large po-prose ma-0 mb-1">{{ title }}</p>
      <p class="text-medium-emphasis ma-0">{{ subtitle }}</p>

      <template #append>
        <v-icon v-if="href !== undefined" icon="fas fa-chevron-right" size="small" />
        <slot v-else />
      </template>
    </v-card-item>
  </po-card>
</template>
