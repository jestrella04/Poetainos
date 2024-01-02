<script setup>

defineProps({
  alone: { type: Boolean, default: true },
  data: { type: Object, required: true },
})
</script>

<template>
  <div class="d-flex justify-center ga-8 mx-auto text-medium-emphasis text-caption text-center">
    <div class="d-flex flex-column">
      <div><v-icon icon="fas fa-feather"></v-icon></div>
      <div>{{ $helper.readable(data.writings_count) }}</div>
    </div>

    <div class="d-flex flex-column">
      <div><v-icon icon="fas fa-fan"></v-icon></div>
      <div>{{ $helper.readable(data.awards_count) }}</div>
    </div>

    <div class="d-flex flex-column">
      <div><v-icon icon="fas fa-heart"></v-icon></div>
      <div>{{ $helper.readable(data.likes_count) }}</div>
    </div>

    <div class="d-flex flex-column">
      <div><v-icon icon="fas fa-comment"></v-icon></div>
      <div>{{ $helper.readable(data.comments_count) }}</div>
    </div>

    <div class="d-flex flex-column">
      <div><v-icon icon="fas fa-bookmark"></v-icon></div>
      <div>{{ $helper.readable(data.shelf_count) }}</div>
    </div>

    <div class="d-flex flex-column">
      <div><v-icon icon="fas fa-eye"></v-icon></div>
      <div>{{ $helper.readable(data.profile_views) }}</div>
    </div>

    <div v-if="alone" class="d-flex flex-column">
      <div><v-icon icon="fas fa-dove"></v-icon></div>
      <div>{{ data.aura }}</div>
    </div>
  </div>
</template>