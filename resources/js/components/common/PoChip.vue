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
  <template v-if="!inertia">
    <v-chip :href="href">
      <slot />
    </v-chip>
  </template>

  <template v-else>
    <v-chip :href="href" @click.prevent="visit">
      <slot />
    </v-chip>
  </template>
</template>
