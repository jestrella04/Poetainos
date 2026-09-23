<script setup lang="ts">
import { useFormatting } from '@/composables/useFormatting'
import { useTypeGuards } from '@/composables/useTypeGuards'
import type { User } from '@/types/models'

defineProps<{
  data: User
}>()

const { userDisplayName } = useFormatting()
const { strNullOrEmpty } = useTypeGuards()
</script>

<template>
  <po-card
    variant="text"
    class="border-b-md"
    :href="route('users.show', data.username)"
    height="100%"
    inertia
  >
    <v-card-text class="d-flex flex-column pa-6 h-100">
      <div class="d-flex align-center ga-4 mb-4">
        <po-avatar-award :user="data" avatar-size="64" avatar-color="secondary" />

        <div>
          <p class="text-headline-large po-prose ma-0">
            {{ userDisplayName(data) }}
          </p>

          <p class="text-medium-emphasis ma-0">
            @{{ data.username }}
            <template v-if="!strNullOrEmpty(data.location)"> {{ data.location }}</template>
          </p>
        </div>
      </div>

      <p v-if="!strNullOrEmpty(data.bio)" class="text-title-large po-prose ma-0 mb-4">
        {{ data.bio }}
      </p>

      <div class="d-inline-flex align-center text-medium-emphasis ga-3 mt-auto">
        <span>
          {{ $t('main.count-writings', { count: data.writings_count }, data.writings_count) }}
        </span>
        <span>{{ $t('main.count-likes', { count: data.likes_count }, data.likes_count) }}</span>
      </div>
    </v-card-text>
  </po-card>
</template>
