<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import type { InertiaPageProps } from '@/types/inertia'

interface PageSummary {
  title: string
  slug: string
}

const page = usePage<InertiaPageProps<{ pages: PageSummary[] }>>()
</script>

<template>
  <v-card :title="$t('pages.pages-list')">
    <v-card-text>
      <v-list>
        <template v-for="data in page.props.pages" :key="data.slug">
          <po-list-item :href="route('pages.show', data.slug)" inertia>{{
            data.title
          }}</po-list-item>
        </template>
      </v-list>
    </v-card-text>
  </v-card>
</template>
