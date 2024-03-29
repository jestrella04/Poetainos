<script setup>
import { computed, ref, inject, onMounted, nextTick } from 'vue'
import { usePage } from '@inertiajs/vue3'
import PoUsersCard from './partials/PoUsersCard.vue'
import axios from 'axios'
import Masonry from '@paper-folding/masonry-layout'

const page = computed(() => usePage())
const helper = inject('helper')
const users = ref(page.value.props.users.data)
const next = ref(page.value.props.users.next_page_url)

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

onMounted(() => {
  new Masonry('.masonry', { "percentPosition": true })
})
</script>

<template>
  <po-head />

  <v-row>
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
          <v-icon icon="fas fa-fire-flame-curved" class="d-md-none" />
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
