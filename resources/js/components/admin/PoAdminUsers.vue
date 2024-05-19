<script setup>
import { computed, ref, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import PoLayoutAdmin from '../layouts/PoLayoutAdmin.vue'
import axios from 'axios'

defineOptions({
  layout: PoLayoutAdmin
})

const page = computed(() => usePage())
const headers = [
  { title: 'Id', align: 'start', sortable: false, key: 'id' },
  { title: 'Username', align: 'start', sortable: false, key: 'username' },
  { title: 'Full Name', align: 'start', sortable: false, key: 'name' },
  { title: 'Email', align: 'start', sortable: false, key: 'email' },
  { title: 'Created at', align: 'start', sortable: false, key: 'created_at' },
  { title: 'Aura', align: 'start', sortable: false, key: 'aura' },
  { title: 'Karma', align: 'start', sortable: false, key: 'karma' },
  { title: 'Actions', align: 'start', sortable: false, key: 'actions' }
]
const items = ref([])
const totalItems = ref(page.value.props.total)
const isLoading = ref(true)

onMounted(() => {
  loadItems({ page: 1 })
})

async function loadItems(event) {
  await axios
    .get(window.route('admin.users', { page: event.page }))
    .then((response) => {
      items.value = response.data.data
      isLoading.value = false
    })
    .catch()
    .finally()
}
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
