<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { useDates } from '@/composables/useDates'
import { useRequestFailure } from '@/composables/useRequestFailure'

interface ReviewedComplaint {
  id: number
  reasons: string[]
  comment: string | null
  reported_url: string | null
  created_at: string
  closed_at: string | null
  closed_comment: string | null
}

const props = defineProps<{
  complaint: ReviewedComplaint
}>()

const emit = defineEmits<{
  closed: []
}>()

const isOpen = defineModel<boolean>({ required: true })
const { toLocaleDate } = useDates()
const { onHttpException, onNetworkError } = useRequestFailure()
const form = useForm({ closed_comment: '' })

// The server returns to the table, confirming with a flash message
function closeComplaint(): void {
  form.put(route('admin.complaints.close', props.complaint.id), {
    preserveState: true,
    preserveScroll: true,
    onHttpException,
    onNetworkError,
    onSuccess: () => {
      isOpen.value = false
      emit('closed')
    }
  })
}
</script>

<template>
  <v-dialog v-model="isOpen" width="600">
    <v-card :title="$t('complaints.complaint-details')">
      <po-modal-close @click.prevent="isOpen = false" />

      <v-card-text>
        <v-list density="compact">
          <v-list-item
            :title="$t('main.created-at')"
            :subtitle="toLocaleDate(complaint.created_at)"
          />

          <v-list-item :title="$t('complaints.reasons')">
            <div class="d-flex flex-wrap ga-1 mt-1">
              <v-chip v-for="reason in complaint.reasons" :key="reason" size="small">
                {{ reason }}
              </v-chip>
            </div>
          </v-list-item>

          <v-list-item
            v-if="complaint.comment !== null"
            :title="$t('complaints.reporter-comment')"
            :subtitle="complaint.comment"
          />

          <v-list-item
            v-if="complaint.closed_at !== null"
            :title="$t('admin.closed-at')"
            :subtitle="toLocaleDate(complaint.closed_at)"
          />

          <v-list-item
            v-if="complaint.closed_comment !== null"
            :title="$t('complaints.closing-note')"
            :subtitle="complaint.closed_comment"
          />
        </v-list>

        <po-button
          v-if="complaint.reported_url !== null"
          :href="complaint.reported_url"
          color="secondary"
          variant="tonal"
          prepend-icon="fas fa-eye"
          block
          inertia
        >
          {{ $t('complaints.view-reported-content') }}
        </po-button>

        <v-alert v-else type="info" variant="tonal">
          {{ $t('complaints.reported-content-gone') }}
        </v-alert>

        <v-form
          v-if="complaint.closed_at === null"
          id="admin-complaint-close-form"
          class="mt-3"
          @submit.prevent="closeComplaint"
        >
          <v-textarea
            id="admin-complaint-closing-note"
            v-model="form.closed_comment"
            :label="$t('complaints.closing-note')"
            :error-messages="form.errors.closed_comment"
            maxlength="255"
            rows="2"
            hide-details="auto"
          />

          <po-button
            id="admin-complaint-close-submit"
            color="primary"
            type="submit"
            block
            :disabled="form.processing"
          >
            <span v-if="!form.processing">{{ $t('complaints.close-complaint') }}</span>
            <v-progress-circular v-else indeterminate />
          </po-button>
        </v-form>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>
