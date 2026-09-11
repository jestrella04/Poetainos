<script setup lang="ts">
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import PoLayoutAdmin from '../layouts/PoLayoutAdmin.vue'
import { useServerTable } from '@/composables/useServerTable'
import type { DataTableHeader } from 'vuetify'
import type { InertiaPageProps } from '@/types/inertia'
import type { UserLike } from '@/types/models'

defineOptions({
  layout: PoLayoutAdmin
})

interface WritingAdmin {
  id: number
  title: string
  slug: string
  aura: string
  created_at: string
  author: UserLike
}

const { t } = useI18n()
const page = computed(() => usePage<InertiaPageProps<{ total: number }>>())
const headers: DataTableHeader[] = [
  { title: t('main.id'), align: 'start', sortable: false, key: 'id' },
  { title: t('main.title'), align: 'start', sortable: false, key: 'title' },
  { title: t('users.author'), align: 'start', sortable: false, key: 'author' },
  { title: t('main.aura'), align: 'start', sortable: false, key: 'aura' },
  { title: t('main.created-at'), align: 'start', sortable: false, key: 'created_at' },
  { title: t('main.actions'), align: 'start', sortable: false, key: 'actions' }
]
const { items, totalItems, isLoading, loadItems } = useServerTable<WritingAdmin>(
  'admin.writings',
  page.value.props.total
)
</script>

<template>
  <po-wrapper>
    <v-card-title>{{ $t('writings.writings') }}</v-card-title>

    <v-data-table-server
      v-model:items-per-page="page.props.site.pagination"
      :headers="headers"
      :items-length="totalItems"
      :items="items"
      :loading="isLoading"
      item-value="id"
      @update:options="loadItems"
    >
      <template v-slot:item.author="{ item }">
        {{ $helper.userDisplayName(item.author) }}
      </template>

      <template v-slot:item.created_at="{ item }">
        {{ $helper.toLocaleDate(item.created_at) }}
      </template>

      <template v-slot:item.actions="{ item }">
        <div class="d-flex ga-2">
          <po-button
            :href="route('writings.show', item.slug)"
            size="x-small"
            color="secondary"
            icon
            inertia
          >
            <v-icon icon="fas fa-eye"></v-icon>
          </po-button>

          <po-button
            :href="route('writings.edit', item.slug)"
            size="x-small"
            color="secondary"
            icon
            inertia
          >
            <v-icon icon="fas fa-edit"></v-icon>
          </po-button>

          <po-button
            :href="route('writings.edit', item.slug)"
            size="x-small"
            color="secondary"
            icon
            inertia
          >
            <v-icon icon="fas fa-trash"></v-icon>
          </po-button>
        </div>
      </template>
    </v-data-table-server>
  </po-wrapper>
</template>
