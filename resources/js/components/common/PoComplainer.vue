<script setup lang="ts">
import { ref, watch } from 'vue'
import { router, useHttp } from '@inertiajs/vue3'
import { complainerKey } from '@/composables/keys'
import { injectStrict } from '@/composables/injectStrict'
import { useTypeGuards } from '@/composables/useTypeGuards'
import { useRequestFailure } from '@/composables/useRequestFailure'

const props = defineProps<{
  complainableType: string
  complainableId: number
}>()

const { isEmpty } = useTypeGuards()
const { whenSettled } = useRequestFailure()
const complainer = injectStrict(complainerKey)
const reasonsRequest = useHttp<Record<string, never>, { reasons: string[] }>()
const complaint = useHttp({ reasons: [] as string[], comment: '' })
const reasons = ref<string[]>([])
const hasReasonsLoadError = ref(false)
const hasSubmitError = ref(false)
const hasNoReasonSelected = ref(false)

watch(complainer, async () => {
  if (complainer.value === true) {
    hasReasonsLoadError.value = false

    await whenSettled(
      reasonsRequest.get(route('complaints.reasons'), {
        onSuccess: (data) => {
          reasons.value = data.reasons
        },
        onHttpException: () => {
          hasReasonsLoadError.value = true
        },
        onNetworkError: () => {
          hasReasonsLoadError.value = true
        }
      })
    )
  }
})

function markSubmitFailed(): void {
  hasSubmitError.value = true
}

async function submit(): Promise<void> {
  hasNoReasonSelected.value = isEmpty(complaint.reasons)
  hasSubmitError.value = false

  if (hasNoReasonSelected.value === true) {
    return
  }

  await whenSettled(
    complaint
      .transform((data) => ({
        ...data,
        complainable_type: props.complainableType,
        complainable_id: props.complainableId
      }))
      .post(route('complaints.store'), {
        onHttpException: markSubmitFailed,
        onNetworkError: markSubmitFailed,
        onError: markSubmitFailed,
        onSuccess: () => {
          router.flash({ message: 'complaints.complaint-received', color: 'success' })
          complainer.value = false
          complaint.reset()
        }
      })
  )
}
</script>

<template>
  <v-dialog width="500" persistent>
    <v-card :title="$t('complaints.complaint')">
      <po-modal-close @click.prevent="complainer = false" />
      <v-card-text>
        <p class="text-bold">{{ $t('complaints.report-reason-ask') }}</p>
        <p class="text-disabled">{{ $t('complaints.select-all-apply') }}</p>

        <v-divider class="mt-3" />

        <v-form id="complaint-form" @submit.prevent="submit">
          <p v-if="hasNoReasonSelected" class="text-error mt-3 mb-n2">
            {{ $t('main.select-least-one') }}
          </p>

          <p v-if="hasReasonsLoadError || hasSubmitError" class="text-error mt-3">
            {{ $t('main.error-try-again') }}
          </p>

          <template v-for="reason in reasons" :key="reason">
            <v-switch
              v-model="complaint.reasons"
              class="mb-n5"
              color="primary"
              :label="reason"
              :value="reason"
              multiple
              hide-details
            />
          </template>

          <v-textarea
            v-model="complaint.comment"
            class="mt-5 mb-1"
            :label="$t('main.tell-bit-more-optional')"
            rows="2"
          />
          <po-button color="primary" type="submit" block :disabled="complaint.processing">
            <span v-if="!complaint.processing">{{ $t('main.send') }}</span>
            <v-progress-circular v-else indeterminate />
          </po-button>
        </v-form>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>
