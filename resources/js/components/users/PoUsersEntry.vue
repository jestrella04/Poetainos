<script setup>
//import { ref } from 'vue'

defineProps({
  alone: { type: Boolean, default: true },
  data: { type: Object, required: true },
})

</script>

<template>
  <v-card rounded elevation="2" class="mb-5">
    <v-card-text class="pos-relative">
      <div class="d-flex ga-4 mb-2">
        <div>
          <po-avatar size="48" color="secondary" :user="data" />
        </div>

        <div>
          <p class="font-weight-bold">
            <po-link v-if="!alone" :href="$route('users.show', data.username)" class="stretched" inertia>
              {{ $helper.userDisplayName(data) }}
            </po-link>
            <span v-else>{{ $helper.userDisplayName(data) }}</span>
          </p>
          <p class="text-medium-emphasis">@{{ data.username }}</p>
        </div>
      </div>

      <p>{{ data.bio }}</p>
    </v-card-text>

    <v-divider></v-divider>
    <v-card-actions>
      <div class="d-flex w-100 mx-auto align-items-center text-caption text-center">
        <div class="flex-grow-1">
          <div class="d-flex flex-column">
            <div><v-icon icon="fas fa-feather"></v-icon></div>
            <div>{{ $helper.readable(data.writings_count) }}</div>
          </div>
        </div>

        <div class="flex-grow-1">
          <div class="d-flex flex-column">
            <div><v-icon icon="fas fa-fan"></v-icon></div>
            <div>{{ $helper.readable(data.awards_count) }}</div>
          </div>
        </div>

        <div class="flex-grow-1">
          <div class="d-flex flex-column">
            <div><v-icon icon="fas fa-heart"></v-icon></div>
            <div>{{ $helper.readable(data.likes_count) }}</div>
          </div>
        </div>

        <div class="flex-grow-1">
          <div class="d-flex flex-column">
            <div><v-icon icon="fas fa-comment"></v-icon></div>
            <div>{{ $helper.readable(data.comments_count) }}</div>
          </div>
        </div>

        <div class="flex-grow-1">
          <div class="d-flex flex-column">
            <div><v-icon icon="fas fa-bookmark"></v-icon></div>
            <div>{{ $helper.readable(data.shelf_count) }}</div>
          </div>
        </div>

        <div class="flex-grow-1">
          <div class="d-flex flex-column">
            <div><v-icon icon="fas fa-eye"></v-icon></div>
            <div>{{ $helper.readable(data.profile_views) }}</div>
          </div>
        </div>

        <div v-if="alone" class="flex-grow-1">
          <div class="d-flex flex-column">
            <div><v-icon icon="fas fa-dove"></v-icon></div>
            <div>{{ data.aura }}</div>
          </div>
        </div>
      </div>
    </v-card-actions>
  </v-card>
</template>