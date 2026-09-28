<script setup lang="ts">
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useFormValidation } from '@/composables/useFormValidation'
import { useRequestFailure } from '@/composables/useRequestFailure'

interface CategoryOption {
  id: number
  name: string
}

interface EditableCategory extends CategoryOption {
  slug: string
  parent_id: number | null
  description: string | null
}

// Without a category, the dialog creates a new one
const props = defineProps<{
  category: EditableCategory | null
  parentOptions: CategoryOption[]
}>()

const emit = defineEmits<{
  saved: []
}>()

const isOpen = defineModel<boolean>({ required: true })
const { isSubmittedFormValid } = useFormValidation()
const { onHttpException, onNetworkError } = useRequestFailure()
const isUpdate = props.category !== null
const form = useForm({
  name: props.category?.name ?? '',
  parent: props.category?.parent_id ?? null,
  description: props.category?.description ?? ''
})
// A category can't be moved under itself; the server also refuses its descendants
const availableParents = computed(() =>
  props.parentOptions.filter((option) => option.id !== props.category?.id)
)

// The server returns to the table, confirming with a flash message
function submit(event: Event): void {
  if (isSubmittedFormValid(event) === false) {
    return
  }

  const options = {
    preserveState: true,
    preserveScroll: true,
    onHttpException,
    onNetworkError,
    onSuccess: () => {
      isOpen.value = false
      emit('saved')
    }
  }

  if (props.category !== null) {
    form.put(route('admin.categories.update', props.category.slug), options)
  } else {
    form.post(route('admin.categories.store'), options)
  }
}
</script>

<template>
  <v-dialog v-model="isOpen" width="600" persistent>
    <v-card :title="isUpdate ? $t('categories.edit-category') : $t('categories.create-category')">
      <po-modal-close @click.prevent="isOpen = false" />

      <v-card-text>
        <v-form id="admin-category-form" @submit.prevent="submit">
          <v-text-field
            id="admin-category-name"
            v-model="form.name"
            :label="$t('main.name')"
            :error-messages="form.errors.name"
            minlength="3"
            maxlength="40"
            required
            hide-details="auto"
          />

          <v-select
            id="admin-category-parent"
            v-model="form.parent"
            :items="availableParents"
            item-title="name"
            item-value="id"
            :label="$t('main.parent')"
            :error-messages="form.errors.parent"
            clearable
            hide-details="auto"
          />

          <v-textarea
            id="admin-category-description"
            v-model="form.description"
            :label="$t('main.description')"
            :error-messages="form.errors.description"
            minlength="3"
            maxlength="255"
            rows="3"
            required
            hide-details="auto"
          />

          <po-button
            id="admin-category-submit"
            color="primary"
            type="submit"
            block
            :disabled="form.processing"
          >
            <span v-if="!form.processing">{{ $t('main.save') }}</span>
            <v-progress-circular v-else indeterminate />
          </po-button>
        </v-form>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>
