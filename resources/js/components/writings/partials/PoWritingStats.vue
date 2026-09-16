<script setup lang="ts">
import { computed, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { loginModalKey, writingKey } from '@/composables/keys'
import { injectStrict } from '@/composables/injectStrict'
import { useAuth } from '@/composables/useAuth'
import { useTypeGuards } from '@/composables/useTypeGuards'
import { useFormatting } from '@/composables/useFormatting'
import { useToggleReaction } from '@/composables/useToggleReaction'

const page = computed(() => usePage())
const { auth, authUser } = useAuth()
const { strNullOrEmpty } = useTypeGuards()
const { readable } = useFormatting()
const { toggleReaction } = useToggleReaction()
const writing = injectStrict(writingKey)
const liked = page.value.props.auth.liked.writings.includes(writing.id)
const shelved = page.value.props.auth.shelved.includes(writing.id)
const likesCount = ref(writing.likes_count)
const shelfCount = ref(writing.shelf_count)
const loginModal = injectStrict(loginModalKey)

const canReactToWriting = computed(
  () => auth() === true && authUser()!.username !== writing.author.username
)

async function like(event: MouseEvent): Promise<void> {
  await toggleReaction({
    event,
    doerSelector: '.do-like',
    canReact: canReactToWriting.value,
    isAuthenticated: auth(),
    onUnauthenticated: () => {
      loginModal.value = true
    },
    postUrl: route('likes.store', ['writing', writing.id]),
    activeClass: 'liked',
    onCount: (count) => {
      likesCount.value = count
    }
  })
}

async function shelf(event: MouseEvent): Promise<void> {
  await toggleReaction({
    event,
    doerSelector: '.do-shelf',
    canReact: canReactToWriting.value,
    isAuthenticated: auth(),
    onUnauthenticated: () => {
      loginModal.value = true
    },
    postUrl: route('shelves.store', writing.slug),
    activeClass: 'shelved',
    onCount: (count) => {
      shelfCount.value = count
    }
  })
}
</script>

<template>
  <div class="d-flex justify-center ga-8 mx-auto text-on-surface-variant text-caption text-center">
    <div
      v-if="!strNullOrEmpty(writing.home_posted_at)"
      class="d-flex flex-column"
      :title="$t('writings.awarded')"
    >
      <div><v-icon icon="fas fa-fan" color="amber-accent-4" /></div>
      <div>:</div>
    </div>

    <div
      class="d-flex flex-column do-like"
      :class="{ liked: liked }"
      :title="$t('main.count-likes', { count: likesCount })"
      @click="like"
    >
      <div><v-icon icon="fas fa-heart" /></div>
      <div>{{ readable(likesCount) }}</div>
    </div>

    <div
      class="d-flex flex-column"
      :title="$t('main.count-comments', { count: writing.comments_count })"
    >
      <div><v-icon icon="fas fa-comment" /></div>
      <div>{{ readable(writing.comments_count) }}</div>
    </div>

    <div class="d-flex flex-column" :title="$t('main.count-views', { count: writing.views })">
      <div><v-icon icon="fas fa-book-reader" /></div>
      <div>{{ readable(writing.views) }}</div>
    </div>

    <div
      class="d-flex flex-column do-shelf"
      :class="{ shelved: shelved }"
      :title="$t('main.count-shelved', { count: shelfCount })"
      @click="shelf"
    >
      <div><v-icon icon="fas fa-bookmark" /></div>
      <div>{{ readable(shelfCount) }}</div>
    </div>

    <div class="d-flex flex-column" :title="$t('main.aura-value', { aura: writing.aura })">
      <div><v-icon icon="fas fa-dove" /></div>
      <div>{{ writing.aura }}</div>
    </div>
  </div>
</template>
