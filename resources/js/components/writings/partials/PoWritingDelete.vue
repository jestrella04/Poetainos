<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { isDeleteKey } from '@/composables/keys'
import { injectStrict } from '@/composables/injectStrict'
import { useRequestFailure } from '@/composables/useRequestFailure'

const props = defineProps<{
  slug: string
}>()

const isDelete = injectStrict(isDeleteKey)
const { onHttpException, onNetworkError } = useRequestFailure()
const form = useForm({})

// The server takes the user home and confirms with a flash message
function submit(): void {
  form.delete(route('writings.destroy', props.slug), {
    onHttpException,
    onNetworkError,
    onSuccess: () => {
      isDelete.value = false
    }
  })
}
</script>

<template>
  <v-dialog width="500" persistent>
    <v-card :title="$t('main.proceed-with-caution')">
      <po-modal-close @click.prevent="isDelete = false" />

      <v-card-text>
        <p class="mb-2">
          {{ $t('main.permanent-delete-ask') }}
          {{ $t('main.action-irreversible') }}
        </p>

        <v-alert color="warning" variant="tonal">
          <p>{{ $t('writings.delete-writing-warning') }}</p>
        </v-alert>

        <v-divider class="mt-3" />

        <v-form id="writing-delete-form" @submit.prevent="submit">
          <po-button
            id="writing-delete-submit"
            color="primary"
            type="submit"
            block
            :disabled="form.processing"
          >
            <span v-if="!form.processing">{{ $t('main.delete') }}</span>
            <v-progress-circular v-else indeterminate />
          </po-button>
        </v-form>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>
