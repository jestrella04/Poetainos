<script setup lang="ts">
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import type { InertiaPageProps } from '@/types/inertia'

interface StaticPage {
  title: string
  text: string
}

const page = computed(() => usePage<InertiaPageProps<{ page: StaticPage }>>())
const data = page.value.props.page
</script>

<style scoped>
*:deep(h1),
*:deep(h2),
*:deep(h3),
*:deep(h4),
*:deep(h5),
*:deep(h6) {
  margin-top: 2rem;
  margin-bottom: 1rem;
}

*:deep(p) {
  margin-bottom: 1rem;
}

*:deep(ul) {
  list-style-type: circle;
}

*:deep(ul) *:deep(li),
*:deep(ol) *:deep(li) {
  padding-left: 0.5rem;
  margin-bottom: 0.5rem;
}

*:deep(code),
*:deep(ul),
*:deep(ol) {
  margin-left: 2rem;
  margin-bottom: 1rem;
}
</style>

<template>
  <po-head></po-head>
  <v-card :title="data.title.toUpperCase()">
    <v-card-text>
      <div v-html="$helper.markdown(data.text)" class="text-justify"></div>
    </v-card-text>
  </v-card>
</template>
