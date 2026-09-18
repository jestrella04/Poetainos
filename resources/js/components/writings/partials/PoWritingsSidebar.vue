<script setup lang="ts">
import { useFormatting } from '@/composables/useFormatting'
import type { TagLike, UserLike } from '@/types/models'

defineProps<{
  authors: UserLike[]
  tags: TagLike[]
}>()

const { userDisplayName } = useFormatting()
</script>

<template>
  <aside>
    <div v-if="authors.length > 0" class="mb-10">
      <p class="text-caption text-uppercase text-eyebrow text-on-surface-variant mb-4">
        {{ $t('main.authors-to-follow') }}
      </p>

      <div class="d-flex flex-column ga-4">
        <div v-for="author in authors" :key="author.id" class="d-flex align-center ga-3">
          <po-link :href="route('users.show', author.username)" inertia>
            <po-avatar-award :user="author" avatar-size="32" avatar-color="secondary" />
          </po-link>

          <div class="flex-grow-1">
            <po-link
              :href="route('users.show', author.username)"
              class="d-block text-body-2"
              inertia
            >
              {{ userDisplayName(author) }}
            </po-link>
            <span class="text-caption text-on-surface-variant">
              {{ $t('main.count-writings', { count: author.writings_count ?? 0 }) }}
            </span>
          </div>

          <!-- Not wired up yet: following authors has no backend support. -->
          <span class="text-caption text-primary">{{ $t('main.follow') }}</span>
        </div>
      </div>
    </div>

    <div v-if="tags.length > 0">
      <p class="text-caption text-uppercase text-eyebrow text-on-surface-variant mb-4">
        {{ $t('main.trending-topics') }}
      </p>

      <div class="d-inline-flex flex-wrap ga-2">
        <po-chip
          v-for="tag in tags"
          :key="tag.id"
          color="primary"
          :href="route('tags.show', tag.slug)"
          inertia
        >
          {{ tag.name }}
        </po-chip>
      </div>
    </div>
  </aside>
</template>
