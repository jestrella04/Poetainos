<script setup>
defineProps({
  href: String,
  inertia: Boolean,
  method: { type: String, default: 'get' },
  data: Object,
})
</script>

<template>
  <template v-if="!inertia">
    <v-chip :href="href">
      <slot />
    </v-chip>
  </template>

  <template v-else>
    <v-chip :href="href" @click.prevent="$inertia.visit(href, { method: method, data: data })">
      <slot />
    </v-chip>
  </template>
</template>
