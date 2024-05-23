<script setup>
import { provide } from 'vue'
import { ref, inject } from 'vue'

const writing = inject('writing')
const sharer = ref(false)
const complainer = ref(false)
const blocker = ref(false)

provide('complainer', complainer)
provide('blocker', blocker)
provide('sharer', sharer)

function share() {
  if (navigator.share) {
    navigator.share({
      title: writing.title,
      url: route('writings.show', [writing.slug])
    })
  } else {
    sharer.value = true
  }
}
</script>

<template>
  <po-sharer
    v-model="sharer"
    :link-title="writing.title"
    :link-url="route('writings.show', [writing.slug])"
  ></po-sharer>
  <po-complainer v-model="complainer" comp-type="writings" :comp-id="writing.id"></po-complainer>
  <po-blocker v-model="blocker" :user="writing.author"></po-blocker>

  <v-menu>
    <template v-slot:activator="{ props }">
      <v-btn
        v-bind="props"
        icon="fas fa-ellipsis-vertical"
        color="secondary"
        size="x-small"
        variant="tonal"
        class="po-btn-more"
        :aria-label="$t('main.more-actions')"
      >
      </v-btn>
    </template>

    <v-list>
      <po-list-item prepend-icon="fas fa-share-nodes" @click="share">
        <span>{{ $t('main.share-writing') }}</span>
      </po-list-item>
      <v-divider class="my-0"></v-divider>

      <template v-if="$helper.canEdit(writing.author)">
        <po-list-item
          :href="route('writings.edit', [writing.slug])"
          prepend-icon="fas fa-pen-to-square"
          inertia
        >
          <span>{{ $t('main.edit-delete') }}</span>
        </po-list-item>
        <v-divider class="my-0"></v-divider>
      </template>

      <po-list-item prepend-icon="fas fa-flag" @click.prevent="complainer = true">
        <span>{{ $t('complaints.report-writing') }}</span>
      </po-list-item>

      <template v-if="$helper.auth() && $helper.authUser().username !== writing.author.username">
        <v-divider class="my-0"></v-divider>
        <po-list-item prepend-icon="fas fa-ban" @click.prevent="blocker = true">
          <span>{{ $t('main.block-user') }}</span>
        </po-list-item>
      </template>
    </v-list>
  </v-menu>
</template>
