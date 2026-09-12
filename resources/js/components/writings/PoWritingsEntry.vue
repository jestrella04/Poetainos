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

<style scoped>
/* Tighter line-height for the title and preserved whitespace in user-authored body text; content-specific, not a Vuetify concern. */
.writing-title {
  line-height: 1.6rem !important;
  margin-bottom: 0.3rem;
}

.writing-body {
  white-space: pre-wrap !important;
}
</style>

<template>
  <po-wrapper>
    <v-card
      :class="{ 'position-relative': true, 'writing-container': !alone }"
      elevation="2"
      rounded
    >
      <po-writing-dropdown></po-writing-dropdown>
      <template v-if="!isEmpty(data.extra_info) && !strNullOrEmpty(data.extra_info?.cover)">
        <v-img
          class="align-end text-white"
          height="200"
          :src="storage(data.extra_info?.cover ?? '')"
          alt=""
          cover
        >
          <div class="text-center py-3">
            <po-link :href="route('users.show', data.author.username)" inertia>
              <po-avatar-award :user="data.author" avatar-size="64" avatar-color="secondary" />
            </po-link>
          </div>
        </v-img>
      </template>

      <div v-else class="text-center pt-6">
        <po-link :href="route('users.show', data.author.username)" inertia>
          <po-avatar-award :user="data.author" avatar-size="64" avatar-color="secondary" />
        </po-link>
      </div>

      <v-card-text class="position-relative pt-1">
        <div class="text-center mb-3">
          <p class="text-h6 text-uppercase writing-title">
            <po-link
              v-if="!alone"
              :href="route('writings.show', data.slug)"
              class="stretched"
              inertia
            >
              {{ data.title }}
            </po-link>
            <span v-else>{{ data.title }}</span>
          </p>

          <p class="text-caption text-uppercase text-medium-emphasis">
            {{
              `${toLocaleDate(data.created_at)}
            — ${$t('main.by-name', { name: userDisplayName(data.author) })}
            `
            }}
          </p>
        </div>

        <template v-if="alone">
          <blockquote class="writing-body mb-4">
            {{ data.text }}
          </blockquote>

          <template v-if="!isEmpty(data.extra_info) && !strNullOrEmpty(data.extra_info?.link)">
            <div class="d-flex align-center mb-4">
              <v-icon icon="fas fa-link" size="24" class="mr-3"></v-icon>
              <po-link :href="data.extra_info?.link" target="_blank" rel="nofollow noopener">
                {{ cropUrl(data.extra_info?.link ?? '') }}
              </po-link>
            </div>
          </template>

          <div class="d-flex flex-column ga-3 mb-4">
            <div v-if="!isEmpty(data.categories)" class="d-flex">
              <div class="mr-3">
                <v-icon icon="fas fa-folder-open" size="24"></v-icon>
              </div>

              <div class="d-inline-flex flex-wrap ga-1">
                <po-chip
                  v-for="category in data.categories"
                  :key="category.slug"
                  color="secondary"
                  variant="elevated"
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
                <v-icon icon="fas fa-hashtag" size="24"></v-icon>
              </div>

              <div class="d-inline-flex flex-wrap ga-1">
                <po-chip
                  v-for="tag in data.tags"
                  :key="tag.slug"
                  color="secondary"
                  variant="elevated"
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
        </template>

        <template v-else>
          <blockquote class="writing-body">
            {{ excerpt(data.text) }}
          </blockquote>
        </template>
      </v-card-text>

      <v-divider></v-divider>
      <v-card-actions>
        <po-writing-stats></po-writing-stats>
      </v-card-actions>
    </v-card>

    <template v-if="alone">
      <v-skeleton-loader
        v-if="loadingComments"
        :elevation="2"
        type="list-item-avatar"
        class="mb-2"
      ></v-skeleton-loader>
      <v-skeleton-loader
        v-if="loadingComments"
        :elevation="2"
        type="list-item-avatar"
        class="mb-2"
      ></v-skeleton-loader>
      <v-skeleton-loader
        v-if="loadingComments"
        :elevation="2"
        type="list-item-avatar"
        class="mb-2"
      ></v-skeleton-loader>

      <po-comments-index />
    </template>
  </po-wrapper>
</template>
