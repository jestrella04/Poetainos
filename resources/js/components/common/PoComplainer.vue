<script setup lang="ts">
import { ref, watch } from 'vue'
import axios from 'axios'
import { complainerKey, forceSnackBarKey } from '@/composables/keys'
import { injectStrict } from '@/composables/injectStrict'
import { useTypeGuards } from '@/composables/useTypeGuards'
import { useSnackbar } from '@/composables/useSnackbar'
import { useFormSubmit } from '@/composables/useFormSubmit'

const props = defineProps<{
  compType: string
  compId: number
}>()

const { isEmpty } = useTypeGuards()
const { setSnackBar } = useSnackbar()
const complainer = injectStrict(complainerKey)
const reasons = ref<string[]>([])
const compReasons = ref<string[]>([])
const compMessage = ref('')
const hasReasonsLoadError = ref(false)
const forceSnackBar = injectStrict(forceSnackBarKey)
const { isPosting, errors, submitForm } = useFormSubmit(false)

watch(complainer, async () => {
  if (complainer.value === true) {
    hasReasonsLoadError.value = false

    await axios
      .get<{ reasons: string[] }>(route('complaints.reasons'))
      .then((response) => {
        reasons.value = response.data.reasons
      })
      .catch(() => {
        hasReasonsLoadError.value = true
      })
  }
})

async function submit(): Promise<void> {
  if (isEmpty(compReasons.value)) {
    errors.value = true
    return
  }

  await submitForm({
    formSelector: '#complaint-form',
    payload: {
      complainable_type: props.compType,
      complainable_id: props.compId,
      reasons: compReasons.value,
      message: compMessage.value
    },
    onSuccess: () => {
      setSnackBar({
        message: 'complaints.complaint-received',
        color: 'success',
        active: true
      })

      forceSnackBar.value = true

      complainer.value = false
      compReasons.value = []
      compMessage.value = ''
    },
    onError: () => true
  })
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

        <v-form id="complaint-form" :action="route('complaints.store')" @submit.prevent="submit">
          <p v-if="errors" class="text-caption text-error mt-3" style="margin-bottom: -10px">
            {{ $t('main.select-least-one') }}
          </p>

          <p v-if="hasReasonsLoadError" class="text-caption text-error mt-3">
            {{ $t('main.error-try-again') }}
          </p>

          <template v-for="reason in reasons" :key="reason">
            <v-switch
              v-model="compReasons"
              style="margin-bottom: -20px"
              color="primary"
              :label="reason"
              :value="reason"
              multiple
              hide-details
            />
          </template>

          <v-textarea
            v-model="compMessage"
            class="mt-5 mb-1"
            :label="$t('main.tell-bit-more-optional')"
            rows="2"
          />
          <po-button color="primary" type="submit" block>
            <span v-if="!isPosting">{{ $t('main.send') }}</span>
            <v-progress-circular v-else indeterminate />
          </po-button>
        </v-form>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>
