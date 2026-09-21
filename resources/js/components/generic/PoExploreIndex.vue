<script setup lang="ts">
import { computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { VAvatarGroup } from 'vuetify/labs/VAvatarGroup'
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

const { userDisplayName, formatCount } = useFormatting()
const { strNullOrEmpty } = useTypeGuards()
const page = computed(() => usePage<InertiaPageProps<ExploreProps>>())
const categories = computed(() => [
  ...page.value.props.categories.main,
  ...page.value.props.categories.alt
])
</script>

<template>
  <po-head />

  <p class="text-display-large po-prose ma-0 mb-2">
    {{ $t('main.explore') }}
  </p>

  <p class="text-headline-small text-medium-emphasis po-prose ma-0 mb-8">
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

  <v-row class="mb-8">
    <v-col v-for="cat in categories" :key="cat.id" cols="12" sm="6" lg="4">
      <v-card :href="route('categories.show', cat.slug)" height="100%" inertia>
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
      </v-card>
    </v-col>
  </v-row>

  <p class="text-uppercase text-eyebrow mb-3">
    {{ $t('tags.tags') }}
  </p>

  <div class="d-inline-flex flex-wrap ga-2 mb-8">
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
    {{ $t('main.featured-authors') }}
  </p>

  <v-avatar-group size="96" border="md" hoverable>
    <po-avatar
      v-for="author in page.props.authors"
      :key="author.id"
      tag="a"
      color="primary"
      :user="author"
      :href="route('users.show', author.username)"
      :title="userDisplayName(author)"
      @click.prevent="router.visit(route('users.show', author.username))"
    />
  </v-avatar-group>
</template>
