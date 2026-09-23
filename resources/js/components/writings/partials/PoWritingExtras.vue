<script setup lang="ts">
import { computed } from 'vue'
import { useTypeGuards } from '@/composables/useTypeGuards'
import { useFormatting } from '@/composables/useFormatting'
import type { UserLike, Writing } from '@/types/models'

const props = defineProps<{
  data: Writing
  likers?: UserLike[]
}>()

const { isEmpty, strNullOrEmpty } = useTypeGuards()
const { cropUrl } = useFormatting()

const taxonomies = computed(() => [
  {
    icon: 'fas fa-folder-open',
    terms: props.data.categories,
    routeName: 'categories.show',
    color: 'primary'
  },
  {
    icon: 'fas fa-hashtag',
    terms: props.data.tags,
    routeName: 'tags.show',
    color: 'default'
  }
])
</script>

<template>
  <div class="d-flex flex-column ga-3 my-8">
    <div v-if="!isEmpty(data.extra_info) && !strNullOrEmpty(data.extra_info?.link)" class="d-flex">
      <v-icon icon="fas fa-link" size="24" class="mr-3" />

      <po-link :href="data.extra_info?.link" target="_blank" rel="nofollow noopener">
        {{ cropUrl(data.extra_info?.link ?? '') }}
      </po-link>
    </div>

    <template v-for="taxonomy in taxonomies" :key="taxonomy.routeName">
      <div v-if="!isEmpty(taxonomy.terms)" class="d-flex align-center">
        <v-icon :icon="taxonomy.icon" size="24" class="mr-3" />

        <div class="d-inline-flex flex-wrap ga-1">
          <po-chip
            v-for="term in taxonomy.terms"
            :key="term.slug"
            :href="route(taxonomy.routeName, term.slug)"
            :color="taxonomy.color"
            inertia
          >
            {{ term.name }}
          </po-chip>
        </div>
      </div>
    </template>

    <div v-if="!isEmpty(likers)">
      <p class="mb-2">{{ $t('main.liked-by') }}</p>

      <po-avatar-stack :users="likers ?? []" :size="64" color="secondary" />
    </div>
  </div>
</template>
