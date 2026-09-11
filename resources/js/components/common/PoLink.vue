<script setup lang="ts">
import { router } from '@inertiajs/vue3'

const props = defineProps<{
  href?: string
  inertia?: boolean
}>()

function visit() {
  if (!props.href) {
    return
  }

  router.get(props.href)
}
</script>

<template>
  <template v-if="!inertia">
    <a :href="href">
      <slot />
    </a>
  </template>

  <template v-else>
    <a :href="href" @click.prevent="visit">
      <slot />
    </a>
  </template>
</template>
