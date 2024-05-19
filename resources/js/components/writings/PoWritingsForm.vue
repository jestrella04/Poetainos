<script setup>
import { inject, provide, reactive, computed, ref, watch, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'
import PoWritingDelete from './partials/PoWritingDelete.vue'
import PoWritingKarma from './partials/PoWritingKarma.vue'

const page = computed(() => usePage())
const helper = inject('helper')
const writing = page.value.props.writing
const formData = reactive({
  title: (writing.data.title ??= ''),
  main_category: null, // Properly set onMounted
  alt_categories: [], // Properly set onMounted
  tags: (writing.tags ??= []),
  text: (writing.data.text ??= ''),
  link: '',
  cover: []
})
const errors = ref({})
const mainCategories = ref(page.value.props.main_categories)
const altCategories = ref([])
const isPosting = ref(false)
const isPosted = ref({})
const isUpdate = !helper.strNullOrEmpty(writing.data.title)
const isDelete = ref(false)
const author = reactive({
  karma: page.value.props.author.karma,
  today: page.value.props.author.interactions_today
})
const karma = reactive({
  interactionsLow: page.value.props.karma.req_interactions_low,
  interactionsMid: page.value.props.karma.req_interactions_mid,
  interactionsHigh: page.value.props.karma.req_interactions_high,
  hideBanner: false
})

provide('formData', formData)
provide('isDelete', isDelete)
provide('author', author)
provide('karma', karma)

onMounted(() => {
  // Check is karma banner should be displayed
  if (['F', 'D'].includes(author.karma) && author.today >= karma.interactionsLow) {
    karma.hideBanner = true
  } else if (['C', 'B'].includes(author.karma) && author.today >= karma.interactionsMid) {
    karma.hideBanner = true
  } else if (['A'].includes(author.karma) && author.today >= karma.interactionsHigh) {
    karma.hideBanner = true
  }

  // If updating, trigger category update
  if (!helper.strNullOrEmpty(writing.data.title)) {
    formData.main_category = writing.main_category
    formData.alt_categories = writing.categories
  }

  // Logic needed to assign below values
  if (
    'extra_info' in writing.data &&
    !helper.isNull(writing.data.extra_info) &&
    'link' in writing.data.extra_info
  ) {
    formData.link = writing.data.extra_info.link
  }
})

watch(
  () => formData.main_category,
  (newValue, oldValue) => {
    // Clear selections (but not on first load)
    if (!helper.isNull(oldValue) && newValue > 0) {
      formData.alt_categories = []
    }

    // Set new options
    if (parseInt(formData.main_category) > 0) {
      altCategories.value = JSON.parse(
        JSON.stringify(
          Object.values(mainCategories.value).filter((category) => {
            return category.id === formData.main_category
          })
        )
      )[0].descendants
    } else {
      altCategories.value = []
    }
  }
)

function clearInputs() {
  formData.title = ''
  formData.main_category = null
  formData.alt_categories = []
  formData.tags = []
  formData.text = ''
  formData.link = ''
  formData.cover = []
}

function clearErrors() {
  errors.value = {}
}

async function submitForm() {
  const form = document.querySelector('#writing-form')

  clearErrors()

  if (!helper.checkFormValidity(form)) {
    return
  }

  isPosted.value = {}
  isPosting.value = true

  await axios
    .postForm(form.action, {
      _method: isUpdate ? 'PUT' : 'POST',
      title: formData.title,
      main_category: formData.main_category,
      categories: formData.alt_categories,
      tags: formData.tags,
      text: formData.text,
      link: formData.link,
      cover: formData.cover[0],
      service_agreement: formData.serviceAgreement,
      privacy_agreement: formData.privacyAgreement
    })
    .then((response) => {
      resetForm()
      isPosted.value = response.data
    })
    .catch((error) => {
      errors.value = error.response.data.errors
    })
    .finally(
      setTimeout(() => {
        isPosting.value = false
      }, 1000)
    )
}

function resetForm() {
  clearErrors()

  if (!isUpdate) {
    clearInputs()
  }
}
</script>

<template>
  <po-wrapper class="w-100" style="max-width: 900px">
    <po-head></po-head>

    <v-card
      :title="
        isUpdate ? $t('writings.update-writing') : $t('writings.publish-writing').toUpperCase()
      "
    >
      <template v-if="!isUpdate">
        <po-writing-karma />
      </template>

      <v-card-text v-if="!karma.hideBanner">
        <po-karma-inspire style="margin-top: -0.5rem" />
      </v-card-text>

      <v-form
        id="writing-form"
        v-if="karma.hideBanner"
        :action="isUpdate ? route('writings.update', writing.data.slug) : route('writings.store')"
        class="px-5 pb-5"
        @submit.prevent="submitForm"
        @reset.prevent="resetForm"
      >
        <p class="mb-4 text-caption text-disabled" style="margin-top: -0.5rem">
          {{ $t('main.required-fields-marked') }}
        </p>

        <v-text-field
          v-model="formData.title"
          :label="$t('main.title') + ' *'"
          hide-details="auto"
          :error-messages="errors.title"
          :placeholder="$t('main.enter-title')"
          minlength="3"
          maxlength="100"
          persistent-placeholder
          clearable
          required
        ></v-text-field>

        <v-select
          v-model="formData.main_category"
          :label="$t('categories.main-category') + ' *'"
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
        ></v-select>

        <v-select
          v-model="formData.alt_categories"
          :label="$t('categories.alt-categories') + ' *'"
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
          :disabled="!parseInt(formData.main_category) > 0"
        ></v-select>

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
        ></v-combobox>

        <v-textarea
          v-model="formData.text"
          :label="$t('main.text') + ' *'"
          hide-details="auto"
          :error-messages="errors.text"
          :placeholder="$t('main.enter-text')"
          minlength="10"
          maxlength="4000"
          persistent-placeholder
          clearable
          required
        ></v-textarea>

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
        ></v-text-field>

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
        ></v-file-input>

        <po-agreement v-if="!page.props.agreement"></po-agreement>

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

        <po-writing-delete
          v-if="isUpdate"
          v-model="isDelete"
          :slug="writing.data.slug"
        ></po-writing-delete>

        <po-button type="submit" color="primary" size="large" block :disabled="isPosting">
          <template v-if="isPosting"
            ><v-progress-circular indeterminate></v-progress-circular
          ></template>
          <template v-else>{{ isUpdate ? $t('main.save') : $t('main.send') }}</template>
        </po-button>
      </v-form>

      <v-alert
        v-if="!helper.isEmpty(isPosted)"
        id="writing-alert"
        type="success"
        variant="tonal"
        class="mb-5 mx-auto"
        style="width: 85%; max-width: 600px"
      >
        {{ isUpdate ? $t('writings.writing-updated') : $t('writings.writing-published') }}
        {{ $t('main.take-a-look') }}
        <po-link :href="isPosted.url" inertia>{{ $t('main.here') }}.</po-link>
      </v-alert>
    </v-card>
  </po-wrapper>
</template>
