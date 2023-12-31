<script setup>
import axios from 'axios'
import { ref, onMounted, inject } from 'vue'
import PoCommentsForm from './PoCommentsForm.vue'

const comments = ref({})
const loadingComments = inject('loadingComments', true)
const writingId = inject('writingId')

onMounted(() => {
  axios.get(window.route('comments.index', writingId)).then(response => {
    comments.value = response.data
    loadingComments.value = false
  })
})
</script>

<template>
  <div class="mb-5">
    <div v-if="!loadingComments" class="mb-5">
      <po-inline-login v-if="!$helper.auth()" />
      <po-comments-form v-else />
    </div>

    <template v-if="!$helper.isEmpty(comments.data)">
      <p class="text-h6">{{ $t('comments.comments') }}</p>

      <template v-for="comment in comments.data" :key="comment.id">
        <v-card class="mb-2">
          <v-card-text class="pb-0">
            <div v-html="$helper.linkify(comment.message)"></div>
          </v-card-text>

          <v-card-actions>
            <div class="w-100 d-flex flex-wrap ga-3">
              <div class="flex-grow-1 d-inline-flex ga-3">
                <po-avatar size="36" color="secondary" :user="comment.author" />

                <div class="">
                  <p class="text-caption mb-0">{{ $helper.userDisplayName(comment.author) }}</p>
                  <p class="text-caption text-medium-emphasis">{{ $helper.toLocaleDate(comment.created_at) }}</p>
                </div>
              </div>

              <div class="d-flex align-end justify-end">
                <po-button variant="tonal" size="small">
                  <v-icon class="me-2" icon="fas fa-heart"></v-icon>
                  <span>{{ comment.likes_count }}</span>
                </po-button>

                <po-button variant="tonal" size="small">
                  <v-icon class="me-2" icon="fa fa-reply"></v-icon>
                  <span>{{ $t('main.reply') }}</span>
                </po-button>

                <po-button variant="tonal" size="small" max-width="2">
                  <v-icon icon="fa fa-ellipsis-v"></v-icon>
                </po-button>
              </div>
            </div>

          </v-card-actions>
        </v-card>
      </template>
    </template>

    <template v-else>
      <po-msg-block :msg-title="$t('comments.comments-empty')" :msg-body="$t('comments.be-first-ask')"
        icon="fas fa-comment" class="py-10" />
    </template>
  </div>
</template>