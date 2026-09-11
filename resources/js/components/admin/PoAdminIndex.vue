<script setup lang="ts">
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import PoLayoutAdmin from '../layouts/PoLayoutAdmin.vue'
import type { InertiaPageProps } from '@/types/inertia'

defineOptions({
  layout: PoLayoutAdmin
})

interface Counter {
  title: string
  count: number
}

const page = computed(() => usePage<InertiaPageProps<{ counters: Record<string, Counter> }>>())
const counters = page.value.props.counters
</script>

<style scoped>
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
          <p class="text-h3">{{ $helper.readable(counter.count) }}</p>
          <span class="text-caption">{{ counter.title }}</span>
        </v-card>
      </template>
    </div>
  </po-wrapper>
</template>
