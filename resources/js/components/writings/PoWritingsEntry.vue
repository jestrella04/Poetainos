<script setup>
import { ref, provide, computed, inject } from 'vue'
import PoCommentsIndex from '../comments/PoCommentsIndex.vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'

const props = defineProps({
  alone: { type: Boolean, default: true },
  data: { type: Object, required: true },
  likers: Object,
})

const page = computed(() => usePage())
const helper = inject('helper')
const loadingComments = ref(true)
const liked = page.value.props.auth.liked.writings.includes(props.data.id)
const shelved = page.value.props.auth.shelved.includes(props.data.id)
const likesCount = ref(props.data.likes_count)
const shelfCount = ref(props.data.shelf_count)

provide('loadingComments', loadingComments)
provide('writingId', props.data.id)

async function like(event) {
  const doer = event.target.closest('.do-like')

  if (helper.auth()) {
    await axios
      .post(window.route('likes.store', ['writing', props.data.id]))
      .then((response) => {
        likesCount.value = response.data.count

        if ('store' === response.data.method) {
          doer.classList.add('liked')
        } else {
          doer.classList.remove('liked')
        }
      })
      .catch()
      .finally(() => {
        helper.animate(doer.querySelector("i"), "heartBeat")
      })
  }
}

async function shelf(event) {
  const doer = event.target.closest('.do-shelf')

  if (helper.auth()) {
    await axios
      .post(window.route('shelves.store', props.data.slug))
      .then((response) => {
        shelfCount.value = response.data.count

        if ('store' === response.data.method) {
          doer.classList.add('shelved')
        } else {
          doer.classList.remove('shelved')
        }
      })
      .catch()
      .finally(() => {
        helper.animate(doer.querySelector("i"), "heartBeat")
      })
  }
}
</script>

<template>
  <po-wrapper>
    <v-card rounded elevation="2">
      <template v-if="!$helper.isEmpty(data.extra_info) && !$helper.strNullOrEmpty(data.extra_info.cover)">
        <v-img class="align-end text-white" height="200" :src="$helper.storage(data.extra_info.cover)" cover>
          <div class="text-center py-3">
            <po-link :href="$route('users.show', data.author.username)" inertia>
              <po-avatar size="64" color="secondary" :user="data.author" />
            </po-link>
          </div>
        </v-img>
      </template>

      <div v-else class="text-center pt-3">
        <po-link :href="$route('users.show', data.author.username)" inertia>
          <po-avatar size="64" color="secondary" :user="data.author" />
        </po-link>
      </div>

      <v-card-text>
        <div class="text-center mb-3">
          <p class="text-h5 text-uppercase font-weight-bold">
            <po-link v-if="!alone" :href="$route('writings.show', data.slug)" inertia>{{ data.title }}</po-link>
            <span v-else>{{ data.title }}</span>
          </p>
          <p class="text-caption text-uppercase font-weight-light">
            {{
              `${$t('main.by-name', { name: $helper.userDisplayName(data.author) })}
                        ${$helper.relativeDate(data.created_at)}`
            }}
          </p>
        </div>

        <template v-if="alone">
          <blockquote class="mb-3">
            {{ data.text }}
          </blockquote>

          <template v-if="!$helper.isEmpty(data.extra_info) && !$helper.strNullOrEmpty(data.extra_info.link)">
            <po-button variant="text" :href="data.extra_info.link" prepend-icon="fas fa-link" target="_blank"
              rel="nofollow noopener" class="mb-4">

              {{ $helper.cropUrl(data.extra_info.link) }}
            </po-button>
          </template>

          <div v-if="!$helper.isEmpty(data.categories)" class="d-flex mb-2">
            <div class="mr-3">
              <v-icon icon="fas fa-folder-open"></v-icon>
            </div>

            <div class="d-inline-flex ga-1">
              <po-chip v-for="category in data.categories" :key="category.slug" color="secondary" variant="elevated"
                size="small" :href="$route('categories.show', category.slug)" inertia>
                {{ category.name }}
              </po-chip>
            </div>
          </div>

          <div v-if="!$helper.isEmpty(data.tags)" class="d-flex mb-4">
            <div class="mr-3">
              <v-icon icon="fas fa-hashtag"></v-icon>
            </div>

            <div class="d-inline-flex ga-1">
              <po-chip v-for="tag in data.tags" :key="tag.slug" color="secondary" variant="elevated" size="small"
                :href="$route('tags.show', tag.slug)" inertia>
                {{ tag.name }}
              </po-chip>
            </div>
          </div>

          <div v-if="!$helper.isEmpty(likers)">
            <p class="mb-3">{{ $t('main.liked-by') }}</p>

            <div class="d-inline-flex flex-wrap ga-1">
              <div v-for="liker in likers" :key="liker.id">
                <po-button icon :href="$route('users.show', liker.username)" inertia>
                  <po-avatar size="48" color="secondary" :user="liker" />
                </po-button>
              </div>
              <div v-if="likers.length > 5">
                <v-avatar size="48" color="secondary" text="+" />
              </div>
            </div>
          </div>
        </template>

        <template v-else>
          <blockquote>
            {{ $helper.excerpt(data.text) }}
          </blockquote>
        </template>
      </v-card-text>

      <v-divider></v-divider>
      <v-card-actions>
        <div class="d-flex justify-center ga-8 mx-auto text-medium-emphasis text-caption text-center">
          <div v-if="!$helper.strNullOrEmpty(data.home_posted_at)" class="d-flex flex-column">
            <div><v-icon icon="fas fa-fan" color="amber-accent-4"></v-icon></div>
            <div>:</div>
          </div>

          <div class="d-flex flex-column do-like" :class="{ 'liked': liked }" @click="like">
            <div><v-icon icon="fas fa-heart"></v-icon></div>
            <div>{{ $helper.readable(likesCount) }}</div>
          </div>

          <div class="d-flex flex-column">
            <div><v-icon icon="fas fa-comment"></v-icon></div>
            <div>{{ $helper.readable(data.comments_count) }}</div>
          </div>

          <div class="d-flex flex-column">
            <div><v-icon icon="fas fa-book-reader"></v-icon></div>
            <div>{{ $helper.readable(data.views) }}</div>
          </div>

          <div class="d-flex flex-column do-shelf" :class="{ 'shelved': shelved }" @click="shelf">
            <div><v-icon icon="fas fa-bookmark"></v-icon></div>
            <div>{{ $helper.readable(shelfCount) }}</div>
          </div>

          <div class="d-flex flex-column">
            <div><v-icon icon="fas fa-dove"></v-icon></div>
            <div>{{ data.aura }}</div>
          </div>
        </div>
      </v-card-actions>
    </v-card>

    <template v-if="alone">
      <v-skeleton-loader v-if="loadingComments" :elevation="3" type="list-item-avatar" class="mb-2"></v-skeleton-loader>
      <v-skeleton-loader v-if="loadingComments" :elevation="3" type="list-item-avatar" class="mb-2"></v-skeleton-loader>
      <v-skeleton-loader v-if="loadingComments" :elevation="3" type="list-item-avatar" class="mb-2"></v-skeleton-loader>

      <po-comments-index />
    </template>
  </po-wrapper>
</template>