<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = computed(() => usePage())
</script>

<template>
  <v-card title="Pages List">
    <v-card-text>
      <v-list>
        <template v-for="data in page.props.pages" :key="data.slug">
          <po-list-item :href="route('pages.show', data.slug)" inertia>{{ data.title }}</po-list-item>
        </template>
      </v-list>
    </v-card-text>
  </v-card>
</template>