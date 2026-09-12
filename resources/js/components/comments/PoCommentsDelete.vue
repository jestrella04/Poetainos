<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { forceSnackBarKey, isDeleteKey, writingKey } from '@/composables/keys'
import { injectStrict } from '@/composables/injectStrict'
import { useSnackbar } from '@/composables/useSnackbar'
import { useFormSubmit } from '@/composables/useFormSubmit'
import type { Comment } from '@/types/models'

defineProps<{
  comment: Comment
}>()

const { setSnackBar } = useSnackbar()
const isDelete = injectStrict(isDeleteKey)
const forceSnackBar = injectStrict(forceSnackBarKey)
const writing = injectStrict(writingKey)
const { isPosting, submitForm } = useFormSubmit(false)

async function submit(): Promise<void> {
  await submitForm({
    formSelector: '#comment-delete-form',
    payload: { _method: 'DELETE' },
    onSuccess: () => {
      router.visit(route('writings.show', writing.slug))
      setSnackBar({
        message: 'comments.comment-deleted',
        color: 'success',
        active: true
      })

      forceSnackBar.value = true
      isDelete.value = false
    },
    onError: () => true
  })
}
</script>

<template>
  <v-dialog width="500" persistent>
    <v-card :title="$t('main.proceed-with-caution')">
      <po-modal-close @click.prevent="isDelete = false"></po-modal-close>
      <v-card-text>
        <p class="mb-2">
          {{ $t('main.permanent-delete-ask') }}
          {{ $t('main.action-irreversible') }}
        </p>

        <v-divider class="mt-3"></v-divider>

        <v-form
          id="comment-delete-form"
          :action="route('comments.destroy', comment.id)"
          @submit.prevent="submit"
        >
          <po-button color="primary" type="submit" block>
            <span v-if="!isPosting">{{ $t('main.delete') }}</span>
            <v-progress-circular v-else indeterminate></v-progress-circular>
          </po-button>
        </v-form>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>
