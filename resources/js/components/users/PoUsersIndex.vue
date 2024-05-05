<script setup>
import { computed, ref, inject, onMounted, nextTick } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import PoUsersCard from './partials/PoUsersCard.vue'
import axios from 'axios'
import MasonrySimple from 'masonry-simple'
import { useSwipe } from '@vueuse/core'

const page = computed(() => usePage())
const helper = inject('helper')
const users = ref([])
const next = ref('')
const fetched = ref(false)
const target = document.body

useSwipe(
  target,
  {
    passive: true,
    onSwipe() {
      //
    },
    onSwipeEnd(e, direction) {
      if (direction === 'left') {
        swipeRight()
      } else if (direction === 'right') {
        swipeLeft()
      }
    },
  },
)

async function loadMore({ done }) {
  if (!helper.strNullOrEmpty(next.value)) {
    await axios
      .get(next.value)
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
  if ("featured" === page.value.props.sort) {
    document.querySelector('.v-tab[value="latest"]').click()
  } else if ("latest" === page.value.props.sort) {
    document.querySelector('.v-tab[value="popular"]').click()
  }
}

function swipeLeft() {
  if ("popular" === page.value.props.sort) {
    document.querySelector('.v-tab[value="latest"]').click()
  } else if ("latest" === page.value.props.sort) {
    document.querySelector('.v-tab[value="featured"]').click()
  }
}

onMounted(async () => {
  await router.reload({
    only: ['users'],
    onSuccess: (page) => {
      update(page.props.users.data, page.props.users.next_page_url)
    }
  })
})

function update(usersData, nextPage) {
  users.value.push(...usersData)
  next.value = nextPage
  fetched.value = true

  nextTick(() => {
    new MasonrySimple({ container: '.masonry' }).init()
  })
}
</script>

<template>
  <po-head />

  <v-row class="sticky-tabs">
    <v-col cols="12">
      <v-tabs v-model="page.props.sort" fixed-tabs>
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
    <div class="masonry">
      <template v-for="user in users" :key="user.id">
        <div class="masonry__item">
          <po-users-card :alone="false" :data="user" />
        </div>
      </template>
    </div>

    <po-infinite-scroll @load="loadMore"></po-infinite-scroll>
  </template>

  <template v-else>
    <po-msg-block class="py-15" msg-title="" :msg-body="$t('main.nothing-to-display')"
      icon="fas fa-sad-tear"></po-msg-block>
  </template>
</template>
