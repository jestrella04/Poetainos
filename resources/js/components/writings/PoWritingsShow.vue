<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import PoWritingsEntry from './PoWritingsEntry.vue'

const page = computed(() => usePage())
</script>

<template>
  <po-head></po-head>
  <v-row>
    <v-col cols="12" md="8">
      <po-writings-entry :data="page.props.writing" :likers="page.props.likers" />
    </v-col>

    <v-col cols="12" md="4">
      <v-card :title="$t('main.explore-more').toLocaleUpperCase()">
        <v-card-text class="mt-3">
          <div v-if="!$helper.isEmpty(page.props.related.from_author)">
            <p class="text-uppercase font-medium-weight mb-5">{{ $t('main.from-author') }}</p>

            <template v-for="writing in page.props.related.from_author" :key="writing.id">
              <div class="mb-2 pos-relative">
                <po-link :href="$route('writings.show', writing.slug)" class="text-bold stretched" inertia>
                  {{ writing.title }}
                </po-link>

                <p class="text-caption text-disabled">
                  {{ $t('main.by-name', { name: $helper.userDisplayName(page.props.writing.author) }) }}
                  {{ $helper.relativeDate(writing.created_at) }}
                </p>
              </div>
            </template>

            <v-divider class="my-8"></v-divider>
          </div>

          <div v-if="!$helper.isEmpty(page.props.related.from_category)">
            <p class="text-uppercase font-medium-weight mb-5">{{ $t('main.from-categories') }}</p>
            <template v-for="writing in page.props.related.from_category" :key="writing.id">
              <div class="mb-2 pos-relative">
                <po-link :href="$route('writings.show', writing.slug)" class="text-bold stretched" inertia>
                  {{ writing.title }}
                </po-link>

                <p class="text-caption text-disabled">
                  {{ $t('main.by-name', { name: $helper.userDisplayName(writing.author) }) }}
                  {{ $helper.relativeDate(writing.created_at) }}
                </p>
              </div>
            </template>
          </div>
        </v-card-text>
      </v-card>
    </v-col>
  </v-row>
</template>