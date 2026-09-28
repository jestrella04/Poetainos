<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import PoLayoutAdmin from '../layouts/PoLayoutAdmin.vue'
import PoAdminTitle from './partials/PoAdminTitle.vue'
import { useFormatting } from '@/composables/useFormatting'
import type { InertiaPageProps } from '@/types/inertia'

defineOptions({
  layout: PoLayoutAdmin
})

interface Counter {
  title: string
  count: number
}

const { abbreviateNumber } = useFormatting()
const page = usePage<InertiaPageProps<{ counters: Record<string, Counter> }>>()
const counters = page.props.counters
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
    <po-admin-title :title="$t('admin.summary')" />

    <div class="d-flex flex-wrap ga-5">
      <template v-for="counter in counters" :key="counter.title">
        <v-card color="primary" class="counter text-center pa-5" rounded>
          <p class="po-prose text-display-medium ma-0 mb-2">
            {{ abbreviateNumber(counter.count) }}
          </p>
          <span>{{ counter.title }}</span>
        </v-card>
      </template>
    </div>
  </po-wrapper>
</template>
