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

function handleClick(event: MouseEvent | KeyboardEvent): void {
  if (props.inertia === true) {
    event.preventDefault()
    visit()
  }
}
</script>

<template>
  <v-chip :href="href" @click="handleClick">
    <template v-for="(_, name) in $slots" :key="name" #[name]="slotProps">
      <slot :name="name" v-bind="slotProps ?? {}" />
    </template>
  </v-chip>
</template>
