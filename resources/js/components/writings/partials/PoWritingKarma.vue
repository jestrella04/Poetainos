<script setup>
import { inject } from 'vue'

const author = inject('author')
const karma = inject('karma')
</script>

<style scoped>
.karma-icon {
  top: 1rem;
  right: 1rem;
}
</style>

<template>
  <div class="karma-icon position-absolute">
    <v-tooltip location="bottom" max-width="400px" open-on-click>
      <template v-slot:activator="{ props }">
        <v-icon
          icon="fas fa-yin-yang"
          :color="$helper.karmaLabel(author.karma, karma)"
          v-bind="props"
        ></v-icon>
      </template>

      <po-karma-inspire tooltip></po-karma-inspire>
    </v-tooltip>
  </div>
</template>
