<script setup>
defineProps({
  href: String,
  inertia: Boolean,
})
</script>

<template>
  <template v-if="!inertia">
    <a :href="href">
      <slot />
    </a>
  </template>

  <template v-else>
    <a :href="href" @click.prevent="$inertia.get(href)">
      <slot />
    </a>
  </template>
</template>
