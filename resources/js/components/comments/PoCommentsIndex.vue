<script setup lang="ts">
import { ref, onMounted, provide } from 'vue'
import PoCommentsForm from './PoCommentsForm.vue'
import PoCommentsDropdown from './PoCommentsDropdown.vue'
import { loadingCommentsKey, loginModalKey, replyBoxKey, writingKey } from '@/composables/keys'
import { injectStrict } from '@/composables/injectStrict'
import { useAuth } from '@/composables/useAuth'
import { useTypeGuards } from '@/composables/useTypeGuards'
import { useFormatting } from '@/composables/useFormatting'
import { usePaginatedList } from '@/composables/usePaginatedList'
import { mentionedUsernames } from '@/composables/validationRules'
import type { Comment } from '@/types/models'

const { isAuthenticated } = useAuth()
const { isEmpty, isBlank } = useTypeGuards()
const { userDisplayName, toLocaleDate, linkify } = useFormatting()
const { items: comments, nextPageUrl, loadFirstPage, loadMore } = usePaginatedList<Comment>()
const hasLoadError = ref(false)
const loadingComments = injectStrict(loadingCommentsKey)
const writing = injectStrict(writingKey)
const loginModal = injectStrict(loginModalKey)
const replyBox = ref(0)

provide(replyBoxKey, replyBox)

onMounted(() => {
  void loadComments()
})

async function loadComments(): Promise<void> {
  hasLoadError.value = (await loadFirstPage(route('comments.index', writing.slug))) === false
  loadingComments.value = false
}

function toggleReply(commentId: number) {
  if (isAuthenticated()) {
    if (replyBox.value === commentId) {
      replyBox.value = 0
    } else {
      replyBox.value = commentId
    }
  } else {
    loginModal.value = true
  }
}

function reply(comment: Comment): string {
  const usernames = new Set([comment.author.username, ...mentionedUsernames(comment.message)])

  return [...usernames].map((username) => `@${username}`).join(' ') + ' '
}
</script>

<template>
  <po-wrapper class="my-5">
    <div class="mb-5">
      <po-inline-login v-if="!isAuthenticated()" :message="$t('accounts.login-before-comment')" />
      <po-comments-form v-else form-id="comment-form" @comment-posted="loadComments" />
    </div>

    <p v-if="hasLoadError" class="text-error mb-5">{{ $t('main.error-try-again') }}</p>

    <template v-if="!isEmpty(comments)">
      <p class="text-uppercase text-medium-emphasis mb-3">{{ $t('comments.comments') }}</p>

      <template v-for="comment in comments" :key="comment.id">
        <div class="py-12 border-b">
          <div class="d-flex align-center flex-wrap mb-4 ga-6">
            <po-link :href="route('users.show', comment.author.username)" inertia>
              <po-avatar-award
                :user="comment.author"
                avatar-size="28"
                avatar-color="primary"
                class="me-1"
              />
              {{ userDisplayName(comment.author) }}
            </po-link>

            <span class="text-medium-emphasis">{{ toLocaleDate(comment.created_at) }}</span>
          </div>

          <div
            :id="`comment-${comment.id}-message`"
            class="po-prose text-title-large mb-6"
            v-html="linkify(comment.message)"
          />

          <div class="d-flex ga-2">
            <po-reaction-button
              icon="fa-heart"
              :count="comment.likes_count"
              :is-active="comment.is_liked === true"
              :post-url="route('likes.toggle', ['comment', comment.id])"
              :can-react="true"
              :activate-title="$t('comments.like-comment')"
              :deactivate-title="$t('comments.unlike-comment')"
            />

            <v-hover v-slot="{ isHovering, props: hoverProps }">
              <po-button
                v-bind="hoverProps"
                color="primary"
                variant="tonal"
                :title="$t('comments.reply-comment')"
                :prepend-icon="`${isHovering === true ? 'fas' : 'far'} fa-comment`"
                @click.prevent="toggleReply(comment.id)"
              >
                {{ $t('main.reply') }}
              </po-button>
            </v-hover>

            <po-comments-dropdown :comment="comment" />
          </div>

          <template v-if="isAuthenticated() && replyBox === comment.id">
            <div class="reply-box pa-3">
              <po-comments-form
                :form-id="`reply-${comment.id}-form`"
                :reply-to="reply(comment)"
                @comment-posted="loadComments"
              />
            </div>
          </template>
        </div>
      </template>

      <po-infinite-scroll v-if="!isBlank(nextPageUrl)" @load="loadMore" />
    </template>

    <template v-else-if="!hasLoadError">
      <po-msg-block
        :msg-title="$t('comments.comments-empty')"
        :msg-body="$t('comments.be-first-ask')"
        icon="fas fa-comment"
        class="py-10"
      />
    </template>
  </po-wrapper>
</template>
