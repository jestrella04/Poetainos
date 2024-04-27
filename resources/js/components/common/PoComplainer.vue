<script setup>
import { ref, inject, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  compType: { type: String, required: true },
  compId: { type: Number, required: true }
})

const helper = inject('helper')
const complainer = inject('complainer')
const reasons = ref([])
const compReasons = ref([])
const compMessage = ref('')
const isPosting = ref(false)
const errors = ref(false)
const forceSnackBar = inject('forceSnackBar')

watch(complainer, async () => {
  if (complainer.value) {
    await axios
      .get(window.route('complaints.reasons'))
      .then((response) => {
        reasons.value = response.data.reasons
      })
      .catch()
      .finally()
  }
})

async function submit() {
  const form = document.querySelector('#complaint-form')

  if (helper.isEmpty(compReasons.value)) {
    errors.value = true
    return
  }

  isPosting.value = true
  errors.value = false

  await axios
    .post(form.action, {
      complainable_type: props.compType,
      complainable_id: props.compId,
      reasons: compReasons.value,
      message: compMessage.value
    })
    .then(() => {
      helper.setSnackBar({
        message: 'complaints.complaint-received',
        color: 'success',
        active: true
      })

      forceSnackBar.value = true

      complainer.value = false
      compReasons.value = []
      compMessage.value = ''
    })
    .catch(() => {
      errors.value = true
    })
    .finally(() => {
      isPosting.value = false
    })
}
</script>

<template>
  <v-dialog width="500" persistent>
    <v-card :title="$t('complaints.complaint')">
      <po-modal-close @click.prevent="complainer = false"></po-modal-close>
      <v-card-text>
        <p class="text-bold">{{ $t('complaints.report-reason-ask') }}</p>
        <p class="text-disabled">{{ $t('complaints.select-all-apply') }}</p>

        <v-divider class="mt-3"></v-divider>

        <v-form id="complaint-form" :action="route('complaints.store')" @submit.prevent="submit">
          <p v-if="errors" class="text-caption text-error mt-3" style="margin-bottom: -10px;">{{
            $t('main.select-least-one') }}</p>

          <template v-for="reason in reasons" :key="reason">
            <v-switch v-model="compReasons" style="margin-bottom: -20px;" color="primary" :label="reason"
              :value="reason" multiple hide-details></v-switch>
          </template>

          <v-textarea v-model="compMessage" class="mt-5 mb-1" :label="$t('main.tell-bit-more-optional')"
            rows="2"></v-textarea>
          <po-button color="primary" type="submit" block>
            <span v-if="!isPosting">{{ $t('main.send') }}</span>
            <v-progress-circular v-else indeterminate></v-progress-circular>
          </po-button>
        </v-form>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>