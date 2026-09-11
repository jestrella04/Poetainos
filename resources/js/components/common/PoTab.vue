<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import type { Method, RequestPayload, VisitOptions } from '@inertiajs/core'

const props = withDefaults(
  defineProps<{
    href?: string
    inertia?: boolean
    method?: Method
    data?: RequestPayload
  }>(),
  {
    method: 'get'
  }
)

function visit() {
  // use the programmatic router.visit provided by Inertia
  if (!props.href) {
    return
  }

  const visitOptions: VisitOptions = { method: props.method }
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
