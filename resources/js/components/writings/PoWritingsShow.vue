<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import PoWritingsEntry from './PoWritingsEntry.vue'
import { useTypeGuards } from '@/composables/useTypeGuards'
import { useFormatting } from '@/composables/useFormatting'
import type { InertiaPageProps } from '@/types/inertia'
import type { UserLike, Writing } from '@/types/models'

interface WritingsShowProps {
  isAuthorBlocked: boolean
  writing: Writing
  likers: UserLike[]
  related: {
    from_author: Writing[]
    from_category: Writing[]
  }
}

const { isEmpty } = useTypeGuards()
const { userDisplayName, relativeDate } = useFormatting()
const page = usePage<InertiaPageProps<WritingsShowProps>>()
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
          <po-unblocker :user="page.props.writing.author" />
        </po-msg-block>
      </div>
    </template>
    <template v-else>
      <v-row>
        <v-col cols="12" md="8">
          <po-writings-entry :data="page.props.writing" :likers="page.props.likers" />
        </v-col>

        <v-col cols="12" md="4">
          <template v-if="!isEmpty(page.props.related.from_author)">
            <p class="text-uppercase text-medium-emphasis text-primary text-eyebrow">
              {{ $t('main.more-from-author') }}
            </p>

            <template v-for="writing in page.props.related.from_author" :key="writing.id">
              <div class="position-relative">
                <po-link
                  :href="route('writings.show', writing.slug)"
                  class="text-title-large po-prose stretched"
                  inertia
                >
                  {{ writing.title }}
                </po-link>

                <div class="d-inline-flex ga-3">
                  <span>
                    {{ $t('main.by-name', { name: userDisplayName(page.props.writing.author) }) }}
                  </span>
                  <span>{{ relativeDate(writing.created_at) }}</span>
                </div>
              </div>
              <v-divider class="my-4" />
            </template>
          </template>

          <template v-if="!isEmpty(page.props.related.from_category)">
            <p class="text-uppercase text-medium-emphasis text-primary text-eyebrow mt-12">
              {{ $t('main.related-writings') }}
            </p>

            <template v-for="writing in page.props.related.from_category" :key="writing.id">
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
          </template>
        </v-col>
      </v-row>
    </template>
  </po-wrapper>
</template>
