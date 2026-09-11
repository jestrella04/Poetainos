<script setup lang="ts">
import { computed, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'
import { helperKey, loginModalKey, writingKey } from '@/composables/keys'
import { injectStrict } from '@/composables/injectStrict'

const page = computed(() => usePage())
const helper = injectStrict(helperKey)
const writing = injectStrict(writingKey)
const liked = page.value.props.auth.liked.writings.includes(writing.id)
const shelved = page.value.props.auth.shelved.includes(writing.id)
const likesCount = ref(writing.likes_count)
const shelfCount = ref(writing.shelf_count)
const loginModal = injectStrict(loginModalKey)

async function like(event: MouseEvent) {
  const doer = (event.target as HTMLElement).closest<HTMLElement>('.do-like')

  if (!doer) {
    return
  }

  if (helper.auth() && helper.authUser()!.username !== writing.author.username) {
    await axios
      .post<{ count: number; method: 'store' | 'destroy' }>(
        route('likes.store', ['writing', writing.id])
      )
      .then((response) => {
        likesCount.value = response.data.count

        if ('store' === response.data.method) {
          doer.classList.add('liked')
        } else if ('destroy' === response.data.method) {
          doer.classList.remove('liked')
        }
      })
      .catch(() => undefined)
      .finally(() => {
        const icon = doer.querySelector<HTMLElement>('i')

        if (icon) {
          void helper.animate(icon, 'heartBeat')
        }
      })
  } else if (!helper.auth()) {
    loginModal.value = true
  }
}

async function shelf(event: MouseEvent) {
  const doer = (event.target as HTMLElement).closest<HTMLElement>('.do-shelf')

  if (!doer) {
    return
  }

  if (helper.auth() && helper.authUser()!.username !== writing.author.username) {
    await axios
      .post<{ count: number; method: 'store' | 'destroy' }>(route('shelves.store', writing.slug))
      .then((response) => {
        shelfCount.value = response.data.count

        if ('store' === response.data.method) {
          doer.classList.add('shelved')
        } else if ('destroy' === response.data.method) {
          doer.classList.remove('shelved')
        }
      })
      .catch(() => undefined)
      .finally(() => {
        const icon = doer.querySelector<HTMLElement>('i')

        if (icon) {
          void helper.animate(icon, 'heartBeat')
        }
      })
  } else if (!helper.auth()) {
    loginModal.value = true
  }
}
</script>

<template>
  <div class="d-flex justify-center ga-8 mx-auto text-medium-emphasis text-caption text-center">
    <div
      v-if="!$helper.strNullOrEmpty(writing.home_posted_at)"
      class="d-flex flex-column"
      :title="$t('writings.awarded')"
    >
      <div><v-icon icon="fas fa-fan" color="amber-accent-4"></v-icon></div>
      <div>:</div>
    </div>

    <div
      class="d-flex flex-column do-like"
      :class="{ liked: liked }"
      :title="$t('main.count-likes', { count: likesCount })"
      @click="like"
    >
      <div><v-icon icon="fas fa-heart"></v-icon></div>
      <div>{{ $helper.readable(likesCount) }}</div>
    </div>

    <div
      class="d-flex flex-column"
      :title="$t('main.count-comments', { count: writing.comments_count })"
    >
      <div><v-icon icon="fas fa-comment"></v-icon></div>
      <div>{{ $helper.readable(writing.comments_count) }}</div>
    </div>

    <div class="d-flex flex-column" :title="$t('main.count-views', { count: writing.views })">
      <div><v-icon icon="fas fa-book-reader"></v-icon></div>
      <div>{{ $helper.readable(writing.views) }}</div>
    </div>

    <div
      class="d-flex flex-column do-shelf"
      :class="{ shelved: shelved }"
      :title="$t('main.count-shelved', { count: shelfCount })"
      @click="shelf"
    >
      <div><v-icon icon="fas fa-bookmark"></v-icon></div>
      <div>{{ $helper.readable(shelfCount) }}</div>
    </div>

    <div class="d-flex flex-column" :title="$t('main.aura-value', { aura: writing.aura })">
      <div><v-icon icon="fas fa-dove"></v-icon></div>
      <div>{{ writing.aura }}</div>
    </div>
  </div>
</template>
