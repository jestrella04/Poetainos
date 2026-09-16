<script setup lang="ts">
import { ref, onMounted, computed, provide } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'
import PoCommentsForm from './PoCommentsForm.vue'
import PoCommentsDropdown from './PoCommentsDropdown.vue'
import { loadingCommentsKey, loginModalKey, replyBoxKey, writingKey } from '@/composables/keys'
import { injectStrict } from '@/composables/injectStrict'
import { useAuth } from '@/composables/useAuth'
import { useTypeGuards } from '@/composables/useTypeGuards'
import { useFormatting } from '@/composables/useFormatting'
import { useToggleReaction } from '@/composables/useToggleReaction'
import type { Comment, Paginated } from '@/types/models'

const page = computed(() => usePage())
const { auth } = useAuth()
const { isEmpty } = useTypeGuards()
const { userDisplayName, toLocaleDate, linkify, readable } = useFormatting()
const { toggleReaction } = useToggleReaction()
const comments = ref<Partial<Paginated<Comment>>>({})
const loadingComments = injectStrict(loadingCommentsKey)
const writing = injectStrict(writingKey)
const loginModal = injectStrict(loginModalKey)
const replyBox = ref(0)

provide(replyBoxKey, replyBox)

onMounted(() => {
  void loadComments()
})

async function loadComments() {
  await axios.get<Paginated<Comment>>(route('comments.index', writing.id)).then((response) => {
    comments.value = response.data
    loadingComments.value = false
  })
}

async function like(event: MouseEvent, id: number): Promise<void> {
  const isAuthenticated = auth()

  await toggleReaction({
    event,
    doerSelector: '.do-like',
    canReact: isAuthenticated,
    isAuthenticated,
    onUnauthenticated: () => {
      loginModal.value = true
    },
    postUrl: route('likes.store', ['comment', id]),
    activeClass: 'liked',
    onCount: (count) => {
      const countEl = (event.target as HTMLElement)
        .closest<HTMLElement>('.do-like')
        ?.querySelector<HTMLElement>('span.count')

      if (countEl !== null && countEl !== undefined) {
        countEl.textContent = readable(count)
      }
    }
  })
}

function toggleReply(commentId: number) {
  if (auth()) {
    if (replyBox.value === commentId) {
      replyBox.value = 0
    } else {
      replyBox.value = commentId
    }
  } else {
    loginModal.value = true
  }
}

function reply(comment: Comment) {
  const initialText = ['@' + comment.author.username]
  const mentions = comment.message.matchAll(/(^|\W)@\b([-a-zA-Z0-9._]{3,25})\b/g)

  for (const mention of mentions) {
    initialText.push(mention[0].trim())
  }

  return [...new Set(initialText)].join(' ') + ' '
}
</script>

<template>
  <po-wrapper class="my-5">
    <div class="mb-5">
      <po-inline-login v-if="!auth()" :message="$t('accounts.login-before-comment')" />
      <po-comments-form v-else form-id="comment-form" @comment-posted="loadComments" />
    </div>

    <template v-if="!isEmpty(comments.data)">
      <p class="text-h6 mb-3">{{ $t('comments.comments') }}</p>

      <template v-for="comment in comments.data" :key="comment.id">
        <v-card class="mb-2 position-relative">
          <v-card-text class="pb-1">
            <div class="d-flex ga-3">
              <div class="flex-grow-1 d-inline-flex ga-3">
                <po-avatar size="40" color="secondary" :user="comment.author" />

                <div class="">
                  <p class="text-caption mb-0">{{ userDisplayName(comment.author) }}</p>
                  <p class="text-caption mb-2 text-eyebrow text-on-surface-variant">
                    {{ toLocaleDate(comment.created_at) }}
                  </p>
                </div>
              </div>

              <div>
                <po-comments-dropdown :comment="comment" />
              </div>
            </div>

            <div class="po-prose" v-html="linkify(comment.message)" />
          </v-card-text>

          <v-card-actions class="justify-end">
            <po-button
              variant="tonal"
              size="small"
              class="do-like"
              :class="{ liked: page.props.auth.liked.comments.includes(comment.id) }"
              @click="
                (event: MouseEvent) => {
                  like(event, comment.id)
                }
              "
            >
              <v-icon class="me-2" icon="fas fa-heart" />
              <span class="count">{{ readable(comment.likes_count) }}</span>
            </po-button>

            <po-button variant="tonal" size="small" @click.prevent="toggleReply(comment.id)">
              <v-icon class="me-2" icon="fa fa-reply" />
              <span>{{ $t('main.reply') }}</span>
            </po-button>
          </v-card-actions>

          <template v-if="auth() && replyBox === comment.id">
            <div id="" class="reply-box pa-3">
              <po-comments-form
                :form-id="`reply-${comment.id}-form`"
                :reply-to="reply(comment)"
                @comment-posted="loadComments"
              />
            </div>
          </template>
        </v-card>
      </template>
    </template>

    <template v-else>
      <po-msg-block
        :msg-title="$t('comments.comments-empty')"
        :msg-body="$t('comments.be-first-ask')"
        icon="fas fa-comment"
        class="py-10"
      />
    </template>
  </po-wrapper>
</template>
