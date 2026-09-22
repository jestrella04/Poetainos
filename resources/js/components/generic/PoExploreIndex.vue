<script setup lang="ts">
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useFormatting } from '@/composables/useFormatting'
import { useTypeGuards } from '@/composables/useTypeGuards'
import type { CategoryLike, TagLike, UserLike } from '@/types/models'
import type { InertiaPageProps } from '@/types/inertia'

interface ExploreProps {
  totals: {
    writings: number
    authors: number
  }
  categories: {
    main: CategoryLike[]
    alt: CategoryLike[]
  }
  tags: TagLike[]
  authors: UserLike[]
}

const { formatCount } = useFormatting()
const { strNullOrEmpty } = useTypeGuards()
const page = usePage<InertiaPageProps<ExploreProps>>()
const categories = computed(() => [...page.props.categories.main, ...page.props.categories.alt])
</script>

<template>
  <po-head />

  <p class="text-display-large po-prose ma-0 mb-2">
    {{ $t('main.explore') }}
  </p>

  <p class="text-headline-small text-medium-emphasis po-prose ma-0 mb-12">
    {{
      $t('main.explore-subtitle', {
        writings: formatCount(page.props.totals.writings),
        authors: formatCount(page.props.totals.authors)
      })
    }}
  </p>

  <p class="text-uppercase text-eyebrow mb-3">
    {{ $t('categories.category') }}
  </p>

  <v-row :gap="[12, 0]" class="mb-12">
    <v-col v-for="cat in categories" :key="cat.id" cols="12" sm="6" lg="4">
      <po-card
        variant="text"
        class="border-b-md"
        :href="route('categories.show', cat.slug)"
        height="100%"
        inertia
      >
        <v-card-text class="d-flex flex-column h-100">
          <p class="text-headline-large po-prose ma-0 mb-2">{{ cat.name }}</p>

          <p
            v-if="!strNullOrEmpty(cat.description)"
            class="text-title-large po-prose ma-0 mb-2 flex-grow-1"
          >
            {{ cat.description }}
          </p>

          <p class="text-medium-emphasis mb-0">
            {{ $t('main.count-writings', { count: cat.writings_count ?? 0 }) }}
          </p>
        </v-card-text>
      </po-card>
    </v-col>
  </v-row>

  <p class="text-uppercase text-eyebrow mb-3">
    {{ $t('main.recurring-motifs') }}
  </p>

  <div class="d-inline-flex flex-wrap ga-2 mb-12">
    <template v-for="tag in page.props.tags" :key="tag.id">
      <po-chip
        :href="route('tags.show', tag.slug)"
        color="primary"
        size="x-large"
        variant="tonal"
        inertia
      >
        {{ tag.name }}
        <template v-slot:append>
          <v-avatar color="primary" end>{{ tag.writings_count }}</v-avatar>
        </template>
      </po-chip>
    </template>
  </div>

  <p class="text-uppercase text-eyebrow mb-3">
    {{ $t('main.featured-voices') }}
  </p>

  <po-avatar-stack :users="page.props.authors" :size="96" color="primary" />
</template>
