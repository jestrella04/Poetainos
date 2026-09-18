<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import PoWritingsEntry from './PoWritingsEntry.vue'
import PoWritingsSidebar from './partials/PoWritingsSidebar.vue'
import { useTypeGuards } from '@/composables/useTypeGuards'
import { usePaginatedTabList } from '@/composables/usePaginatedTabList'
import type { InertiaPageProps } from '@/types/inertia'
import type { TagLike, UserLike, Writing } from '@/types/models'

interface WritingsIndexProps {
  sort: string
  isHome: boolean
  authors: UserLike[] | null
  tags: TagLike[] | null
}

const page = computed(() => usePage<InertiaPageProps<WritingsIndexProps>>())
const { isEmpty, strNullOrEmpty } = useTypeGuards()

const {
  items: writings,
  next,
  fetched,
  loadMore
} = usePaginatedTabList<Writing>({
  tabOrder: ['latest', 'popular', 'likes'],
  currentTab: () => page.value.props.sort,
  reloadPropKey: 'writings'
})

const heroWriting = ref<Writing | null>(null)

// Temporary placeholder curation: pick a random writing from the first
// loaded batch as the "pick of the day" feature. Real curation logic is
// planned separately.
watch(
  fetched,
  (isFetched) => {
    if (
      isFetched &&
      page.value.props.isHome &&
      heroWriting.value === null &&
      !isEmpty(writings.value)
    ) {
      const index = Math.floor(Math.random() * writings.value.length)
      heroWriting.value = writings.value[index] ?? null
    }
  },
  { immediate: true }
)

const restWritings = computed(() =>
  heroWriting.value === null
    ? writings.value
    : writings.value.filter((writing) => writing.slug !== heroWriting.value?.slug)
)
</script>

<template>
  <po-wrapper class="h-100">
    <po-head />

    <template v-if="page.props.isHome && heroWriting !== null">
      <p class="text-uppercase text-primary mb-6">
        {{ $t('main.pick-of-the-day') }}
      </p>

      <po-writings-entry :alone="false" :data="heroWriting" hero />
    </template>

    <v-row>
      <v-col v-if="page.props.isHome" cols="12" md="3" order="1" order-md="2">
        <po-writings-sidebar :authors="page.props.authors ?? []" :tags="page.props.tags ?? []" />
      </v-col>

      <v-col cols="12" :md="page.props.isHome ? 9 : 12" order="2" order-md="1" class="pe-md-12">
        <div class="sticky-tabs">
          <v-tabs :model-value="page.props.sort" color="primary" fixed-tabs>
            <po-tab href="?sort=latest" value="latest" :aria-label="$t('main.most-recent')" inertia>
              <v-icon icon="fas fa-clock" class="d-md-none" />
              <span class="d-none d-md-inline">{{ $t('main.most-recent') }}</span>
            </po-tab>

            <po-tab
              href="?sort=popular"
              value="popular"
              :aria-label="$t('main.most-popular')"
              inertia
            >
              <v-icon icon="fas fa-fire" class="d-md-none" />
              <span class="d-none d-md-inline">{{ $t('main.most-popular') }}</span>
            </po-tab>

            <po-tab href="?sort=likes" value="likes" :aria-label="$t('main.most-liked')" inertia>
              <v-icon icon="fas fa-heart" class="d-md-none" />
              <span class="d-none d-md-inline">{{ $t('main.most-liked') }}</span>
            </po-tab>
          </v-tabs>
        </div>

        <template v-if="!fetched">
          <po-loading />
        </template>

        <template v-else-if="!isEmpty(writings)">
          <template v-for="writing in restWritings" :key="writing.slug">
            <po-writings-entry :alone="false" :data="writing" />
          </template>

          <po-infinite-scroll v-if="!strNullOrEmpty(next)" @load="loadMore" />
        </template>

        <template v-else>
          <po-msg-block
            class="py-15"
            msg-title=""
            :msg-body="$t('main.nothing-to-display')"
            icon="fas fa-sad-tear"
          />
        </template>
      </v-col>
    </v-row>
  </po-wrapper>
</template>
