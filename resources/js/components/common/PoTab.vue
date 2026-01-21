<script setup>
import { router } from '@inertiajs/vue3'

const props = defineProps({
  href: String,
  inertia: Boolean,
  method: { type: String, default: 'get' },
  data: Object,
})

function visit() {
  // use the programmatic router.visit provided by Inertia
  const visitOptions = { method: props.method }
  if (props.data) {
    visitOptions.data = props.data
  }
  router.visit(props.href, visitOptions)
}
</script>

<template>
  <template v-if="!props.inertia">
    <v-tab v-bind="$attrs" :href="props.href">
      <slot />
    </v-tab>
  </template>

  <template v-else>
    <v-tab v-bind="$attrs" :href="props.href" @click.prevent="visit">
      <slot />
    </v-tab>
  </template>
</template>
