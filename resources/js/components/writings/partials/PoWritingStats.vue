<script setup>
import { computed, inject, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'

const page = computed(() => usePage())
const helper = inject('helper')
const writing = inject('writing')
const liked = page.value.props.auth.liked.writings.includes(writing.id)
const shelved = page.value.props.auth.shelved.includes(writing.id)
const likesCount = ref(writing.likes_count)
const shelfCount = ref(writing.shelf_count)

async function like(event) {
  const doer = event.target.closest('.do-like')

  if (helper.auth()) {
    await axios
      .post(window.route('likes.store', ['writing', writing.id]))
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
      .post(window.route('shelves.store', writing.slug))
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
  <div class="d-flex justify-center ga-8 mx-auto text-medium-emphasis text-caption text-center">
    <div v-if="!$helper.strNullOrEmpty(writing.home_posted_at)" class="d-flex flex-column"
      :title="$t('writings.awarded')">
      <div><v-icon icon="fas fa-fan" color="amber-accent-4"></v-icon></div>
      <div>:</div>
    </div>

    <div class="d-flex flex-column do-like" :class="{ 'liked': liked }"
      :title="$t('main.count-likes', { count: likesCount })" @click="like">
      <div><v-icon icon="fas fa-heart"></v-icon></div>
      <div>{{ $helper.readable(likesCount) }}</div>
    </div>

    <div class="d-flex flex-column" :title="$t('main.count-comments', { count: writing.comments_count })">
      <div><v-icon icon="fas fa-comment"></v-icon></div>
      <div>{{ $helper.readable(writing.comments_count) }}</div>
    </div>

    <div class="d-flex flex-column" :title="$t('main.count-views', { count: writing.views })">
      <div><v-icon icon="fas fa-book-reader"></v-icon></div>
      <div>{{ $helper.readable(writing.views) }}</div>
    </div>

    <div class="d-flex flex-column do-shelf" :class="{ 'shelved': shelved }"
      :title="$t('main.count-shelved', { count: shelfCount })" @click="shelf">
      <div><v-icon icon="fas fa-bookmark"></v-icon></div>
      <div>{{ $helper.readable(shelfCount) }}</div>
    </div>

    <div class="d-flex flex-column" :title="$t('main.aura-value', { aura: writing.aura })">
      <div><v-icon icon="fas fa-dove"></v-icon></div>
      <div>{{ writing.aura }}</div>
    </div>
  </div>
</template>