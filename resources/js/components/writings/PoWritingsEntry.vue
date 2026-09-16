<script setup lang="ts">
import { ref, provide } from 'vue'
import PoCommentsIndex from '../comments/PoCommentsIndex.vue'
import PoWritingStats from './partials/PoWritingStats.vue'
import PoWritingDropdown from './partials/PoWritingDropdown.vue'
import { loadingCommentsKey, writingKey } from '@/composables/keys'
import { useTypeGuards } from '@/composables/useTypeGuards'
import { useFormatting } from '@/composables/useFormatting'
import type { UserLike, Writing } from '@/types/models'

const props = withDefaults(
  defineProps<{
    alone?: boolean
    data: Writing
    likers?: UserLike[]
  }>(),
  {
    alone: true
  }
)

const { isEmpty, strNullOrEmpty } = useTypeGuards()
const { storage, toLocaleDate, userDisplayName, cropUrl, excerpt } = useFormatting()
const loadingComments = ref(true)

provide(loadingCommentsKey, loadingComments)
provide(writingKey, props.data)
</script>

<template>
  <po-wrapper>
    <!-- Single-writing reading view -->
    <template v-if="alone">
      <v-card class="position-relative">
        <po-writing-dropdown />

        <template v-if="!isEmpty(data.extra_info) && !strNullOrEmpty(data.extra_info?.cover)">
          <v-img height="220" :src="storage(data.extra_info?.cover ?? '')" alt="" cover />
        </template>

        <v-card-text class="position-relative">
          <p class="text-caption text-uppercase text-eyebrow text-on-surface-variant mb-2">
            {{ toLocaleDate(data.created_at) }}
          </p>

          <p class="writing-title text-h4">{{ data.title }}</p>

          <div class="d-flex align-center ga-3 mt-4 mb-6 pb-4 border-b">
            <po-link :href="route('users.show', data.author.username)" inertia>
              <po-avatar-award :user="data.author" avatar-size="36" avatar-color="secondary" />
            </po-link>
            <po-link :href="route('users.show', data.author.username)" inertia>
              {{ userDisplayName(data.author) }}
            </po-link>
          </div>

          <blockquote class="writing-body writing-body--reading po-prose mb-4">
            {{ data.text }}
          </blockquote>

          <template v-if="!isEmpty(data.extra_info) && !strNullOrEmpty(data.extra_info?.link)">
            <div class="d-flex align-center mb-4">
              <v-icon icon="fas fa-link" size="24" class="mr-3" />
              <po-link :href="data.extra_info?.link" target="_blank" rel="nofollow noopener">
                {{ cropUrl(data.extra_info?.link ?? '') }}
              </po-link>
            </div>
          </template>

          <div class="d-flex flex-column ga-3 mb-4">
            <div v-if="!isEmpty(data.categories)" class="d-flex">
              <div class="mr-3">
                <v-icon icon="fas fa-folder-open" size="24" />
              </div>

              <div class="d-inline-flex flex-wrap ga-1">
                <po-chip
                  v-for="category in data.categories"
                  :key="category.slug"
                  color="secondary"
                  size="small"
                  :href="route('categories.show', category.slug)"
                  inertia
                >
                  {{ category.name }}
                </po-chip>
              </div>
            </div>

            <div v-if="!isEmpty(data.tags)" class="d-flex">
              <div class="mr-3">
                <v-icon icon="fas fa-hashtag" size="24" />
              </div>

              <div class="d-inline-flex flex-wrap ga-1">
                <po-chip
                  v-for="tag in data.tags"
                  :key="tag.slug"
                  color="secondary"
                  size="small"
                  :href="route('tags.show', tag.slug)"
                  inertia
                >
                  {{ tag.name }}
                </po-chip>
              </div>
            </div>
          </div>

          <div v-if="!isEmpty(likers)">
            <p class="text-caption mb-2">{{ $t('main.liked-by') }}</p>

            <div class="d-inline-flex flex-wrap ga-2">
              <div v-for="liker in likers" :key="liker.id">
                <po-button
                  icon
                  :href="route('users.show', liker.username)"
                  :title="userDisplayName(liker)"
                  inertia
                >
                  <po-avatar size="48" color="secondary" :user="liker" />
                </po-button>
              </div>
              <div v-if="(likers?.length ?? 0) > 5">
                <v-avatar size="48" color="secondary" text="+" />
              </div>
            </div>
          </div>
        </v-card-text>

        <v-divider />
        <v-card-actions>
          <po-writing-stats />
        </v-card-actions>
      </v-card>

      <v-skeleton-loader
        v-if="loadingComments"
        :elevation="2"
        type="list-item-avatar"
        class="mb-2"
      />
      <v-skeleton-loader
        v-if="loadingComments"
        :elevation="2"
        type="list-item-avatar"
        class="mb-2"
      />
      <v-skeleton-loader
        v-if="loadingComments"
        :elevation="2"
        type="list-item-avatar"
        class="mb-2"
      />

      <po-comments-index />
    </template>

    <!-- Feed row (listing) -->
    <template v-else>
      <article class="position-relative py-12 border-b">
        <po-writing-dropdown />

        <p class="text-medium-emphasis text-uppercase text-eyebrow ma-0">
          {{ toLocaleDate(data.created_at) }}
        </p>

        <p class="po-prose text-display-small ma-0 mb-2">
          <po-link :href="route('writings.show', data.slug)" class="stretched" inertia>
            {{ data.title }}
          </po-link>
        </p>

        <p class="po-prose text-title-large mb-6">
          {{ excerpt(data.text) }}
        </p>

        <div class="d-flex align-center flex-wrap ga-2 position-relative" style="z-index: 2">
          <po-link :href="route('users.show', data.author.username)" inertia>
            <po-avatar-award :user="data.author" avatar-size="28" avatar-color="secondary" />
          </po-link>

          <po-link :href="route('users.show', data.author.username)" class="text-body-2" inertia>
            {{ userDisplayName(data.author) }}
          </po-link>

          <span class="">
            · {{ $t('main.count-likes', { count: data.likes_count }) }} ·
            {{ $t('main.count-comments', { count: data.comments_count }) }}
          </span>
        </div>
      </article>
    </template>
  </po-wrapper>
</template>
