<script setup lang="ts">
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import PoUsersCard from './partials/PoUsersCard.vue'
import { useFormatting } from '@/composables/useFormatting'
import { useTypeGuards } from '@/composables/useTypeGuards'
import { usePaginatedTabList } from '@/composables/usePaginatedTabList'
import type { InertiaPageProps } from '@/types/inertia'
import type { User } from '@/types/models'

const page = computed(() => usePage<InertiaPageProps<{ sort: string; totalAuthors: number }>>())
const { isEmpty } = useTypeGuards()
const { formatCount } = useFormatting()

const {
  items: users,
  fetched,
  loadMore
} = usePaginatedTabList<User>({
  tabOrder: ['featured', 'latest', 'popular'],
  currentTab: () => page.value.props.sort,
  reloadPropKey: 'users'
})
</script>

<template>
  <po-wrapper class="h-100">
    <po-head />

    <p class="text-display-large po-prose ma-0 mb-2">
      {{ $t('users.authors') }}
    </p>

    <p class="text-headline-small text-medium-emphasis po-prose ma-0 mb-8">
      {{ $t('main.authors-subtitle', { authors: formatCount(page.props.totalAuthors) }) }}
    </p>

    <div class="sticky-tabs">
      <v-tabs :model-value="page.props.sort" color="primary" fixed-tabs>
        <po-tab href="?sort=featured" value="featured" :aria-label="$t('main.featured')" inertia>
          <v-icon icon="fas fa-fan" class="d-md-none" />
          <span class="d-none d-md-inline">{{ $t('main.featured') }}</span>
        </po-tab>

        <po-tab href="?sort=latest" value="latest" :aria-label="$t('main.most-recent')" inertia>
          <v-icon icon="fas fa-clock" class="d-md-none" />
          <span class="d-none d-md-inline">{{ $t('main.most-recent') }}</span>
        </po-tab>

        <po-tab href="?sort=popular" value="popular" :aria-label="$t('main.most-popular')" inertia>
          <v-icon icon="fas fa-fire" class="d-md-none" />
          <span class="d-none d-md-inline">{{ $t('main.most-popular') }}</span>
        </po-tab>
      </v-tabs>
    </div>

    <template v-if="!fetched">
      <po-loading />
    </template>

    <template v-else-if="!isEmpty(users)">
      <v-row class="mt-8">
        <v-col v-for="user in users" :key="user.id" cols="12" sm="6">
          <po-users-card :data="user" />
        </v-col>
      </v-row>

      <po-infinite-scroll @load="loadMore" />
    </template>

    <template v-else>
      <po-msg-block
        class="py-15"
        msg-title=""
        :msg-body="$t('main.nothing-to-display')"
        icon="fas fa-sad-tear"
      />
    </template>
  </po-wrapper>
</template>
