<script setup lang="ts">
import { ref, onMounted, provide } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'
import PoCommentsForm from './PoCommentsForm.vue'
import PoCommentsDropdown from './PoCommentsDropdown.vue'
import { loadingCommentsKey, loginModalKey, replyBoxKey, writingKey } from '@/composables/keys'
import { injectStrict } from '@/composables/injectStrict'
import { useAuth } from '@/composables/useAuth'
import { useTypeGuards } from '@/composables/useTypeGuards'
import { useFormatting } from '@/composables/useFormatting'
import type { Comment, Paginated } from '@/types/models'

const page = usePage()
const { auth } = useAuth()
const { isEmpty } = useTypeGuards()
const { userDisplayName, toLocaleDate, linkify } = useFormatting()
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

function isLiked(commentId: number): boolean {
  return page.props.auth.liked.comments.includes(commentId)
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
      <p class="text-uppercase text-medium-emphasis mb-3">{{ $t('comments.comments') }}</p>

      <template v-for="comment in comments.data" :key="comment.id">
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

          <div class="po-prose text-title-large mb-6" v-html="linkify(comment.message)" />

          <div class="d-flex ga-2">
            <po-reaction-button
              icon="fa-heart"
              :count="comment.likes_count"
              :is-active="isLiked(comment.id)"
              :post-url="route('likes.store', ['comment', comment.id])"
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

          <template v-if="auth() && replyBox === comment.id">
            <div id="" class="reply-box pa-3">
              <po-comments-form
                :form-id="`reply-${comment.id}-form`"
                :reply-to="reply(comment)"
                @comment-posted="loadComments"
              />
            </div>
          </template>
        </div>
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
