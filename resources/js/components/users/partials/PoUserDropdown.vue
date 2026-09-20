<script setup lang="ts">
import { ref, provide } from 'vue'
import { blockerKey, complainerKey, sharerKey, userKey } from '@/composables/keys'
import { injectStrict } from '@/composables/injectStrict'
import { useAuth } from '@/composables/useAuth'
import { useFormatting } from '@/composables/useFormatting'
import { useNativeShare } from '@/composables/useNativeShare'

const user = injectStrict(userKey)
const { auth, authUser } = useAuth()
const { userDisplayName } = useFormatting()
const { share: nativeShare } = useNativeShare()
const sharer = ref(false)
const complainer = ref(false)
const blocker = ref(false)

provide(complainerKey, complainer)
provide(blockerKey, blocker)
provide(sharerKey, sharer)

function share(): void {
  nativeShare(userDisplayName(user), route('users.show', [user.username]), () => {
    sharer.value = true
  })
}
</script>

<template>
  <po-sharer
    v-model="sharer"
    :link-title="userDisplayName(user)"
    :link-url="route('users.show', [user.username])"
  />
  <po-complainer v-model="complainer" comp-type="users" :comp-id="user.id" />
  <po-blocker v-model="blocker" :user="user" />

  <v-menu open-on-hover>
    <template v-slot:activator="{ props }">
      <v-btn
        v-bind="props"
        icon="fas fa-plus"
        color="primary"
        size="x-small"
        class="po-btn-more"
        :aria-label="$t('main.more-actions')"
      />
    </template>

    <v-list>
      <po-list-item prepend-icon="fas fa-share-nodes" @click="share">
        <span>{{ $t('main.share-profile') }}</span>
      </po-list-item>
      <v-divider class="my-0" />

      <po-list-item
        :href="route('users.edit', [user.username])"
        prepend-icon="fas fa-user-pen"
        inertia
      >
        <span>{{ $t('accounts.update-profile') }}</span>
      </po-list-item>
      <v-divider class="my-0" />

      <po-list-item
        :href="route('users.writings.index', [user.username])"
        prepend-icon="fas fa-feather"
        inertia
      >
        <span>{{ $t('users.view-writings') }}</span>
      </po-list-item>
      <v-divider class="my-0" />

      <po-list-item
        :href="route('users.shelf.index', [user.username])"
        prepend-icon="fas fa-bookmark"
        inertia
      >
        <span>{{ $t('users.view-shelf') }}</span>
      </po-list-item>
      <v-divider class="my-0" />

      <po-list-item
        :href="route('users.likes.index', [user.username])"
        prepend-icon="fas fa-heart"
        inertia
      >
        <span>{{ $t('users.view-liked') }}</span>
      </po-list-item>
      <v-divider class="my-0" />

      <po-list-item prepend-icon="fas fa-flag" @click.prevent="complainer = true">
        <span>{{ $t('complaints.report-user') }}</span>
      </po-list-item>
      <v-divider class="my-0" />

      <template v-if="auth() && authUser()!.username !== user.username">
        <po-list-item prepend-icon="fas fa-ban" @click.prevent="blocker = true">
          <span>{{ $t('main.block-user') }}</span>
        </po-list-item>
      </template>
    </v-list>
  </v-menu>
</template>
