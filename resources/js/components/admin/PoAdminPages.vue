<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import PoLayoutAdmin from '../layouts/PoLayoutAdmin.vue'
import { useServerTable } from '@/composables/useServerTable'
import { useFormatting } from '@/composables/useFormatting'
import type { DataTableHeader } from 'vuetify'
import type { InertiaPageProps } from '@/types/inertia'

defineOptions({
  layout: PoLayoutAdmin
})

interface PageAdmin {
  id: number
  title: string
  slug: string
  created_at: string
}

const { t } = useI18n()
const { toLocaleDate } = useFormatting()
const page = usePage<InertiaPageProps<{ total: number }>>()
const headers: DataTableHeader[] = [
  { title: t('main.id'), align: 'start', sortable: false, key: 'id' },
  { title: t('main.title'), align: 'start', sortable: false, key: 'title' },
  { title: t('main.created-at'), align: 'start', sortable: false, key: 'created_at' },
  { title: t('main.actions'), align: 'start', sortable: false, key: 'actions' }
]
const { items, totalItems, isLoading, loadItems } = useServerTable<PageAdmin>(
  'admin.pages',
  page.props.total
)
</script>

<template>
  <po-wrapper>
    <v-card-title>{{ $t('pages.pages') }}</v-card-title>

    <v-data-table-server
      v-model:items-per-page="page.props.site.pagination"
      :headers="headers"
      :items-length="totalItems"
      :items="items"
      :loading="isLoading"
      item-value="id"
      @update:options="loadItems"
    >
      <template v-slot:item.created_at="{ item }">
        {{ toLocaleDate(item.created_at) }}
      </template>

      <template v-slot:item.actions="{ item }">
        <div class="d-flex ga-2">
          <po-button
            :href="route('pages.show', item.slug)"
            size="x-small"
            color="secondary"
            icon
            inertia
          >
            <v-icon icon="fas fa-eye" />
          </po-button>

          <po-button href="#" size="x-small" color="secondary" icon inertia>
            <v-icon icon="fas fa-edit" />
          </po-button>

          <po-button href="#" size="x-small" color="secondary" icon inertia>
            <v-icon icon="fas fa-trash" />
          </po-button>
        </div>
      </template>
    </v-data-table-server>
  </po-wrapper>
</template>
