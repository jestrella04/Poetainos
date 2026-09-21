<script setup lang="ts">
import { provide, ref } from 'vue'
import { blockerKey, complainerKey, sharerKey, writingKey } from '@/composables/keys'
import { injectStrict } from '@/composables/injectStrict'
import { useAuth } from '@/composables/useAuth'
import { useNativeShare } from '@/composables/useNativeShare'

const { isAuthenticated, authUser, canEdit } = useAuth()
const { share: nativeShare } = useNativeShare()
const writing = injectStrict(writingKey)
const sharer = ref(false)
const complainer = ref(false)
const blocker = ref(false)

provide(complainerKey, complainer)
provide(blockerKey, blocker)
provide(sharerKey, sharer)

function share(): void {
  nativeShare(writing.title, route('writings.show', [writing.slug]), () => {
    sharer.value = true
  })
}
</script>

<template>
  <po-sharer
    v-model="sharer"
    :link-title="writing.title"
    :link-url="route('writings.show', [writing.slug])"
  />
  <po-complainer v-model="complainer" comp-type="writings" :comp-id="writing.id" />
  <po-blocker v-model="blocker" :user="writing.author" />

  <v-menu open-on-hover>
    <template v-slot:activator="{ props }">
      <v-btn
        v-bind="props"
        prepend-icon="fas fa-angle-down"
        color="primary"
        variant="tonal"
        :aria-label="$t('main.more-actions')"
      >
        {{ $t('main.more') }}
      </v-btn>
    </template>

    <v-list>
      <po-list-item prepend-icon="fas fa-share-nodes" @click="share">
        <span>{{ $t('main.share-writing') }}</span>
      </po-list-item>
      <v-divider class="my-0" />

      <template v-if="canEdit(writing.author)">
        <po-list-item
          :href="route('writings.edit', [writing.slug])"
          prepend-icon="fas fa-pen-to-square"
          inertia
        >
          <span>{{ $t('main.edit-delete') }}</span>
        </po-list-item>
        <v-divider class="my-0" />
      </template>

      <po-list-item prepend-icon="fas fa-flag" @click.prevent="complainer = true">
        <span>{{ $t('complaints.report-writing') }}</span>
      </po-list-item>

      <template v-if="isAuthenticated() && authUser()!.username !== writing.author.username">
        <v-divider class="my-0" />
        <po-list-item prepend-icon="fas fa-ban" @click.prevent="blocker = true">
          <span>{{ $t('main.block-user') }}</span>
        </po-list-item>
      </template>
    </v-list>
  </v-menu>
</template>
