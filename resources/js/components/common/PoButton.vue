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

const { handleClick } = useInertiaVisit(props)
</script>

<template>
  <v-btn :href="href" @click="handleClick">
    <slot />
  </v-btn>
</template>
