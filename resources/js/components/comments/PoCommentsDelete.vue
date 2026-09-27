<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { isDeleteKey } from '@/composables/keys'
import { injectStrict } from '@/composables/injectStrict'
import { useRequestFailure } from '@/composables/useRequestFailure'
import type { Comment } from '@/types/models'

const props = defineProps<{
  comment: Comment
}>()

const isDelete = injectStrict(isDeleteKey)
const { onHttpException, onNetworkError } = useRequestFailure()
const form = useForm({})

// The server reloads the writing without the comment and confirms with a flash message
function submit(): void {
  form.delete(route('comments.destroy', props.comment.id), {
    preserveScroll: true,
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

        <v-divider class="mt-3" />

        <v-form id="comment-delete-form" @submit.prevent="submit">
          <po-button color="primary" type="submit" block :disabled="form.processing">
            <span v-if="!form.processing">{{ $t('main.delete') }}</span>
            <v-progress-circular v-else indeterminate />
          </po-button>
        </v-form>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>
