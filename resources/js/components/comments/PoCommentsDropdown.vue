<script setup>
import { provide, ref } from 'vue'
import PoCommentsDelete from './PoCommentsDelete.vue';

defineProps({
  comment: { type: Object, required: true }
})

const complainer = ref(false)
const blocker = ref(false)
const isDelete = ref(false)

provide('complainer', complainer)
provide('blocker', blocker)
provide('isDelete', isDelete)

</script>

<template>
  <po-complainer v-model="complainer" comp-type="comments" :comp-id="comment.id"></po-complainer>
  <po-blocker v-model="blocker" :user="comment.author"></po-blocker>
  <po-comments-delete v-model="isDelete" :comment="comment"></po-comments-delete>

  <v-menu>
    <template v-slot:activator="{ props }">
      <v-btn v-bind="props" icon="fas fa-ellipsis-vertical" color="secondary" size="x-small" variant="tonal"
        :aria-label="$t('main.more-actions')">
      </v-btn>
    </template>

    <v-list>
      <template v-if="$helper.canEdit(comment.author)">
        <po-list-item prepend-icon="fas fa-eraser" @click.prevent="isDelete = true">
          <span>{{ $t('comments.delete-comment') }}</span>
        </po-list-item>
        <v-divider class="my-0"></v-divider>
      </template>

      <po-list-item prepend-icon="fas fa-flag" @click.prevent="complainer = true">
        <span>{{ $t('complaints.report-comment') }}</span>
      </po-list-item>

      <template v-if="$helper.auth() && $helper.authUser().username !== comment.author.username">
        <v-divider class="my-0"></v-divider>
        <po-list-item prepend-icon="fas fa-ban" @click.prevent="blocker = true">
          <span>{{ $t('main.block-user') }}</span>
        </po-list-item>
      </template>
    </v-list>
  </v-menu>
</template>