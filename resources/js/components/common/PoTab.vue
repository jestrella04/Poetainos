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
  <v-tab v-bind="$attrs" :href="props.href" @click="handleClick">
    <slot />
  </v-tab>
</template>
