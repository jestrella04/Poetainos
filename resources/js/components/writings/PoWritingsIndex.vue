<script setup>
import { computed, ref, inject, onMounted, nextTick } from 'vue'
import { usePage } from '@inertiajs/vue3'
import PoWritingsEntry from './PoWritingsEntry.vue'
import axios from 'axios'
import Masonry from 'masonry-layout'

const page = computed(() => usePage())
const helper = inject('helper')
const writings = ref(page.value.props.writings.data)
const next = ref(page.value.props.writings.next_page_url)
const mason = ref()

async function loadMore({ done }) {
  if (!helper.strNullOrEmpty(next.value)) {
    await axios
      .get(next.value)
      .then((response) => {
        writings.value.push(...response.data.data)
        next.value = response.data.next_page_url
        nextTick(() => {
          mason.value = new Masonry('.masonry', { "percentPosition": true })
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
  mason.value = new Masonry('.masonry', { "percentPosition": true })
})

function liked(id, count) {
  const liked = Object.values(writings.value).filter((writing) => {
    if (writing.id === id) {
      return
    }
  })

  liked.likes_count.value = count
}
</script>

<template>
  <po-wrapper v-if="!$helper.isEmpty(writings)">
    <po-head></po-head>
    <v-row>
      <v-col cols="12">
        <v-tabs v-model="page.props.sort" fixed-tabs>
          <po-tab href="?sort=latest" value="latest" :aria-label="$t('main.most-recent')" inertia>
            <v-icon icon="fas fa-clock" class="d-md-none" />
            <span class="d-none d-md-inline">{{ $t('main.most-recent') }}</span>
          </po-tab>

          <po-tab href="?sort=popular" value="popular" :aria-label="$t('main.most-popular')" inertia>
            <v-icon icon="fas fa-fire-flame-curved" class="d-md-none" />
            <span class="d-none d-md-inline">{{ $t('main.most-popular') }}</span>
          </po-tab>

          <po-tab href="?sort=likes" value="likes" :aria-label="$t('main.most-liked')" inertia>
            <v-icon icon="fas fa-heart" class="d-md-none" />
            <span class="d-none d-md-inline">{{ $t('main.most-liked') }}</span>
          </po-tab>
        </v-tabs>
      </v-col>
    </v-row>

    <v-row class="masonry">
      <v-col v-for="writing in writings" :key="writing.slug" tag="writing" cols="12" md="6" lg="4">
        <po-writings-entry @liked="liked" :alone="false" :data="writing" />
      </v-col>
    </v-row>

    <po-infinite-scroll v-if="!$helper.strNullOrEmpty(next)" @load="loadMore"></po-infinite-scroll>
  </po-wrapper>

  <po-wrapper v-else>
    <po-head></po-head>
    <po-msg-block class="py-15" :msg-body="$t('main.nothing-to-display')" icon="fas fa-sad-tear"></po-msg-block>
  </po-wrapper>
</template>
