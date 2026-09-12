<script setup lang="ts">
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import PoLayoutAdmin from '../layouts/PoLayoutAdmin.vue'
import { useFormatting } from '@/composables/useFormatting'
import type { InertiaPageProps } from '@/types/inertia'

defineOptions({
  layout: PoLayoutAdmin
})

interface Counter {
  title: string
  count: number
}

const { readable } = useFormatting()
const page = computed(() => usePage<InertiaPageProps<{ counters: Record<string, Counter> }>>())
const counters = page.value.props.counters
</script>

<style scoped>
/* Keeps stat figures from reflowing at odd widths; no Vuetify sizing utility fits this exact width. */
.counter {
  min-width: 155px;
  text-align: center;
}
</style>

<template>
  <po-wrapper>
    <v-card-title>{{ $t('admin.summary') }}</v-card-title>

    <div class="d-flex flex-wrap ga-5">
      <template v-for="counter in counters" :key="counter.title">
        <v-card color="primary" class="counter pa-5" rounded>
          <p class="text-h3">{{ readable(counter.count) }}</p>
          <span class="text-caption">{{ counter.title }}</span>
        </v-card>
      </template>
    </div>
  </po-wrapper>
</template>
