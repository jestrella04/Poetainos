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
