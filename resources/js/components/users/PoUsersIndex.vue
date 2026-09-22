<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import PoUsersCard from './partials/PoUsersCard.vue'
import { useFormatting } from '@/composables/useFormatting'
import { useTypeGuards } from '@/composables/useTypeGuards'
import { useInfiniteList } from '@/composables/useInfiniteList'
import { useSwipeTabs } from '@/composables/useSwipeTabs'
import type { InertiaPageProps } from '@/types/inertia'
import type { User } from '@/types/models'

const page = usePage<InertiaPageProps<{ sort: string; totalAuthors: number }>>()
const { isEmpty } = useTypeGuards()
const { formatCount } = useFormatting()

const { items: users, fetched, loadMore } = useInfiniteList<User>('users')
useSwipeTabs({ tabOrder: ['featured', 'latest', 'popular'], currentTab: () => page.props.sort })
</script>

<template>
  <po-wrapper class="h-100">
    <po-head />

    <p class="text-display-large po-prose ma-0 mb-2">
      {{ $t('users.authors') }}
    </p>

    <p class="text-headline-small text-medium-emphasis po-prose ma-0 mb-8">
      {{
        $t('main.authors-subtitle', {
          site_name: page.props.site.name,
          authors: formatCount(page.props.totalAuthors)
        })
      }}
    </p>

    <div class="sticky-tabs">
      <v-tabs :model-value="page.props.sort" color="primary" class="mb-8" fixed-tabs>
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
      <v-row :gap="[12, 0]">
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
