<script setup>
import { router } from '@inertiajs/vue3'

const props = defineProps({
  href: String,
  inertia: Boolean,
  method: { type: String, default: 'get' },
  data: Object,
})

function visit() {
  const visitOptions = { method: props.method }
  if (props.data) {
    visitOptions.data = props.data
  }
  router.visit(props.href, visitOptions)
}
</script>

<template>
  <template v-if="!inertia">
    <v-list-item :href="href">
      <slot />
    </v-list-item>
  </template>

  <template v-else>
    <v-list-item :href="href" @click.prevent="visit">
      <slot />
    </v-list-item>
  </template>
</template>