<script setup lang="ts">
import { provide, computed, ref, watch } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import PoWritingDelete from './partials/PoWritingDelete.vue'
import { isDeleteKey } from '@/composables/keys'
import { useFormValidation } from '@/composables/useFormValidation'
import { useRequestFailure } from '@/composables/useRequestFailure'
import type { InertiaPageProps } from '@/types/inertia'

interface CategoryOption {
  id: number
  name: string
}

interface CategoryWithDescendants extends CategoryOption {
  descendants: CategoryOption[]
}

interface WritingFormProps {
  writing: {
    data: {
      title?: string
      text?: string
      slug?: string
      link?: string | null
    }
    main_category: number | null
    categories: number[]
    tags: string[] | null
  }
  main_categories: CategoryWithDescendants[]
  'max-file-size': number
  agreement: boolean
  isUpdate: boolean
}

const page = usePage<InertiaPageProps<WritingFormProps>>()
const { t } = useI18n()
const { isSubmittedFormValid } = useFormValidation()
const { onHttpException, onNetworkError } = useRequestFailure()

function requiredLabel(key: string): string {
  return `${t(key)} *`
}

const writing = page.props.writing
const isUpdate = page.props.isUpdate
const form = useForm({
  title: writing.data.title ?? '',
  main_category: writing.main_category,
  categories: [...writing.categories],
  tags: [...(writing.tags ?? [])],
  text: writing.data.text ?? '',
  link: writing.data.link ?? '',
  cover: null as File | null,
  service_agreement: false,
  privacy_agreement: false
})
const isDelete = ref(false)

provide(isDeleteKey, isDelete)

const altCategories = computed<CategoryOption[]>(
  () =>
    page.props.main_categories.find((category) => category.id === form.main_category)
      ?.descendants ?? []
)
const isAltCategoriesDisabled = computed(() => (form.main_category ?? 0) <= 0)

// Subcategories belong to their main category, so changing it drops them
watch(
  () => form.main_category,
  () => {
    form.categories = []
  }
)

// The server opens the writing once saved, confirming with a flash message. The
// cover upload makes this multipart, which PHP only parses on POST, hence the
// method spoofing for an update.
function submitForm(event: Event): void {
  if (isSubmittedFormValid(event) === false) {
    return
  }

  form
    .transform((data) => (isUpdate ? { ...data, _method: 'put' } : data))
    .post(isUpdate ? route('writings.update', writing.data.slug) : route('writings.store'), {
      forceFormData: true,
      onHttpException,
      onNetworkError
    })
}

function categoryOptionProps(idPrefix: string): (category: CategoryOption) => { id: string } {
  return (category) => ({ id: `${idPrefix}-${category.id}` })
}
</script>

<template>
  <po-wrapper class="w-100">
    <po-head />

    <v-card>
      <v-card-title class="text-uppercase">
        {{ isUpdate ? $t('writings.update-writing') : $t('writings.publish-writing') }}
      </v-card-title>

      <v-form id="writing-form" class="px-5 pb-5" @submit.prevent="submitForm">
        <p class="mb-4 text-disabled" style="margin-top: -0.5rem">
          {{ $t('main.required-fields-marked') }}
        </p>

        <v-text-field
          id="writing-title"
          v-model="form.title"
          :label="requiredLabel('main.title')"
          hide-details="auto"
          :error-messages="form.errors.title"
          :placeholder="$t('main.enter-title')"
          minlength="3"
          maxlength="100"
          persistent-placeholder
          clearable
          required
        />

        <v-select
          id="writing-main-category"
          v-model="form.main_category"
          :label="requiredLabel('categories.main-category')"
          hide-details="auto"
          :error-messages="form.errors.main_category"
          :placeholder="$t('categories.select-main')"
          persistent-placeholder
          :items="page.props.main_categories"
          item-title="name"
          item-value="id"
          :item-props="categoryOptionProps('main-category-option')"
          clearable
          required
          chips
        />

        <v-select
          id="writing-alt-categories"
          v-model="form.categories"
          :label="requiredLabel('categories.alt-categories')"
          hide-details="auto"
          :error-messages="form.errors.categories"
          :placeholder="$t('categories.select-alt')"
          persistent-placeholder
          :items="altCategories"
          item-title="name"
          item-value="id"
          :item-props="categoryOptionProps('alt-category-option')"
          multiple
          clearable
          required
          chips
          :disabled="isAltCategoriesDisabled"
        />

        <v-combobox
          id="writing-tags"
          v-model="form.tags"
          :label="$t('tags.tags')"
          hide-details="auto"
          :error-messages="form.errors.tags"
          :placeholder="$t('tags.enter-tags')"
          pattern="[a-zA-Z0-9,\s\u00c0-\u00d6\u00d8-\u00f6\u00f8-\u02af\u1d00-\u1d25\u1d62-\u1d65\u1d6b-\u1d77\u1d79-\u1d9a\u1e00-\u1eff\u2090-\u2094\u2184-\u2184\u2488-\u2490\u271d-\u271d\u2c60-\u2c7c\u2c7e-\u2c7f\ua722-\ua76f\ua771-\ua787\ua78b-\ua78c\ua7fb-\ua7ff\ufb00-\ufb06]+"
          persistent-placeholder
          :delimiters="[',']"
          multiple
          clearable
          chips
          closable-chips
        />

        <v-textarea
          id="writing-text"
          v-model="form.text"
          :label="requiredLabel('main.text')"
          hide-details="auto"
          :error-messages="form.errors.text"
          :placeholder="$t('main.enter-text')"
          minlength="10"
          maxlength="4000"
          persistent-placeholder
          clearable
          required
        />

        <v-text-field
          id="writing-link"
          v-model="form.link"
          type="url"
          :label="$t('main.link')"
          hide-details="auto"
          :error-messages="form.errors.link"
          :placeholder="$t('main.enter-link')"
          minlength="3"
          maxlength="250"
          persistent-placeholder
          clearable
        />

        <v-file-input
          id="writing-cover"
          v-model="form.cover"
          :label="$t('main.cover')"
          hide-details="auto"
          :error-messages="form.errors.cover"
          prepend-icon=""
          :placeholder="$t('main.select-cover')"
          persistent-placeholder
          :hint="$t('main.max-file-size-is', { size: page.props['max-file-size'] }) + 'kb'"
          persistent-hint
          clearable
        />

        <po-agreement
          v-if="!page.props.agreement"
          v-model:service-agreement="form.service_agreement"
          v-model:privacy-agreement="form.privacy_agreement"
        />

        <po-button
          v-if="isUpdate"
          id="writing-delete"
          color="error"
          variant="tonal"
          class="mb-2"
          block
          @click.prevent="isDelete = true"
        >
          {{ $t('writings.delete-writing-ask') }}
        </po-button>

        <po-writing-delete v-if="isUpdate" v-model="isDelete" :slug="writing.data.slug ?? ''" />

        <po-button
          id="writing-submit"
          type="submit"
          color="primary"
          size="large"
          block
          :disabled="form.processing"
        >
          <template v-if="form.processing"><v-progress-circular indeterminate /></template>
          <template v-else>{{ isUpdate ? $t('main.save') : $t('main.send') }}</template>
        </po-button>
      </v-form>
    </v-card>
  </po-wrapper>
</template>
