<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import PoUsersEntry from './PoUsersEntry.vue'
import PoWritingsEntry from '../writings/PoWritingsEntry.vue'
import { useTypeGuards } from '@/composables/useTypeGuards'
import { useFormatting } from '@/composables/useFormatting'
import { usePaginatedTabList } from '@/composables/usePaginatedTabList'
import type { InertiaPageProps } from '@/types/inertia'
import type { User, Writing } from '@/types/models'

interface WritingSummary {
  id: number
  slug: string
  title: string
  author: User
  created_at: string
}

interface UsersShowProps {
  isAuthorBlocked: boolean
  user: User
  writings: {
    from_shelf: WritingSummary[]
    from_liked: WritingSummary[]
  }
}

const { isEmpty, strNullOrEmpty } = useTypeGuards()
const { userDisplayName, relativeDate, formatCount } = useFormatting()
const page = usePage<InertiaPageProps<UsersShowProps>>()

const {
  items: authorWritings,
  next,
  fetched,
  loadMore
} = usePaginatedTabList<Writing>({
  tabOrder: [],
  currentTab: () => 'latest',
  reloadPropKey: 'authorWritings'
})
</script>

<template>
  <po-wrapper>
    <po-head />

    <template v-if="page.props.isAuthorBlocked">
      <div class="d-flex align-center mx-auto" style="height: 500px; width: 500px">
        <po-msg-block
          :msg-title="$t('users.user-is-blocked')"
          :msg-body="$t('main.author-blocked')"
          icon="fas fa-ban"
        >
          <po-unblocker :user="page.props.user" />
        </po-msg-block>
      </div>
    </template>
    <template v-else>
      <po-users-entry :data="page.props.user" />

      <v-row>
        <v-col cols="12" md="8">
          <template v-if="!fetched">
            <po-loading />
          </template>

          <template v-else-if="!isEmpty(authorWritings)">
            <po-writings-entry
              v-for="writing in authorWritings"
              :key="writing.slug"
              :alone="false"
              :data="writing"
              hide-author
            />

            <po-infinite-scroll v-if="!strNullOrEmpty(next)" @load="loadMore" />

            <po-link
              :href="route('users.writings.index', page.props.user.username)"
              class="d-inline-block text-primary"
              inertia
            >
              {{
                $t(
                  'users.view-writings-count',
                  { count: formatCount(page.props.user.writings_count) },
                  page.props.user.writings_count
                )
              }}
            </po-link>
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

        <v-col cols="12" md="4">
          <template v-if="!isEmpty(page.props.writings.from_shelf)">
            <p class="text-uppercase text-medium-emphasis text-primary text-eyebrow">
              {{ $t('main.more-from-shelf') }}
            </p>

            <template v-for="writing in page.props.writings.from_shelf" :key="writing.id">
              <div class="position-relative">
                <po-link
                  :href="route('writings.show', writing.slug)"
                  class="text-title-large po-prose stretched"
                  inertia
                >
                  {{ writing.title }}
                </po-link>

                <div class="d-inline-flex ga-3">
                  <span>{{ $t('main.by-name', { name: userDisplayName(writing.author) }) }}</span>
                  <span>{{ relativeDate(writing.created_at) }}</span>
                </div>
              </div>
              <v-divider class="my-4" />
            </template>

            <po-link
              :href="route('users.shelf.index', page.props.user.username)"
              class="d-inline-block text-primary"
              inertia
            >
              {{
                $t(
                  'users.view-shelf-count',
                  { count: formatCount(page.props.user.shelf_count) },
                  page.props.user.shelf_count
                )
              }}
            </po-link>
          </template>

          <template v-if="!isEmpty(page.props.writings.from_liked)">
            <p class="text-uppercase text-medium-emphasis text-primary text-eyebrow mt-12">
              {{ $t('main.more-from-liked') }}
            </p>

            <template v-for="writing in page.props.writings.from_liked" :key="writing.id">
              <div class="position-relative">
                <po-link
                  :href="route('writings.show', writing.slug)"
                  class="text-title-large po-prose stretched"
                  inertia
                >
                  {{ writing.title }}
                </po-link>

                <div class="d-inline-flex ga-3">
                  <span>{{ $t('main.by-name', { name: userDisplayName(writing.author) }) }}</span>
                  <span>{{ relativeDate(writing.created_at) }}</span>
                </div>
              </div>
              <v-divider class="my-4" />
            </template>

            <po-link
              :href="route('users.likes.index', page.props.user.username)"
              class="d-inline-block text-primary"
              inertia
            >
              {{
                $t(
                  'users.view-liked-count',
                  { count: formatCount(page.props.user.likes_count) },
                  page.props.user.likes_count
                )
              }}
            </po-link>
          </template>
        </v-col>
      </v-row>
    </template>
  </po-wrapper>
</template>
