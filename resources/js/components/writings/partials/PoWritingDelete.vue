<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { forceSnackBarKey, isDeleteKey } from '@/composables/keys'
import { injectStrict } from '@/composables/injectStrict'
import { useSnackbar } from '@/composables/useSnackbar'
import { useFormSubmit } from '@/composables/useFormSubmit'

defineProps<{
  slug: string
}>()

const { setSnackBar } = useSnackbar()
const isDelete = injectStrict(isDeleteKey)
const forceSnackBar = injectStrict(forceSnackBarKey)
const { isPosting, submitForm } = useFormSubmit(false)

async function submit(): Promise<void> {
  await submitForm({
    formSelector: '#writing-delete-form',
    payload: { _method: 'DELETE' },
    onSuccess: () => {
      router.visit(route('home'))
      setSnackBar({
        message: 'writings.writing-deleted',
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

        <v-form
          id="writing-delete-form"
          :action="route('writings.destroy', slug)"
          @submit.prevent="submit"
        >
          <po-button color="primary" type="submit" block>
            <span v-if="!isPosting">{{ $t('main.delete') }}</span>
            <v-progress-circular v-else indeterminate />
          </po-button>
        </v-form>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>
