<script setup lang="ts">
import { provide, reactive, computed, ref, watch, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import PoWritingDelete from './partials/PoWritingDelete.vue'
import { formDataKey, isDeleteKey } from '@/composables/keys'
import { useTypeGuards } from '@/composables/useTypeGuards'
import { useFormErrors } from '@/composables/useFormErrors'
import { useFormSubmit } from '@/composables/useFormSubmit'
import type { InertiaPageProps } from '@/types/inertia'
import type { LaravelValidationErrors } from '@/types/http'

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
      extra_info?: { link?: string; cover?: string } | null
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

interface PostedResult {
  url: string
}

const page = usePage<InertiaPageProps<WritingFormProps>>()
const { t } = useI18n()
const { isEmpty } = useTypeGuards()
const { validationErrors } = useFormErrors()
const { isPosting, errors, submitForm: postForm } = useFormSubmit<LaravelValidationErrors>({})

function requiredLabel(key: string): string {
  return `${t(key)} *`
}
const writing = page.props.writing
const formData = reactive({
  title: writing.data.title ?? '',
  main_category: null as number | null, // Properly set onMounted
  alt_categories: [] as number[], // Properly set onMounted
  tags: [...(writing.tags ?? [])],
  text: writing.data.text ?? '',
  link: '',
  cover: [] as File[],
  serviceAgreement: false,
  privacyAgreement: false
})
const mainCategories = ref(page.props.main_categories)
const altCategories = ref<CategoryOption[]>([])
const isPosted = ref<Partial<PostedResult>>({})
const isUpdate = ref(page.props.isUpdate)
const isDelete = ref(false)

provide(formDataKey, formData)
provide(isDeleteKey, isDelete)

onMounted(() => {
  // If updating, trigger category update
  if (isUpdate.value === true) {
    formData.main_category = writing.main_category
    formData.alt_categories = writing.categories
  }

  if (writing.data.extra_info?.link !== undefined) {
    formData.link = writing.data.extra_info.link
  }
})

watch(
  () => formData.main_category,
  (newValue, oldValue) => {
    // Clear selections (but not on first load)
    if (oldValue !== null && (newValue ?? 0) > 0) {
      formData.alt_categories = []
    }

    // Set new options
    const selected = mainCategories.value.find((category) => category.id === formData.main_category)
    altCategories.value = selected !== undefined ? selected.descendants : []
  }
)

const isAltCategoriesDisabled = computed(() => (formData.main_category ?? 0) <= 0)

function clearInputs() {
  formData.title = ''
  formData.main_category = null
  formData.alt_categories = []
  formData.tags = []
  formData.text = ''
  formData.link = ''
  formData.cover = []
}

async function submitForm() {
  isPosted.value = {}

  await postForm<PostedResult>({
    formSelector: '#writing-form',
    multipart: true,
    cooldown: true,
    payload: {
      _method: isUpdate.value ? 'PUT' : 'POST',
      title: formData.title,
      main_category: formData.main_category,
      categories: formData.alt_categories,
      tags: formData.tags,
      text: formData.text,
      link: formData.link,
      cover: formData.cover,
      service_agreement: formData.serviceAgreement,
      privacy_agreement: formData.privacyAgreement
    },
    onSuccess: (data) => {
      resetForm()
      isPosted.value = data
    },
    onError: validationErrors
  })
}

function resetForm() {
  errors.value = {}

  if (isUpdate.value === false) {
    clearInputs()
  }
}
</script>

<template>
  <po-wrapper class="w-100">
    <po-head />

    <v-card
      :title="
        isUpdate ? $t('writings.update-writing') : $t('writings.publish-writing').toUpperCase()
      "
    >
      <v-form
        id="writing-form"
        :action="isUpdate ? route('writings.update', writing.data.slug) : route('writings.store')"
        class="px-5 pb-5"
        @submit.prevent="submitForm"
        @reset.prevent="resetForm"
      >
        <p class="mb-4 text-disabled" style="margin-top: -0.5rem">
          {{ $t('main.required-fields-marked') }}
        </p>

        <v-text-field
          v-model="formData.title"
          :label="requiredLabel('main.title')"
          hide-details="auto"
          :error-messages="errors.title"
          :placeholder="$t('main.enter-title')"
          minlength="3"
          maxlength="100"
          persistent-placeholder
          clearable
          required
        />

        <v-select
          v-model="formData.main_category"
          :label="requiredLabel('categories.main-category')"
          hide-details="auto"
          :error-messages="errors.main_category"
          :placeholder="$t('categories.select-main')"
          persistent-placeholder
          :items="mainCategories"
          item-title="name"
          item-value="id"
          clearable
          required
          chips
        />

        <v-select
          v-model="formData.alt_categories"
          :label="requiredLabel('categories.alt-categories')"
          hide-details="auto"
          :error-messages="errors.categories"
          :placeholder="$t('categories.select-alt')"
          persistent-placeholder
          :items="altCategories"
          item-title="name"
          item-value="id"
          multiple
          clearable
          required
          chips
          :disabled="isAltCategoriesDisabled"
        />

        <v-combobox
          v-model="formData.tags"
          :label="$t('tags.tags')"
          hide-details="auto"
          :error-messages="errors.tags"
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
          v-model="formData.text"
          :label="requiredLabel('main.text')"
          hide-details="auto"
          :error-messages="errors.text"
          :placeholder="$t('main.enter-text')"
          minlength="10"
          maxlength="4000"
          persistent-placeholder
          clearable
          required
        />

        <v-text-field
          v-model="formData.link"
          type="url"
          :label="$t('main.link')"
          hide-details="auto"
          :error-messages="errors.link"
          :placeholder="$t('main.enter-link')"
          minlength="3"
          maxlength="250"
          persistent-placeholder
          clearable
        />

        <v-file-input
          v-model="formData.cover"
          :label="$t('main.cover')"
          hide-details="auto"
          :error-messages="errors.cover"
          prepend-icon=""
          :placeholder="$t('main.select-cover')"
          persistent-placeholder
          :hint="$t('main.max-file-size-is', { size: page.props['max-file-size'] }) + 'kb'"
          persistent-hint
          clearable
        />

        <po-agreement v-if="!page.props.agreement" />

        <po-button
          v-if="isUpdate"
          color="error"
          variant="tonal"
          class="mb-2"
          block
          @click.prevent="isDelete = true"
        >
          {{ $t('writings.delete-writing-ask') }}
        </po-button>

        <po-writing-delete v-if="isUpdate" v-model="isDelete" :slug="writing.data.slug ?? ''" />

        <po-button type="submit" color="primary" size="large" block :disabled="isPosting">
          <template v-if="isPosting"><v-progress-circular indeterminate /></template>
          <template v-else>{{ isUpdate ? $t('main.save') : $t('main.send') }}</template>
        </po-button>
      </v-form>

      <v-alert
        v-if="!isEmpty(isPosted)"
        id="writing-alert"
        type="success"
        variant="tonal"
        class="mb-5 mx-auto"
        width="85%"
        max-width="600"
      >
        {{ isUpdate ? $t('writings.writing-updated') : $t('writings.writing-published') }}
        {{ $t('main.take-a-look') }}
        <po-link :href="isPosted.url" inertia>{{ $t('main.here') }}.</po-link>
      </v-alert>
    </v-card>
  </po-wrapper>
</template>
