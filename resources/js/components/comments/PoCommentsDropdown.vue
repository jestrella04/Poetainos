<script setup lang="ts">
import { provide, ref } from 'vue'
import PoCommentsDelete from './PoCommentsDelete.vue'
import { blockerKey, complainerKey, isDeleteKey } from '@/composables/keys'
import { useAuth } from '@/composables/useAuth'
import type { Comment } from '@/types/models'

defineProps<{
  comment: Comment
}>()

const { isAuthenticated, authUser, canEdit } = useAuth()
const complainer = ref(false)
const blocker = ref(false)
const isDelete = ref(false)

provide(complainerKey, complainer)
provide(blockerKey, blocker)
provide(isDeleteKey, isDelete)
</script>

<template>
  <po-complainer v-model="complainer" comp-type="comments" :comp-id="comment.id" />
  <po-blocker v-model="blocker" :user="comment.author" />
  <po-comments-delete v-model="isDelete" :comment="comment" />

  <v-menu open-on-hover>
    <template v-slot:activator="{ props }">
      <v-btn
        v-bind="props"
        prepend-icon="fas fa-angle-down"
        color="primary"
        variant="tonal"
        :aria-label="$t('main.more-actions')"
        >{{ $t('main.more') }}</v-btn
      >
    </template>

    <v-list>
      <template v-if="canEdit(comment.author)">
        <po-list-item prepend-icon="fas fa-eraser" @click.prevent="isDelete = true">
          <span>{{ $t('comments.delete-comment') }}</span>
        </po-list-item>
        <v-divider class="my-0" />
      </template>

      <po-list-item prepend-icon="fas fa-flag" @click.prevent="complainer = true">
        <span>{{ $t('complaints.report-comment') }}</span>
      </po-list-item>

      <template v-if="isAuthenticated() && authUser()!.username !== comment.author.username">
        <v-divider class="my-0" />
        <po-list-item prepend-icon="fas fa-ban" @click.prevent="blocker = true">
          <span>{{ $t('main.block-user') }}</span>
        </po-list-item>
      </template>
    </v-list>
  </v-menu>
</template>
