<script setup lang="ts">
import { useFormatting } from '@/composables/useFormatting'
import type { Writing } from '@/types/models'

defineProps<{
  data: Writing
}>()

const { userDisplayName, excerpt } = useFormatting()
</script>

<template>
  <po-wrapper class="mb-8">
    <p class="text-uppercase text-eyebrow text-primary mb-4">
      {{ $t('main.pick-of-the-day') }}
    </p>

    <p class="po-prose text-display-large ma-0 mb-2">
      <po-link :href="route('writings.show', data.slug)" inertia>
        {{ data.title }}
      </po-link>
    </p>

    <p class="po-prose text-headline-small mb-6">
      {{ excerpt(data.text) }}
    </p>

    <div class="d-flex align-center ga-3">
      <po-link :href="route('users.show', data.author.username)" inertia>
        <po-avatar-award :user="data.author" avatar-size="30" avatar-color="secondary" />
      </po-link>

      <po-link :href="route('users.show', data.author.username)" class="text-on-surface" inertia>
        {{ userDisplayName(data.author) }}
      </po-link>

      <span>
        {{ $t('main.count-views', { count: data.views }) }}
      </span>
    </div>
  </po-wrapper>
</template>
