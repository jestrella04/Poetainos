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
    <v-list-item :href="href">
      <slot />
    </v-list-item>
  </template>

  <template v-else>
    <v-list-item :href="href" @click.prevent="$inertia.visit(href, { method: method, data: data })">
      <slot />
    </v-list-item>
  </template>
</template>