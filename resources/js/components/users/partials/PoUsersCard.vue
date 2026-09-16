<script setup lang="ts">
import PoUsersStats from './PoUsersStats.vue'
import { useFormatting } from '@/composables/useFormatting'
import type { User } from '@/types/models'

defineProps<{
  data: User
}>()

const { userDisplayName } = useFormatting()
</script>

<template>
  <v-card rounded elevation="2" class="user-container">
    <v-card-text class="position-relative">
      <div class="d-flex ga-4 mb-2">
        <po-avatar-award :user="data" avatar-size="48" avatar-color="secondary" />

        <div>
          <p class="font-weight-bold">
            <po-link :href="route('users.show', data.username)" class="stretched" inertia>
              {{ userDisplayName(data) }}
            </po-link>
          </p>
          <p class="text-medium-emphasis">@{{ data.username }}</p>
        </div>
      </div>

      <p>{{ data.bio }}</p>
    </v-card-text>

    <v-divider />
    <v-card-actions>
      <po-users-stats :data="data" />
    </v-card-actions>
  </v-card>
</template>
