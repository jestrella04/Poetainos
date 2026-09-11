<script setup lang="ts">
import { computed, ref, onMounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import PoUsersCard from './partials/PoUsersCard.vue'
import axios from 'axios'
import { useSwipe } from '@vueuse/core'
import type { UseSwipeDirection } from '@vueuse/core'
import { helperKey } from '@/composables/keys'
import { injectStrict } from '@/composables/injectStrict'
import type { InertiaPageProps } from '@/types/inertia'
import type { User } from '@/types/models'

type InfiniteScrollStatus = 'ok' | 'empty' | 'loading' | 'error'

interface UsersPage {
  data: User[]
  next_page_url: string | null
}

const page = computed(() => usePage<InertiaPageProps<{ sort: string; users: UsersPage }>>())
const helper = injectStrict(helperKey)
const users = ref<User[]>([])
const next = ref('')
const fetched = ref(false)
const target = document.body

useSwipe(target, {
  passive: true,
  onSwipe() {
    //
  },
  onSwipeEnd(_e: TouchEvent, direction: UseSwipeDirection) {
    if (direction === 'left') {
      swipeRight()
    } else if (direction === 'right') {
      swipeLeft()
    }
  }
})

async function loadMore({ done }: { done: (status: InfiniteScrollStatus) => void }) {
  if (!helper.strNullOrEmpty(next.value)) {
    await axios
      .get<UsersPage>(next.value)
      .then((response) => {
        update(response.data.data, response.data.next_page_url)
        done('ok')
      })
      .catch(() => {
        done('error')
      })
  } else {
    done('empty')
  }
}

function swipeRight() {
  if ('featured' === page.value.props.sort) {
    document.querySelector<HTMLElement>('.v-tab[value="latest"]')?.click()
  } else if ('latest' === page.value.props.sort) {
    document.querySelector<HTMLElement>('.v-tab[value="popular"]')?.click()
  }
}

function swipeLeft() {
  if ('popular' === page.value.props.sort) {
    document.querySelector<HTMLElement>('.v-tab[value="latest"]')?.click()
  } else if ('latest' === page.value.props.sort) {
    document.querySelector<HTMLElement>('.v-tab[value="featured"]')?.click()
  }
}

onMounted(() => {
  router.reload({
    only: ['users'],
    onSuccess: (successPage) => {
      const successProps = successPage.props as unknown as InertiaPageProps<{ users: UsersPage }>
      update(successProps.users.data, successProps.users.next_page_url)
    }
  })
})

function update(usersData: User[], nextPage: string | null) {
  users.value.push(...usersData)
  next.value = nextPage ?? ''
  fetched.value = true
}
</script>

<template>
  <po-head />

  <v-row class="sticky-tabs">
    <v-col cols="12">
      <v-tabs :model-value="page.props.sort" fixed-tabs>
        <po-tab href="?sort=featured" value="featured" :aria-label="$t('main.featured')" inertia>
          <v-icon icon="fas fa-fan" class="d-md-none" />
          <span class="d-none d-md-inline">{{ $t('main.featured') }}</span>
        </po-tab>

        <po-tab href="?sort=latest" value="latest" :aria-label="$t('main.most-recent')" inertia>
          <v-icon icon="fas fa-clock" class="d-md-none" />
          <span class="d-none d-md-inline">{{ $t('main.most-recent') }}</span>
        </po-tab>

        <po-tab href="?sort=popular" value="popular" :aria-label="$t('main.most-popular')" inertia>
          <v-icon icon="fas fa-fire" class="d-md-none" />
          <span class="d-none d-md-inline">{{ $t('main.most-popular') }}</span>
        </po-tab>
      </v-tabs>
    </v-col>
  </v-row>

  <template v-if="!fetched">
    <po-loading type="avatar, paragraph, divider, text"></po-loading>
  </template>

  <template v-else-if="!$helper.isEmpty(users)">
    <template v-for="user in users" :key="user.id">
      <po-users-card :alone="false" :data="user" />
    </template>

    <po-infinite-scroll @load="loadMore"></po-infinite-scroll>
  </template>

  <template v-else>
    <po-msg-block
      class="py-15"
      msg-title=""
      :msg-body="$t('main.nothing-to-display')"
      icon="fas fa-sad-tear"
    ></po-msg-block>
  </template>
</template>
