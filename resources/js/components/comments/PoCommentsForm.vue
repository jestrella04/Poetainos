<script setup>
import { inject } from 'vue'

const writingId = inject('writingId')
</script>

<template>
  <v-form :action="$route('comments.store')" :data="writingId" @submit.prevent>
    <v-textarea :label="$t('comments.comment')" :placeholder="$t('comments.comment-mention')" rows="2" auto-grow clearable
      persistent-placeholder></v-textarea>
    <po-button type="submit" block class="mt-1">{{ $t('comments.post-comment') }}</po-button>
  </v-form>
</template>