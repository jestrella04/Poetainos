<script setup lang="ts">
import type { Method, RequestPayload } from '@inertiajs/core'
import { useInertiaVisit } from '@/composables/useInertiaVisit'

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

const { visit } = useInertiaVisit(props)
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
