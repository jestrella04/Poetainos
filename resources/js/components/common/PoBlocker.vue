<script setup>
import { ref, inject } from 'vue'
import axios from 'axios'

const props = defineProps({
  user: { type: Object, required: true },
})

const helper = inject('helper')
const blocker = inject('blocker')
const isPosting = ref(false)
const errors = ref(false)
const forceSnackBar = inject('forceSnackBar')

async function submit() {
  const form = document.querySelector('#blocking-form')

  isPosting.value = true
  errors.value = false

  await axios
    .post(form.action, {
      user: props.user.username,
    })
    .then(() => {
      helper.setSnackBar({
        message: 'users.user-blocked',
        color: 'success',
        active: true
      })

      forceSnackBar.value = true
      blocker.value = false
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
    <v-card :title="$t('main.block-user')">
      <po-modal-close @click.prevent="blocker = false"></po-modal-close>

      <v-card-text>
        <p>
          {{ $t('accounts.block-user-warning') }}
          {{ $t('users.block-user-ask', { name: $helper.userDisplayName(user) }) }}
        </p>

        <v-divider class="mt-3"></v-divider>

        <v-form id="blocking-form" :action="route('users.block', user.username)" @submit.prevent="submit">
          <po-button color="primary" type="submit" block>
            <span v-if="!isPosting">{{ $t('main.block') }}</span>
            <v-progress-circular v-else indeterminate></v-progress-circular>
          </po-button>
        </v-form>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>