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
      <p class="text-uppercase text-eyebrow mb-4">
        {{ $t('main.featured-voices') }}
      </p>

      <div v-for="author in authors" :key="author.id">
        <div class="d-flex align-center ga-3 position-relative">
          <po-link :href="route('users.show', author.username)" class="stretched" inertia>
            <po-avatar-award :user="author" avatar-size="32" avatar-color="primary" />
          </po-link>

          <div class="flex-grow-1">
            <po-link :href="route('users.show', author.username)" class="d-block" inertia>
              <span class="text-title-large po-prose"> {{ userDisplayName(author) }}</span>
            </po-link>

            <span> @{{ author.username }} </span>
          </div>

          <po-chip class="text-primary">
            {{ $t('main.count-writings', { count: author.writings_count ?? 0 }) }}
          </po-chip>
        </div>

        <v-divider class="my-4" />
      </div>
    </div>

    <div v-if="tags.length > 0">
      <p class="text-uppercase text-eyebrow mb-4">
        {{ $t('main.recurring-motifs') }}
      </p>

      <div class="d-inline-flex flex-wrap ga-2">
        <po-chip
          v-for="tag in tags"
          :key="tag.id"
          :href="route('tags.show', tag.slug)"
          color="primary"
          size="large"
          variant="tonal"
          inertia
        >
          {{ tag.name }}
          <template v-slot:append>
            <v-avatar color="primary" end>{{ tag.writings_count }}</v-avatar>
          </template>
        </po-chip>
      </div>
    </div>
  </aside>
</template>
