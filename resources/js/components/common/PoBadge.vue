<script setup>
defineProps({
  count: { type: Number, default: 0 }
})
</script>

<template>
  <template v-if="count > 0">
    <v-badge :content="count" color="error" :max="9">
      <slot />
    </v-badge>
  </template>

  <template v-else>
    <div>
      <slot />
    </div>

  </template>
</template>