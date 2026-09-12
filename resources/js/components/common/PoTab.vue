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
