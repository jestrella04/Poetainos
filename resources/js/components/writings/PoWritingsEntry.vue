<script setup lang="ts">
import { computed, ref, provide } from 'vue'
import { usePage } from '@inertiajs/vue3'
import PoCommentsIndex from '../comments/PoCommentsIndex.vue'
import PoWritingExtras from './partials/PoWritingExtras.vue'
import PoWritingDropdown from './partials/PoWritingDropdown.vue'
import { loadingCommentsKey, writingKey } from '@/composables/keys'
import { useAuth } from '@/composables/useAuth'
import { useTypeGuards } from '@/composables/useTypeGuards'
import { useFormatting } from '@/composables/useFormatting'
import type { UserLike, Writing } from '@/types/models'

const props = withDefaults(
  defineProps<{
    alone?: boolean
    hero?: boolean
    hideAuthor?: boolean
    data: Writing
    likers?: UserLike[]
  }>(),
  {
    alone: true,
    hero: false,
    hideAuthor: false
  }
)

const { authUser } = useAuth()
const { isEmpty, strNullOrEmpty } = useTypeGuards()
const { storage, toLocaleDate, userDisplayName, excerpt, readable } = useFormatting()
const loadingComments = ref(true)
const page = usePage()
const hasCover = computed(
  () => !isEmpty(props.data.extra_info) && !strNullOrEmpty(props.data.extra_info?.cover)
)
const isLiked = computed(() => page.props.auth.liked.writings.includes(props.data.id))
const isShelved = computed(() => page.props.auth.shelved.includes(props.data.id))
const canReactToWriting = computed(() => authUser()?.username !== props.data.author.username)
const hasSideCover = computed(() => hasCover.value && !props.alone)
const isProminent = computed(() => props.alone || props.hero)
const listSpacingClass = computed(() => (props.hero ? 'pb-16' : 'py-12 border-b'))
// The hero writing may also be listed below it, so its DOM id needs its own prefix to stay unique
const domId = computed(() =>
  props.hero ? `hero-writing-${props.data.id}` : `writing-${props.data.id}`
)

provide(loadingCommentsKey, loadingComments)
provide(writingKey, props.data)
</script>

<template>
  <po-wrapper>
    <article :id="domId" :class="{ [listSpacingClass]: !alone }" class="pe-md-8">
      <v-img
        v-if="hasCover && alone"
        height="320"
        :src="storage(data.extra_info?.cover ?? '')"
        alt=""
        class="mb-6"
        rounded
        cover
      />

      <v-row>
        <v-col v-if="hasSideCover" cols="12" md="3" order="1" order-md="2">
          <v-img height="200" :src="storage(data.extra_info?.cover ?? '')" alt="" rounded cover />
        </v-col>

        <v-col cols="12" :md="hasSideCover ? 9 : 12" order="2" order-md="1">
          <div class="d-flex ga-4">
            <span class="text-medium-emphasis text-uppercase text-eyebrow ma-0">
              {{ toLocaleDate(data.created_at) }}
            </span>

            <template v-if="!strNullOrEmpty(data.home_posted_at)">
              <v-chip color="primary" variant="tonal" size="small">
                <v-icon icon="fas fa-fan" class="mr-2" />
                <span class="text-uppercase" :title="$t('writings.awarded')">
                  {{ $t('main.awarded') }}
                </span>
              </v-chip>
            </template>
          </div>

          <div class="position-relative">
            <p
              :id="`${domId}-title`"
              :class="isProminent ? 'text-display-large' : 'text-display-small'"
              class="po-prose ma-0 mb-2"
            >
              <po-link
                v-if="!alone"
                :href="route('writings.show', data.slug)"
                class="stretched"
                inertia
              >
                {{ data.title }}
              </po-link>
              <template v-else>{{ data.title }}</template>
            </p>

            <div class="d-flex align-center flex-wrap mb-4 ga-6">
              <po-link
                v-if="!hideAuthor"
                :id="`${domId}-author`"
                :href="route('users.show', data.author.username)"
                inertia
              >
                <po-avatar-award
                  :user="data.author"
                  avatar-size="28"
                  avatar-color="primary"
                  class="me-1"
                />
                {{ userDisplayName(data.author) }}
              </po-link>

              <div class="d-inline-flex align-center ga-3 text-medium-emphasis">
                <span>
                  {{ $t('main.count-views', { count: readable(data.views) }, data.views) }}
                </span>

                <span>
                  {{
                    $t(
                      'main.count-comments',
                      { count: readable(data.comments_count) },
                      data.comments_count
                    )
                  }}
                </span>
              </div>
            </div>

            <p
              :class="[
                isProminent ? 'text-headline-small' : 'text-title-large',
                { 'text-pre-wrap': alone }
              ]"
              class="po-prose mb-6"
            >
              {{ alone ? data.text : excerpt(data.text) }}
            </p>
          </div>

          <po-writing-extras v-if="alone" :data="data" :likers="likers" />

          <div class="d-flex ga-2">
            <po-reaction-button
              :id="`${domId}-like`"
              icon="fa-heart"
              :count="data.likes_count"
              :is-active="isLiked"
              :post-url="route('likes.store', ['writing', data.id])"
              :can-react="canReactToWriting"
              :activate-title="$t('writings.like-writing')"
              :deactivate-title="$t('writings.unlike-writing')"
            />

            <po-reaction-button
              :id="`${domId}-shelve`"
              icon="fa-bookmark"
              :count="data.shelf_count"
              :is-active="isShelved"
              :post-url="route('shelves.store', data.slug)"
              :can-react="canReactToWriting"
              :activate-title="$t('writings.shelve-writing')"
              :deactivate-title="$t('writings.unshelve-writing')"
            />

            <po-writing-dropdown :id-prefix="domId" />
          </div>
        </v-col>
      </v-row>
    </article>

    <template v-if="alone">
      <template v-if="loadingComments">
        <v-skeleton-loader
          v-for="n in 3"
          :key="n"
          :elevation="2"
          type="list-item-avatar"
          class="mb-2"
        />
      </template>

      <po-comments-index />
    </template>
  </po-wrapper>
</template>
