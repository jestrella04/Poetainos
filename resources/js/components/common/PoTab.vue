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
    <v-tab :href="href">
      <slot />
    </v-tab>
  </template>

  <template v-else>
    <v-tab :href="href" @click.prevent="$inertia.visit(href, { method: method, data: data })">
      <slot />
    </v-tab>
  </template>
</template>
