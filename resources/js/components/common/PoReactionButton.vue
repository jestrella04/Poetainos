<script setup lang="ts">
import { loginModalKey } from '@/composables/keys'
import { injectStrict } from '@/composables/injectStrict'
import { useAuth } from '@/composables/useAuth'
import { useReactionToggle } from '@/composables/useReactionToggle'

const props = defineProps<{
  icon: string
  count: number
  isActive: boolean
  postUrl: string
  canReact: boolean
  activateTitle: string
  deactivateTitle: string
}>()

const { isAuthenticated } = useAuth()
const loginModal = injectStrict(loginModalKey)
const { count, isActive, isSubmitting, toggle } = useReactionToggle(props, {
  isAuthenticated,
  onUnauthenticated: () => {
    loginModal.value = true
  }
})
</script>

<template>
  <v-hover v-slot="{ isHovering, props: hoverProps }">
    <po-button
      v-bind="hoverProps"
      color="primary"
      variant="tonal"
      :title="isActive ? deactivateTitle : activateTitle"
      :loading="isSubmitting"
      :prepend-icon="`${isHovering === true || isActive ? 'fas' : 'far'} ${icon}`"
      @click="toggle"
    >
      {{ count }}
    </po-button>
  </v-hover>
</template>
