<script setup lang="ts">
import { computed, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import PoLayoutAdmin from '../layouts/PoLayoutAdmin.vue'
import type { InertiaPageProps } from '@/types/inertia'

defineOptions({
  layout: PoLayoutAdmin
})

const page = computed(() =>
  usePage<InertiaPageProps<{ info: Record<string, string>; log: string }>>()
)
const tab = ref<string | null>(null)
</script>

<template>
  <po-wrapper class="h-100">
    <v-card-title>{{ $t('admin.tools') }}</v-card-title>

    <v-tabs v-model="tab" class="mb-5">
      <v-tab value="info">{{ $t('admin.php-info') }}</v-tab>
      <v-tab value="log">{{ $t('admin.log-viewer') }}</v-tab>
    </v-tabs>

    <v-window v-model="tab" class="h-100">
      <v-window-item value="info" class="h-100">
        <v-table>
          <tbody>
            <tr v-for="(value, key) in page.props.info" :key="key">
              <td class="font-weight-bold">{{ key }}</td>
              <td>{{ value }}</td>
            </tr>
          </tbody>
        </v-table>
      </v-window-item>

      <v-window-item value="log" class="h-100">
        <po-button
          color="secondary"
          size="x-small"
          :href="route('admin.log')"
          icon
          :title="$t('admin.download-full-copy')"
          class="mb-2"
        >
          <v-icon icon="fas fa-download" />
        </po-button>
        <v-textarea v-model="page.props.log" :label="$t('admin.log')" rows="20" readonly />
      </v-window-item>
    </v-window>
  </po-wrapper>
</template>
