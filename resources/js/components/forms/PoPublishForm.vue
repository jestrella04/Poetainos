<script setup>
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { watch } from 'vue';
import { inject, provide, reactive, computed, ref } from 'vue'

const page = computed(() => usePage())
const helper = inject('helper')
const formData = reactive({
  title: '',
  main_category: [],
  categories: [],
  tags: [],
  text: '',
  link: '',
  cover: []
})
const errors = ref({})
const categories = ref([])
const isPosting = ref(false)
const isPosted = ref({})

provide('formData', formData)

watch(
  () => formData.main_category, () => {
    if (parseInt(formData.main_category) > 0) {
      categories.value = JSON.parse(JSON.stringify(Object.values(page.value.props.categories).filter((category) => {
        return category.id === formData.main_category
      })))[0].descendants
    } else {
      categories.value = []
    }
  }
)

function clearInputs() {
  formData.title = ''
  formData.main_category = []
  formData.categories = []
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

  isPosted.value = {}
  isPosting.value = true
  clearErrors()

  if (!helper.checkFormValidity(form)) {
    return
  }

  await axios
    .postForm(form.action, {
      title: formData.title,
      main_category: formData.main_category,
      categories: formData.categories,
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
      setTimeout(() => { isPosting.value = false }, 1000)
    )
}

function resetForm() {
  clearInputs()
  clearErrors()
}
</script>

<template>
  <po-wrapper class="w-100" style="max-width: 900px;">
    <po-head></po-head>
    <v-card :title="$t('writings.publish-writing').toUpperCase()" :subtitle="$t('main.required-fields-marked')">
      <v-form id="writing-form" :action="$route('writings.create')" class="px-5 pb-5" @submit.prevent="submitForm"
        @reset.prevent="resetForm">
        <v-text-field v-model="formData.title" :label="$t('main.title') + ' *'" hide-details="auto"
          :error-messages="errors.title" :placeholder="$t('main.enter-title')" minlength="3" maxlength="100"
          persistent-placeholder clearable required></v-text-field>

        <v-select v-model="formData.main_category" :label="$t('categories.main-category') + ' *'" hide-details="auto"
          :error-messages="errors.main_category" :placeholder="$t('categories.select-main')" persistent-placeholder
          :items="page.props.categories" item-title="name" item-value="id" clearable required chips></v-select>

        <v-select v-model="formData.categories" :label="$t('categories.alt-categories') + ' *'" hide-details="auto"
          :error-messages="errors.categories" :placeholder="$t('categories.select-alt')" persistent-placeholder
          :items="categories" item-title="name" item-value="id" multiple clearable required chips
          :disabled="$helper.isEmpty(categories)"></v-select>

        <v-combobox v-model="formData.tags" :label="$t('tags.tags')" hide-details="auto" :error-messages="errors.tags"
          :placeholder="$t('tags.enter-tags')"
          pattern="[a-zA-Z0-9,\s\u00c0-\u00d6\u00d8-\u00f6\u00f8-\u02af\u1d00-\u1d25\u1d62-\u1d65\u1d6b-\u1d77\u1d79-\u1d9a\u1e00-\u1eff\u2090-\u2094\u2184-\u2184\u2488-\u2490\u271d-\u271d\u2c60-\u2c7c\u2c7e-\u2c7f\ua722-\ua76f\ua771-\ua787\ua78b-\ua78c\ua7fb-\ua7ff\ufb00-\ufb06]+"
          persistent-placeholder :delimiters="[',']" multiple clearable chips closable-chips></v-combobox>

        <v-textarea v-model="formData.text" :label="$t('main.text') + ' *'" hide-details="auto"
          :error-messages="errors.text" :placeholder="$t('main.enter-text')" minlength="10" maxlength="4000"
          persistent-placeholder clearable required></v-textarea>

        <v-text-field v-model="formData.link" type="url" :label="$t('main.link')" hide-details="auto"
          :error-messages="errors.link" :placeholder="$t('main.enter-link')" minlength="3" maxlength="250"
          persistent-placeholder clearable></v-text-field>

        <v-file-input v-model="formData.cover" :label="$t('main.cover')" hide-details="auto"
          :error-messages="errors.cover" prepend-icon="" :placeholder="$t('main.select-cover')" persistent-placeholder
          :hint="$t('main.max-file-size-is', { size: page.props['max-file-size'] }) + 'kb'" persistent-hint
          clearable></v-file-input>

        <po-agreement v-if="!page.props.agreement"></po-agreement>

        <po-button type="submit" color="primary" size="large" block :disabled="isPosting">
          <template v-if="isPosting"><v-progress-circular indeterminate></v-progress-circular></template>
          <template v-else>{{ $t('main.send') }}</template>
        </po-button>
      </v-form>

      <v-alert v-if="!helper.isEmpty(isPosted)" type="success" variant="tonal" class="mb-5 mx-auto"
        style="width: 85%; max-width: 600px;">
        {{ isPosted.action === 'create' ? $t('writings.writing-published') : $t('writings.writing-updated') }}
        {{ $t('main.take-a-look') }}
        <po-link :href="isPosted.url" inertia>{{ $t('main.here') }}.</po-link>
      </v-alert>
    </v-card>
  </po-wrapper>
</template>