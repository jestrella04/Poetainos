<script setup lang="ts">
import { computed, ref } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import PoLayoutAdmin from '../layouts/PoLayoutAdmin.vue'
import PoAdminTitle from './partials/PoAdminTitle.vue'
import { useLogEntries } from '@/composables/useLogEntries'
import { useDates } from '@/composables/useDates'
import { useFormatting } from '@/composables/useFormatting'
import { useRequestFailure } from '@/composables/useRequestFailure'
import type { InertiaPageProps } from '@/types/inertia'

defineOptions({
  layout: PoLayoutAdmin
})

interface LogFile {
  name: string
  size: number
  modified_at: string
  // False for the security audit logs, which the admin panel can't erase
  clearable: boolean
}

const DEFAULT_FILE = 'laravel.log'
const ALL_LEVELS = 'all'
const LEVEL_FILTERS = ['error', 'warning', 'info', 'debug']
const LEVEL_COLORS: Record<string, string> = {
  emergency: 'error',
  alert: 'error',
  critical: 'error',
  error: 'error',
  warning: 'warning',
  notice: 'info',
  info: 'info'
}

const { t } = useI18n()
const { relativeDate, toLocaleDateTime } = useDates()
const { fileSize } = useFormatting()
const page = usePage<InertiaPageProps<{ files: LogFile[] }>>()
const files = computed(() => page.props.files)
const fileOptions = computed(() =>
  files.value.map((file) => ({ title: `${file.name} (${fileSize(file.size)})`, value: file.name }))
)
const levelOptions = [
  { title: t('main.all'), value: ALL_LEVELS },
  ...LEVEL_FILTERS.map((level) => ({ title: level.toUpperCase(), value: level }))
]
const initialFile =
  files.value.find((file) => file.name === DEFAULT_FILE)?.name ?? files.value[0]?.name ?? ''
const { file, level, search, entries, isLoading, hasOlder, reload, loadOlder } =
  useLogEntries(initialFile)
// Vuetify chip groups can't select a null value, so "all levels" has a value of its own
const levelFilter = computed({
  get: () => level.value ?? ALL_LEVELS,
  set: (value: string) => {
    level.value = value === ALL_LEVELS ? null : value
  }
})
const hasFiles = computed(() => files.value.length > 0)
const isFileClearable = computed(
  () => files.value.find((listedFile) => listedFile.name === file.value)?.clearable === true
)
const isEmpty = computed(() => entries.value.length === 0 && isLoading.value === false)

const isConfirmingClear = ref(false)
const clearForm = useForm({})
const { onHttpException, onNetworkError } = useRequestFailure()

function levelColor(entryLevel: string | null): string | undefined {
  return entryLevel === null ? undefined : LEVEL_COLORS[entryLevel]
}

// The server empties the file and reloads the page's file list with a flash message
function clearLog(): void {
  clearForm.delete(route('admin.logs.clear', file.value), {
    preserveScroll: true,
    preserveState: true,
    onHttpException,
    onNetworkError,
    onSuccess: () => {
      isConfirmingClear.value = false
      void reload()
    }
  })
}
</script>

<template>
  <po-wrapper>
    <po-admin-title :title="$t('admin.logs')" />

    <v-card-text v-if="hasFiles === false">{{ $t('admin.no-log-files') }}</v-card-text>

    <template v-else>
      <v-row density="compact" align="center" class="mb-2">
        <v-col cols="12" md="4">
          <v-select
            v-model="file"
            :items="fileOptions"
            :label="$t('admin.log-file')"
            prepend-inner-icon="fas fa-file-lines"
            hide-details
          />
        </v-col>
        <v-col cols="12" md="5">
          <v-text-field
            :model-value="search"
            :label="$t('admin.search-log')"
            prepend-inner-icon="fas fa-magnifying-glass"
            clearable
            hide-details
            @update:model-value="search = $event ?? ''"
          />
        </v-col>
        <v-col cols="12" md="3" class="d-flex justify-end ga-2">
          <po-button
            color="secondary"
            size="small"
            icon
            :title="$t('main.reload')"
            :disabled="isLoading"
            @click="reload"
          >
            <v-icon icon="fas fa-rotate-right" />
          </po-button>
          <po-button
            color="secondary"
            size="small"
            icon
            :href="route('admin.logs.download', file)"
            :title="$t('admin.download-full-copy')"
          >
            <v-icon icon="fas fa-download" />
          </po-button>
          <po-button
            v-if="isFileClearable"
            color="error"
            size="small"
            icon
            :title="$t('admin.clear-log')"
            @click="isConfirmingClear = true"
          >
            <v-icon icon="fas fa-trash" />
          </po-button>
        </v-col>
      </v-row>

      <v-chip-group v-model="levelFilter" mandatory selected-class="text-primary" class="mb-4">
        <v-chip
          v-for="option in levelOptions"
          :key="option.title"
          :value="option.value"
          :text="option.title"
          size="small"
          filter
        />
      </v-chip-group>

      <v-skeleton-loader v-if="isLoading && entries.length === 0" type="list-item-two-line@5" />

      <v-alert v-else-if="isEmpty" type="info" variant="tonal" :text="$t('admin.no-log-entries')" />

      <v-expansion-panels v-else variant="accordion" multiple>
        <v-expansion-panel v-for="(entry, index) in entries" :key="`${file}-${index}`">
          <v-expansion-panel-title>
            <div class="d-flex align-center ga-3 w-100 overflow-hidden">
              <v-chip
                v-if="entry.level !== null"
                :color="levelColor(entry.level)"
                size="x-small"
                label
                class="flex-shrink-0"
              >
                {{ entry.level.toUpperCase() }}
              </v-chip>
              <span
                v-if="entry.date !== null"
                class="text-caption text-medium-emphasis flex-shrink-0"
                :title="toLocaleDateTime(entry.date)"
              >
                {{ relativeDate(entry.date) }}
              </span>
              <span class="text-body-2 text-truncate">{{ entry.message }}</span>
            </div>
          </v-expansion-panel-title>
          <v-expansion-panel-text>
            <div v-if="entry.date !== null" class="text-caption text-medium-emphasis mb-2">
              {{ toLocaleDateTime(entry.date) }} · {{ entry.environment }}
            </div>
            <pre class="text-caption overflow-x-auto bg-surface-variant rounded pa-3">{{
              entry.details === '' ? entry.message : `${entry.message}\n${entry.details}`
            }}</pre>
          </v-expansion-panel-text>
        </v-expansion-panel>
      </v-expansion-panels>

      <div v-if="hasOlder" class="d-flex justify-center mt-4">
        <po-button color="secondary" :loading="isLoading" @click="loadOlder">
          {{ $t('admin.load-older') }}
        </po-button>
      </div>
    </template>

    <v-dialog v-model="isConfirmingClear" width="500">
      <v-card :title="$t('main.proceed-with-caution')">
        <po-modal-close @click.prevent="isConfirmingClear = false" />
        <v-card-text>
          <p class="mb-2">
            {{ $t('admin.clear-log-confirm', { file }) }}
            {{ $t('main.action-irreversible') }}
          </p>

          <v-divider class="mt-3" />

          <po-button color="error" block :disabled="clearForm.processing" @click="clearLog">
            <span v-if="!clearForm.processing">{{ $t('admin.clear-log') }}</span>
            <v-progress-circular v-else indeterminate />
          </po-button>
        </v-card-text>
      </v-card>
    </v-dialog>
  </po-wrapper>
</template>
