<script setup>

import { ref, onMounted, inject, computed, provide } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'
import PoCommentsForm from './PoCommentsForm.vue'
import PoCommentsDropdown from './PoCommentsDropdown.vue'

const page = computed(() => usePage())
const helper = inject('helper')
const comments = ref({})
const loadingComments = inject('loadingComments', true)
const writing = inject('writing')
const loginModal = inject('loginModal', false)
const replyBox = ref(0)

provide('replyBox', replyBox)

onMounted(() => {
  loadComments()
})

async function loadComments() {
  await axios.
    get(window.route('comments.index', writing.id))
    .then(response => {
      comments.value = response.data
      loadingComments.value = false
    })
}

async function like(event, id) {
  const doer = event.target.closest('.do-like')

  if (helper.auth()) {
    await axios
      .post(window.route('likes.store', ['comment', id]))
      .then((response) => {
        doer.querySelector('span.count').textContent = helper.readable(response.data.count)

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
  } else {
    loginModal.value = true
  }
}

function toggleReply(commentId) {
  if (helper.auth()) {
    if (replyBox.value === commentId) {
      replyBox.value = 0
    } else {
      replyBox.value = commentId
    }
  } else {
    loginModal.value = true
  }
}

function reply(comment) {
  let initialText = ['@' + comment.author.username]
  const mentions = comment.message.matchAll(/(^|\W)@\b([-a-zA-Z0-9._]{3,25})\b/g)

  for (const mention of mentions) {
    initialText.push(mention[0].trim())
  }

  return [... new Set(initialText)].join(' ') + ' '
}
</script>

<template>
  <po-wrapper class="my-5">
    <div class="mb-5">
      <po-inline-login v-if="!$helper.auth()" :message="$t('accounts.login-before-comment')" />
      <po-comments-form v-else form-id="comment-form" @comment-posted="loadComments" />
    </div>

    <template v-if="!$helper.isEmpty(comments.data)">
      <p class="text-h6 mb-3">{{ $t('comments.comments') }}</p>

      <template v-for="comment in comments.data" :key="comment.id">
        <v-card class="mb-2 pos-relative smaller">
          <v-card-text class="d-flex pb-1 ga-3">
            <div class="flex-grow-1" v-html="$helper.linkify(comment.message)"></div>
            <div>
              <po-comments-dropdown :comment="comment"></po-comments-dropdown>
            </div>
          </v-card-text>

          <v-card-actions>
            <div class="w-100 d-flex flex-wrap ga-3">
              <div class="flex-grow-1 d-inline-flex ga-3">
                <po-avatar size="44" color="secondary" :user="comment.author" />

                <div class="">
                  <p class="text-caption mb-0">{{ $helper.userDisplayName(comment.author) }}</p>
                  <p class="text-caption text-medium-emphasis">{{ $helper.relativeDate(comment.created_at) }}</p>
                </div>
              </div>

              <div class="d-flex align-end justify-end">
                <po-button variant="tonal" size="small" class="do-like"
                  :class="{ 'liked': page.props.auth.liked.comments.includes(comment.id) }"
                  @click="(event) => { like(event, comment.id) }">
                  <v-icon class="me-2" icon="fas fa-heart"></v-icon>
                  <span class="count">{{ helper.readable(comment.likes_count) }}</span>
                </po-button>

                <po-button variant="tonal" size="small" @click.prevent="toggleReply(comment.id)">
                  <v-icon class="me-2" icon="fa fa-reply"></v-icon>
                  <span>{{ $t('main.reply') }}</span>
                </po-button>
              </div>
            </div>
          </v-card-actions>

          <template v-if="$helper.auth() && replyBox === comment.id">
            <div id="" class="reply-box pa-3">
              <po-comments-form :form-id="`reply-${comment.id}-form`" :reply-to="reply(comment)"
                @comment-posted="loadComments" />
            </div>
          </template>
        </v-card>
      </template>
    </template>

    <template v-else>
      <po-msg-block :msg-title="$t('comments.comments-empty')" :msg-body="$t('comments.be-first-ask')"
        icon="fas fa-comment" class="py-10" />
    </template>
  </po-wrapper>
</template>