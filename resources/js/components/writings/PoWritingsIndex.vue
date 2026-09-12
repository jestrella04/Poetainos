<script setup lang="ts">
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import PoWritingsEntry from './PoWritingsEntry.vue'
import { useTypeGuards } from '@/composables/useTypeGuards'
import { usePaginatedTabList } from '@/composables/usePaginatedTabList'
import type { InertiaPageProps } from '@/types/inertia'
import type { Writing } from '@/types/models'

const page = computed(() => usePage<InertiaPageProps<{ sort: string }>>())
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
</script>

<template>
  <po-wrapper class="h-100">
    <po-head></po-head>

    <v-row class="sticky-tabs">
      <v-col cols="12">
        <v-tabs :model-value="page.props.sort" fixed-tabs>
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
      </v-col>
    </v-row>

    <template v-if="!fetched">
      <po-loading></po-loading>
    </template>

    <template v-else-if="!isEmpty(writings)">
      <template v-for="writing in writings" :key="writing.slug">
        <po-writings-entry :alone="false" :data="writing" />
      </template>

      <po-infinite-scroll v-if="!strNullOrEmpty(next)" @load="loadMore"></po-infinite-scroll>
    </template>

    <template v-else>
      <po-msg-block
        class="py-15"
        msg-title=""
        :msg-body="$t('main.nothing-to-display')"
        icon="fas fa-sad-tear"
      ></po-msg-block>
    </template>
  </po-wrapper>
</template>
