<script setup lang="ts">
import PoUsersAccountRow from './partials/PoUsersAccountRow.vue'
import { useFormatting } from '@/composables/useFormatting'
import { useTypeGuards } from '@/composables/useTypeGuards'
import { useInfiniteList } from '@/composables/useInfiniteList'
import type { User } from '@/types/models'

const { isEmpty } = useTypeGuards()
const { userDisplayName } = useFormatting()

const { items: blockedUsers, fetched, loadMore } = useInfiniteList<User>('blockedUsers')
</script>

<template>
  <po-wrapper>
    <po-head />

    <p class="text-uppercase text-eyebrow ma-0 mb-8">
      {{ $t('accounts.blocked-users') }}
    </p>

    <template v-if="!fetched">
      <po-loading />
    </template>

    <template v-else-if="!isEmpty(blockedUsers)">
      <div class="d-flex flex-column">
        <po-users-account-row
          v-for="blockedUser in blockedUsers"
          :key="blockedUser.id"
          :title="userDisplayName(blockedUser)"
          :subtitle="`@${blockedUser.username}`"
        >
          <po-unblocker :user="blockedUser" />
        </po-users-account-row>
      </div>

      <po-infinite-scroll @load="loadMore" />
    </template>

    <template v-else>
      <po-msg-block
        class="py-15"
        msg-title=""
        :msg-body="$t('accounts.no-blocked-users')"
        icon="fas fa-ban"
      />
    </template>
  </po-wrapper>
</template>
