<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import PoLayoutAdmin from '../layouts/PoLayoutAdmin.vue'
import PoAdminComplaintDialog from './partials/PoAdminComplaintDialog.vue'
import { useServerTable } from '@/composables/useServerTable'
import { useRowDialog } from '@/composables/useRowDialog'
import { useDates } from '@/composables/useDates'
import type { DataTableHeader } from 'vuetify'
import type { InertiaPageProps } from '@/types/inertia'

defineOptions({
  layout: PoLayoutAdmin
})

interface ComplaintAdmin {
  id: number
  complainable_type: string
  reasons: string[]
  comment: string | null
  reported_url: string | null
  created_at: string
  closed_at: string | null
  closed_comment: string | null
}

const { t } = useI18n()
const { toLocaleDate } = useDates()
const page = usePage<InertiaPageProps<{ total: number }>>()
const headers: DataTableHeader[] = [
  { title: t('main.id'), align: 'start', sortable: false, key: 'id' },
  { title: t('main.type'), align: 'start', sortable: false, key: 'type' },
  { title: t('main.created-at'), align: 'start', sortable: false, key: 'created_at' },
  { title: t('admin.closed-at'), align: 'start', sortable: false, key: 'closed_at' },
  { title: t('main.actions'), align: 'start', sortable: false, key: 'actions' }
]
const { items, totalItems, isLoading, loadItems, reload } = useServerTable<ComplaintAdmin>(
  'admin.complaints',
  () => page.props.total
)
const complaintDialog = useRowDialog<ComplaintAdmin>()
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

      <template v-slot:item.actions="{ item }">
        <div class="d-flex ga-2">
          <po-button
            size="x-small"
            color="secondary"
            icon
            :id="`admin-complaint-${item.id}`"
            :aria-label="$t('complaints.complaint-details')"
            @click="complaintDialog.open(item)"
          >
            <v-icon :icon="item.closed_at === null ? 'fas fa-eye' : 'fas fa-circle-check'" />
          </po-button>
        </div>
      </template>
    </v-data-table-server>

    <po-admin-complaint-dialog
      v-if="complaintDialog.isOpen.value && complaintDialog.row.value !== null"
      v-model="complaintDialog.isOpen.value"
      :complaint="complaintDialog.row.value"
      @closed="reload"
    />
  </po-wrapper>
</template>
