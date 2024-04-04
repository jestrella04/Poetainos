<script setup>
import { computed, ref, inject, onMounted, nextTick } from 'vue'
import { usePage } from '@inertiajs/vue3'
import PoUsersCard from './partials/PoUsersCard.vue'
import axios from 'axios'
import Masonry from '@paper-folding/masonry-layout'
import { useSwipe } from '@vueuse/core'

const page = computed(() => usePage())
const helper = inject('helper')
const users = ref(page.value.props.users.data)
const next = ref(page.value.props.users.next_page_url)
//const fetched = ref(false) //TODO: add loading state
const target = document.body

useSwipe(
  target,
  {
    passive: false,
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
        users.value.push(...response.data.data)
        next.value = response.data.next_page_url
        nextTick(() => {
          new Masonry('.masonry', { "percentPosition": true })
        })
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

onMounted(() => {
  new Masonry('.masonry', { "percentPosition": true })
})
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

  <v-row class="masonry">
    <v-col v-for="user in users" :key="user.id" tag="user" cols="12" sm="6" lg="4">
      <po-users-card :alone="false" :data="user" />
    </v-col>
  </v-row>

  <po-infinite-scroll @load="loadMore"></po-infinite-scroll>
</template>
