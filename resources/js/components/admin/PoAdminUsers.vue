<script setup lang="ts">
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import PoLayoutAdmin from '../layouts/PoLayoutAdmin.vue'
import { useServerTable } from '@/composables/useServerTable'
import type { DataTableHeader } from 'vuetify'
import type { InertiaPageProps } from '@/types/inertia'

defineOptions({
  layout: PoLayoutAdmin
})

interface UserAdmin {
  id: number
  username: string
  name: string | null
  email: string
  created_at: string
  aura: string
  karma: string | null
}

const { t } = useI18n()
const page = computed(() => usePage<InertiaPageProps<{ total: number }>>())
const headers: DataTableHeader[] = [
  { title: t('main.id'), align: 'start', sortable: false, key: 'id' },
  { title: t('users.username'), align: 'start', sortable: false, key: 'username' },
  { title: t('admin.full-name'), align: 'start', sortable: false, key: 'name' },
  { title: t('main.email'), align: 'start', sortable: false, key: 'email' },
  { title: t('main.created-at'), align: 'start', sortable: false, key: 'created_at' },
  { title: t('main.aura'), align: 'start', sortable: false, key: 'aura' },
  { title: t('main.karma'), align: 'start', sortable: false, key: 'karma' },
  { title: t('main.actions'), align: 'start', sortable: false, key: 'actions' }
]
const { items, totalItems, isLoading, loadItems } = useServerTable<UserAdmin>(
  'admin.users',
  page.value.props.total
)
</script>

<template>
  <po-wrapper>
    <v-card-title>{{ $t('users.users') }}</v-card-title>

    <v-data-table-server
      v-model:items-per-page="page.props.site.pagination"
      :headers="headers"
      :items-length="totalItems"
      :items="items"
      :loading="isLoading"
      item-value="id"
      @update:options="loadItems"
    >
      <template v-slot:item.username="{ item }">
        {{ item.username }}
      </template>

      <template v-slot:item.name="{ item }">
        {{ item.name }}
      </template>

      <template v-slot:item.email="{ item }">
        {{ item.email }}
      </template>

      <template v-slot:item.created_at="{ item }">
        {{ $helper.toLocaleDate(item.created_at) }}
      </template>

      <template v-slot:item.aura="{ item }">
        {{ item.aura }}
      </template>

      <template v-slot:item.karma="{ item }">
        {{ item.karma }}
      </template>

      <template v-slot:item.actions="{ item }">
        <div class="d-flex ga-2">
          <po-button
            :href="route('users.show', item.username)"
            size="x-small"
            color="secondary"
            icon
            inertia
          >
            <v-icon icon="fas fa-eye"></v-icon>
          </po-button>

          <po-button
            :href="route('users.edit', item.username)"
            size="x-small"
            color="secondary"
            icon
            inertia
          >
            <v-icon icon="fas fa-edit"></v-icon>
          </po-button>

          <po-button href="#" size="x-small" color="secondary" icon inertia>
            <v-icon icon="fas fa-trash"></v-icon>
          </po-button>
        </div>
      </template>
    </v-data-table-server>
  </po-wrapper>
</template>
