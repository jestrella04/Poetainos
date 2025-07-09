<script setup>
defineProps({
  type: { type: String, default: 'image, article' }
})
</script>

<template>
  <div v-for="n in 20" :key="n">
    <v-skeleton-loader class="border mx-auto mb-6" :type="type" elevation="2"
      style="width:100%; max-width: 35rem;"></v-skeleton-loader>
  </div>
</template>