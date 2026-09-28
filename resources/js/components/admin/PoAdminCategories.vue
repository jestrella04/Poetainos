<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import PoLayoutAdmin from '../layouts/PoLayoutAdmin.vue'
import PoAdminTitle from './partials/PoAdminTitle.vue'
import PoAdminCategoryDialog from './partials/PoAdminCategoryDialog.vue'
import PoAdminDeleteDialog from './partials/PoAdminDeleteDialog.vue'
import { useServerTable } from '@/composables/useServerTable'
import { useRowDialog } from '@/composables/useRowDialog'
import { useDates } from '@/composables/useDates'
import type { DataTableHeader } from 'vuetify'
import type { InertiaPageProps } from '@/types/inertia'

defineOptions({
  layout: PoLayoutAdmin
})

interface CategoryOption {
  id: number
  name: string
}

interface CategoryAdmin extends CategoryOption {
  slug: string
  parent_id: number | null
  description: string | null
  created_at: string
}

const { t } = useI18n()
const { toLocaleDate } = useDates()
const page = usePage<InertiaPageProps<{ total: number; parentOptions: CategoryOption[] }>>()
const headers: DataTableHeader[] = [
  { title: t('main.id'), align: 'start', sortable: false, key: 'id' },
  { title: t('main.name'), align: 'start', sortable: false, key: 'name' },
  { title: t('main.parent'), align: 'start', sortable: false, key: 'parent' },
  { title: t('main.created-at'), align: 'start', sortable: false, key: 'created_at' },
  { title: t('main.actions'), align: 'start', sortable: false, key: 'actions' }
]
const { items, totalItems, isLoading, loadItems, reload } = useServerTable<CategoryAdmin>(
  'admin.categories',
  () => page.props.total
)
const categoryDialog = useRowDialog<CategoryAdmin>()
const deleteDialog = useRowDialog<CategoryAdmin>()
</script>

<template>
  <po-wrapper>
    <div class="d-flex align-center justify-space-between">
      <po-admin-title :title="$t('categories.category')" />

      <po-button
        id="admin-category-create"
        color="primary"
        prepend-icon="fas fa-plus"
        @click="categoryDialog.open()"
      >
        {{ $t('categories.create-category') }}
      </po-button>
    </div>

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

      <template v-slot:item.parent="{ item }">
        {{ item.parent_id }}
      </template>

      <template v-slot:item.created_at="{ item }">
        {{ toLocaleDate(item.created_at) }}
      </template>

      <template v-slot:item.actions="{ item }">
        <div class="d-flex ga-2">
          <po-button
            :href="route('categories.show', item.slug)"
            size="x-small"
            color="secondary"
            icon
            inertia
          >
            <v-icon icon="fas fa-eye" />
          </po-button>

          <po-button
            size="x-small"
            color="secondary"
            icon
            :id="`admin-edit-${item.id}`"
            :aria-label="$t('categories.edit-category')"
            @click="categoryDialog.open(item)"
          >
            <v-icon icon="fas fa-edit" />
          </po-button>

          <po-button
            :id="`admin-delete-${item.id}`"
            size="x-small"
            color="secondary"
            icon
            :aria-label="$t('main.delete')"
            @click="deleteDialog.open(item)"
          >
            <v-icon icon="fas fa-trash" />
          </po-button>
        </div>
      </template>
    </v-data-table-server>

    <po-admin-category-dialog
      v-if="categoryDialog.isOpen.value"
      v-model="categoryDialog.isOpen.value"
      :category="categoryDialog.row.value"
      :parent-options="page.props.parentOptions"
      @saved="reload"
    />

    <po-admin-delete-dialog
      v-if="deleteDialog.isOpen.value && deleteDialog.row.value !== null"
      v-model="deleteDialog.isOpen.value"
      :url="route('admin.categories.destroy', deleteDialog.row.value.slug)"
      warning-key="categories.delete-category-warning"
      @deleted="reload"
    />
  </po-wrapper>
</template>
