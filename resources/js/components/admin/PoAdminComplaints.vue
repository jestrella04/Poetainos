<script setup lang="ts">
import { computed } from 'vue'
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

interface ComplaintAdmin {
  id: number
  complainable_type: string
  created_at: string
  closed_at: string | null
}

const { t } = useI18n()
const { toLocaleDate } = useFormatting()
const page = computed(() => usePage<InertiaPageProps<{ total: number }>>())
const headers: DataTableHeader[] = [
  { title: t('main.id'), align: 'start', sortable: false, key: 'id' },
  { title: t('main.type'), align: 'start', sortable: false, key: 'type' },
  { title: t('main.created-at'), align: 'start', sortable: false, key: 'created_at' },
  { title: t('admin.closed-at'), align: 'start', sortable: false, key: 'closed_at' },
  { title: t('main.actions'), align: 'start', sortable: false, key: 'actions' }
]
const { items, totalItems, isLoading, loadItems } = useServerTable<ComplaintAdmin>(
  'admin.complaints',
  page.value.props.total
)
</script>

<template>
  <po-wrapper>
    <v-card-title>{{ $t('complaints.complaints') }}</v-card-title>

    <v-data-table-server
      v-model:items-per-page="page.props.site.pagination"
      :headers="headers"
      :items-length="totalItems"
      :items="items"
      :loading="isLoading"
      item-value="id"
      @update:options="loadItems"
    >
      <template v-slot:item.type="{ item }">
        {{ item.complainable_type }}
      </template>

      <template v-slot:item.created_at="{ item }">
        {{ toLocaleDate(item.created_at) }}
      </template>

      <template v-slot:item.closed_at="{ item }">
        {{ item.closed_at !== null ? toLocaleDate(item.closed_at) : '' }}
      </template>

      <template v-slot:item.actions>
        <div class="d-flex ga-2">
          <po-button href="#" size="x-small" color="secondary" icon inertia>
            <v-icon icon="fas fa-eye"></v-icon>
          </po-button>

          <po-button href="#" size="x-small" color="secondary" icon inertia>
            <v-icon icon="fas fa-circle-check"></v-icon>
          </po-button>
        </div>
      </template>
    </v-data-table-server>
  </po-wrapper>
</template>
