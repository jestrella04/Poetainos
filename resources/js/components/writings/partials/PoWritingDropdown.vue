<script setup>
import { provide } from 'vue';
import { ref, inject } from 'vue'

const writing = inject('writing')
const sharer = ref(false)
const complainer = ref(false)

provide('complainer', complainer)

function share() {
  if (navigator.share) {
    navigator.share({
      title: writing.title,
      url: window.route('writings.show', [writing.slug]),
    });
  } else {
    sharer.value = true
  }
}
</script>

<template>
  <po-sharer v-model="sharer" :link-title="writing.title" :link-url="$route('writings.show', [writing.slug])"></po-sharer>
  <po-complainer v-model="complainer" comp-type="writings" :comp-id="writing.id"></po-complainer>

  <v-menu>
    <template v-slot:activator="{ props }">
      <v-btn v-bind="props" icon="fas fa-ellipsis-vertical" color="secondary" size="x-small" variant="tonal"
        style="position: absolute; top: 1rem; right: 1rem; z-index: 999;"></v-btn>
    </template>

    <v-list>
      <po-list-item prepend-icon="fas fa-share-nodes" @click="share">
        <span>{{ $t('main.share-writing') }}</span>
      </po-list-item>
      <v-divider class="my-0"></v-divider>

      <po-list-item :href="$route('writings.edit', [writing.id])" prepend-icon="fas fa-pen-to-square" inertia>
        <span>{{ $t('main.edit-delete') }}</span>
      </po-list-item>
      <v-divider class="my-0"></v-divider>

      <po-list-item prepend-icon="fas fa-flag" @click.prevent="complainer = true">
        <span>{{ $t('complaints.report-writing') }}</span>
      </po-list-item>
      <v-divider class="my-0"></v-divider>

      <po-list-item :href="$route('users.block.confirm', [writing.author.username])" prepend-icon="fas fa-ban" inertia>
        <span>{{ $t('main.block-user') }}</span>
      </po-list-item>
    </v-list>
  </v-menu>
</template>