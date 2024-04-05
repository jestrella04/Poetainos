<script setup>
defineProps({
  type: { type: String, default: 'image, article' },
  cols: { type: String, default: "12" },
  md: { type: String, default: "6" },
  lg: { type: String, default: "4" },
})
</script>

<template>
  <v-row>
    <v-col v-for="n in 20" :key="n" :cols="cols" :md="md" :lg="lg">
      <v-skeleton-loader class="border" :type="type" elevation="2"></v-skeleton-loader>
    </v-col>
  </v-row>
</template>