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
    <v-btn :href="href">
      <slot />
    </v-btn>
  </template>

  <template v-else>
    <v-btn :href="href" @click.prevent="$inertia.visit(href, { method: method, data: data })">
      <slot />
    </v-btn>
  </template>
</template>
