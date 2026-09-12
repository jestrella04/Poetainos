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
