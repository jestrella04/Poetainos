<script setup>
import PoUsersStats from './PoUsersStats.vue'

defineProps({
  data: { type: Object, required: true },
})

</script>

<template>
  <v-card rounded elevation="2">
    <v-card-text class="pos-relative">
      <div class="d-flex ga-4 mb-2">
        <div>
          <po-avatar size="48" color="secondary" :user="data" />
        </div>

        <div>
          <p class="font-weight-bold">
            <po-link :href="route('users.show', data.username)" class="stretched" inertia>
              {{ $helper.userDisplayName(data) }}
            </po-link>
          </p>
          <p class="text-medium-emphasis">@{{ data.username }}</p>
        </div>
      </div>

      <p>{{ data.bio }}</p>
    </v-card-text>

    <v-divider></v-divider>
    <v-card-actions>
      <po-users-stats :data="data" :alone="false" />
    </v-card-actions>
  </v-card>
</template>