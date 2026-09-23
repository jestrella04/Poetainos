<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import PoLayoutAdmin from '../layouts/PoLayoutAdmin.vue'
import { useServerTable } from '@/composables/useServerTable'
import type { DataTableHeader } from 'vuetify'
import type { InertiaPageProps } from '@/types/inertia'

defineOptions({
  layout: PoLayoutAdmin
})

interface TagAdmin {
  id: number
  name: string
  slug: string
}

const { t } = useI18n()
const page = usePage<InertiaPageProps<{ total: number }>>()
const headers: DataTableHeader[] = [
  { title: t('main.id'), align: 'start', sortable: false, key: 'id' },
  { title: t('main.name'), align: 'start', sortable: false, key: 'name' },
  { title: t('main.actions'), align: 'start', sortable: false, key: 'actions' }
]
const { items, totalItems, isLoading, loadItems } = useServerTable<TagAdmin>(
  'admin.tags',
  page.props.total
)
</script>

<template>
  <po-wrapper>
    <v-card-title>{{ $t('tags.tags') }}</v-card-title>

    <v-data-table-server
      v-model:items-per-page="page.props.site.pagination"
      :headers="headers"
      :items-length="totalItems"
      :items="items"
      :loading="isLoading"
      item-value="id"
      @update:options="loadItems"
    >
      <template v-slot:item.name="{ item }">
        {{ item.name }}
      </template>

      <template v-slot:item.actions="{ item }">
        <div class="d-flex ga-2">
          <po-button
            :href="route('tags.show', item.slug)"
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
