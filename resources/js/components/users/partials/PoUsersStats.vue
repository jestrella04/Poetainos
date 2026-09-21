<script setup lang="ts">
import { useFormatting } from '@/composables/useFormatting'
import type { User } from '@/types/models'

defineProps<{
  data: User
}>()

const { readable } = useFormatting()
</script>

<template>
  <div class="d-flex justify-center ga-8 mx-auto text-on-surface-variant text-center">
    <div class="d-flex flex-column" :title="$t('main.count-awards', { count: data.awards_count })">
      <div><v-icon icon="fas fa-fan" /></div>
      <div>{{ readable(data.awards_count) }}</div>
    </div>

    <div
      class="d-flex flex-column"
      :title="$t('main.count-comments', { count: data.comments_count })"
    >
      <div><v-icon icon="fas fa-comment" /></div>
      <div>{{ readable(data.comments_count) }}</div>
    </div>

    <div class="d-flex flex-column" :title="$t('main.count-shelved', { count: data.shelf_count })">
      <div><v-icon icon="fas fa-bookmark" /></div>
      <div>{{ readable(data.shelf_count) }}</div>
    </div>

    <div class="d-flex flex-column" :title="$t('main.aura-value', { aura: data.aura })">
      <div><v-icon icon="fas fa-dove" /></div>
      <div>{{ data.aura }}</div>
    </div>
  </div>
</template>
