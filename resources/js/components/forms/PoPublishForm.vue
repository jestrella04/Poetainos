<script setup>
import { usePage } from '@inertiajs/vue3';
import { provide } from 'vue';
import { reactive } from 'vue';
import { computed } from 'vue';

const page = computed(() => usePage())
const formData = reactive({})

provide('formData', formData)

function submitForm() {
  const form = document.querySelector('#writing-form')

  if (!form.checkValidity()) {
    form.reportValidity()
    return
  }

  // magic goes here
}
</script>

<template>
  <div class="w-100" style="max-width: 900px;">
    <v-card :title="$t('writings.publish-writing').toUpperCase()" :subtitle="$t('main.required-fields-marked')">
      <v-form id="writing-form" class="px-5 pb-5" @submit.prevent="submitForm()">
        <v-text-field :label="$t('main.title') + ' *'" hide-details="auto" :placeholder="$t('main.enter-title')" clearable
          required></v-text-field>

        <v-select :label="$t('categories.main-category') + ' *'" hide-details="auto"
          :placeholder="$t('categories.select-main')" :items="page.props.categories.main" item-title="name"
          item-value="id" clearable required chips></v-select>

        <v-select :label="$t('categories.alt-categories') + ' *'" hide-details="auto"
          :placeholder="$t('categories.select-alt')" :items="page.props.categories.alt" item-title="name" item-value="id"
          multiple clearable required chips></v-select>

        <v-combobox :label="$t('tags.tags')" hide-details="auto" :placeholder="$t('tags.enter-tags')" :delimiters="[',']"
          multiple clearable chips closable-chips></v-combobox>

        <v-textarea :label="$t('main.text') + ' *'" hide-details="auto" :placeholder="$t('main.enter-text')" clearable
          required></v-textarea>

        <v-text-field type="url" :label="$t('main.link')" hide-details="auto" :placeholder="$t('main.enter-link')"
          clearable></v-text-field>

        <v-file-input :label="$t('main.cover')" hide-details="auto" :placeholder="$t('main.select-cover')"
          :hint="$t('main.max-file-size-is', { size: page.props['max-file-size'] }) + 'kb'" persistent-hint
          clearable></v-file-input>

        <po-agreement></po-agreement>

        <po-button type="submit" color="primary" size="large" block>{{ $t('main.send') }}</po-button>
      </v-form>
    </v-card>
  </div>
</template>