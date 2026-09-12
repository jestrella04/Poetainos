<script setup lang="ts">
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import PoUsersCard from './partials/PoUsersCard.vue'
import { useTypeGuards } from '@/composables/useTypeGuards'
import { usePaginatedTabList } from '@/composables/usePaginatedTabList'
import type { InertiaPageProps } from '@/types/inertia'
import type { User } from '@/types/models'

const page = computed(() => usePage<InertiaPageProps<{ sort: string }>>())
const { isEmpty } = useTypeGuards()

const { items: users, fetched, loadMore } = usePaginatedTabList<User>({
  tabOrder: ['featured', 'latest', 'popular'],
  currentTab: () => page.value.props.sort,
  reloadPropKey: 'users'
})
</script>

<template>
  <po-head />

  <v-row class="sticky-tabs">
    <v-col cols="12">
      <v-tabs :model-value="page.props.sort" fixed-tabs>
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
    </v-col>
  </v-row>

  <template v-if="!fetched">
    <po-loading type="avatar, paragraph, divider, text"></po-loading>
  </template>

  <template v-else-if="!isEmpty(users)">
    <template v-for="user in users" :key="user.id">
      <po-users-card :alone="false" :data="user" />
    </template>

    <po-infinite-scroll @load="loadMore"></po-infinite-scroll>
  </template>

  <template v-else>
    <po-msg-block
      class="py-15"
      msg-title=""
      :msg-body="$t('main.nothing-to-display')"
      icon="fas fa-sad-tear"
    ></po-msg-block>
  </template>
</template>
