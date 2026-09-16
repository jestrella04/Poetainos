<script setup lang="ts">
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useFormatting } from '@/composables/useFormatting'
import { useTypeGuards } from '@/composables/useTypeGuards'
import type { CategoryLike, TagLike, UserLike } from '@/types/models'
import type { InertiaPageProps } from '@/types/inertia'

interface ExploreProps {
  categories: {
    main: CategoryLike[]
    alt: CategoryLike[]
  }
  tags: TagLike[]
  authors: UserLike[]
}

const { userDisplayName } = useFormatting()
const { strNullOrEmpty } = useTypeGuards()
const page = computed(() => usePage<InertiaPageProps<ExploreProps>>())
</script>

<template>
  <po-head />

  <div class="mb-10">
    <p class="text-h4 mb-2">{{ $t('main.explore') }}</p>
  </div>

  <p class="text-caption text-uppercase text-eyebrow text-on-surface-variant mb-3">
    {{ $t('categories.main-categories') }}
  </p>
  <v-row class="mb-8">
    <v-col v-for="cat in page.props.categories.main" :key="cat.id" cols="12" sm="6" lg="4">
      <v-card :href="route('categories.show', cat.slug)" height="100%" inertia>
        <v-card-text class="d-flex flex-column h-100">
          <p class="text-h6 mb-2">{{ cat.name }}</p>
          <p v-if="!strNullOrEmpty(cat.description)" class="po-prose text-body-2 mb-4 flex-grow-1">
            {{ cat.description }}
          </p>
          <p class="text-caption text-on-surface-variant mb-0">
            {{ $t('main.count-writings', { count: cat.writings_count ?? 0 }) }}
          </p>
        </v-card-text>
      </v-card>
    </v-col>
  </v-row>

  <p class="text-caption text-uppercase text-eyebrow text-on-surface-variant mb-3">
    {{ $t('categories.alt-categories') }}
  </p>
  <div class="d-inline-flex flex-wrap ga-2 mb-8">
    <template v-for="cat in page.props.categories.alt" :key="cat.id">
      <po-chip :href="route('categories.show', cat.slug)" color="secondary" inertia>
        {{ cat.name }}
        <span>&nbsp;({{ cat.writings_count }})</span>
      </po-chip>
    </template>
  </div>

  <p class="text-caption text-uppercase text-eyebrow text-on-surface-variant mb-3">
    {{ $t('tags.tags') }}
  </p>
  <div class="d-inline-flex flex-wrap ga-2 mb-8">
    <template v-for="tag in page.props.tags" :key="tag.id">
      <po-chip :href="route('tags.show', tag.slug)" color="secondary" inertia>
        {{ tag.name }}
        <span>&nbsp;({{ tag.writings_count }})</span>
      </po-chip>
    </template>
  </div>

  <p class="text-caption text-uppercase text-eyebrow text-on-surface-variant mb-3">
    {{ $t('main.featured-authors') }}
  </p>
  <div class="d-inline-flex flex-wrap ga-2">
    <template v-for="author in page.props.authors" :key="author.id">
      <po-link
        :href="route('users.show', author.username)"
        :title="userDisplayName(author)"
        inertia
      >
        <po-avatar-award :user="author" avatar-size="64" avatar-color="secondary" />
      </po-link>
    </template>
  </div>
</template>
