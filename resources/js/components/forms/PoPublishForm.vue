<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = computed(() => usePage())
</script>

<template>
  <div class="w-100" style="max-width: 900px;">
    <v-card :title="$t('writings.publish-writing').toUpperCase()" :subtitle="$t('main.required-fields-marked')">
      <v-form @submit.prevent class="px-5 pb-5">
        <v-text-field :label="$t('main.title') + ' *'" :placeholder="$t('main.enter-title')" clearable></v-text-field>

        <v-select :label="$t('categories.main-category') + ' *'" :placeholder="$t('categories.select-main')"
          :items="page.props.categories.main" item-title="name" item-value="id" clearable chips></v-select>

        <v-select :label="$t('categories.alt-categories') + ' *'" :placeholder="$t('categories.select-alt')"
          :items="page.props.categories.alt" item-title="name" item-value="id" multiple clearable chips></v-select>

        <v-combobox :label="$t('tags.tags')" :placeholder="$t('tags.enter-tags')" :delimiters="[',']" multiple clearable
          chips closable-chips></v-combobox>

        <v-textarea :label="$t('main.text') + ' *'" :placeholder="$t('main.enter-text')" clearable></v-textarea>

        <v-text-field type="url" :label="$t('main.link')" :placeholder="$t('main.enter-link')" clearable></v-text-field>

        <v-file-input :label="$t('main.cover')" :placeholder="$t('main.select-cover')"
          :hint="$t('main.max-file-size-is', { size: page.props['max-file-size'] }) + 'kb'" persistent-hint
          clearable></v-file-input>

        <po-agreement></po-agreement>

        <po-button type="submit" color="primary" size="large" block>{{ $t('main.send') }}</po-button>
      </v-form>
    </v-card>
  </div>
</template>