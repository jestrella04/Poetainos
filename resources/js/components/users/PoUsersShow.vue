<script setup lang="ts">
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import PoUsersEntry from './PoUsersEntry.vue'
import { useTypeGuards } from '@/composables/useTypeGuards'
import { useFormatting } from '@/composables/useFormatting'
import type { InertiaPageProps } from '@/types/inertia'
import type { User } from '@/types/models'

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
    from_author: WritingSummary[]
    from_shelf: WritingSummary[]
    from_liked: WritingSummary[]
  }
}

const { isEmpty } = useTypeGuards()
const { userDisplayName, relativeDate } = useFormatting()
const page = computed(() => usePage<InertiaPageProps<UsersShowProps>>())
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
        />
      </div>
    </template>
    <template v-else>
      <v-row>
        <v-col cols="12" md="8">
          <po-users-entry :data="page.props.user" />
        </v-col>

        <v-col cols="12" md="4">
          <v-card v-if="!isEmpty(page.props.writings.from_author)" class="mb-6">
            <v-card-text class="mt-3">
              <p class="text-caption text-uppercase text-eyebrow text-on-surface-variant mb-5">
                {{ $t('main.more-from-author') }}
              </p>

              <template v-for="writing in page.props.writings.from_author" :key="writing.id">
                <div class="mb-2 position-relative">
                  <po-link
                    :href="route('writings.show', writing.slug)"
                    class="text-bold stretched"
                    inertia
                  >
                    {{ writing.title }}
                  </po-link>

                  <p class="text-caption text-eyebrow text-on-surface-variant">
                    {{ $t('main.by-name', { name: userDisplayName(writing.author) }) }}
                    {{ relativeDate(writing.created_at) }}
                  </p>
                </div>
              </template>
            </v-card-text>
          </v-card>

          <v-card v-if="!isEmpty(page.props.writings.from_shelf)" class="mb-6">
            <v-card-text>
              <p class="text-caption text-uppercase text-eyebrow text-on-surface-variant mb-5">
                {{ $t('main.more-from-shelf') }}
              </p>

              <template v-for="writing in page.props.writings.from_shelf" :key="writing.id">
                <div class="mb-2 position-relative">
                  <po-link
                    :href="route('writings.show', writing.slug)"
                    class="text-bold stretched"
                    inertia
                  >
                    {{ writing.title }}
                  </po-link>

                  <p class="text-caption text-eyebrow text-on-surface-variant">
                    {{ $t('main.by-name', { name: userDisplayName(writing.author) }) }}
                    {{ relativeDate(writing.created_at) }}
                  </p>
                </div>
              </template>
            </v-card-text>
          </v-card>

          <v-card v-if="!isEmpty(page.props.writings.from_liked)">
            <v-card-text>
              <p class="text-caption text-uppercase text-eyebrow text-on-surface-variant mb-5">
                {{ $t('main.more-from-liked') }}
              </p>

              <template v-for="writing in page.props.writings.from_liked" :key="writing.id">
                <div class="mb-2 position-relative">
                  <po-link
                    :href="route('writings.show', writing.slug)"
                    class="text-bold stretched"
                    inertia
                  >
                    {{ writing.title }}
                  </po-link>

                  <p class="text-caption text-eyebrow text-on-surface-variant">
                    {{ $t('main.by-name', { name: userDisplayName(writing.author) }) }}
                    {{ relativeDate(writing.created_at) }}
                  </p>
                </div>
              </template>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </template>
  </po-wrapper>
</template>
