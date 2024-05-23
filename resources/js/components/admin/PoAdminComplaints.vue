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
  { title: 'Type', align: 'start', sortable: false, key: 'type' },
  { title: 'Created at', align: 'start', sortable: false, key: 'created_at' },
  { title: 'Closed at', align: 'start', sortable: false, key: 'closed_at' },
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
    .get(route('admin.complaints', { page: event.page }))
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
        {{ $helper.toLocaleDate(item.created_at) }}
      </template>

      <template v-slot:item.closed_at="{ item }">
        {{ $helper.toLocaleDate(item.closed_at) }}
      </template>

      <template v-slot:item.actions="{ item }">
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
