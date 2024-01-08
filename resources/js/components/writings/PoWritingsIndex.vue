<script setup>
import { computed, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import PoWritingsEntry from './PoWritingsEntry.vue'
import axios from 'axios'
import { inject } from 'vue';

const page = computed(() => usePage())
const helper = inject('helper')
const writings = ref(page.value.props.writings.data)
const next = ref(page.value.props.writings.next_page_url)

function loadMore({ done }) {
  if (!helper.strNullOrEmpty(next.value)) {
    axios.get(next.value).then((response) => {
      writings.value.push(...response.data.data)
      next.value = response.data.next_page_url
      done('ok')
    })
  } else {
    done('empty')
  }

}
</script>

<template>
  <po-head></po-head>
  <v-row>
    <v-col cols="12">
      <v-tabs v-model="page.props.sort" fixed-tabs>
        <v-tab href="?sort=latest" @click.prevent="$inertia.get('?sort=latest')" value="latest">
          <v-icon icon="fas fa-clock" class="d-md-none" />
          <span class="d-none d-md-inline">{{ $t('main.most-recent') }}</span>
        </v-tab>

        <v-tab href="?sort=popular" @click.prevent="$inertia.get('?sort=popular')" value="popular">
          <v-icon icon="fas fa-fire-flame-curved" class="d-md-none" />
          <span class="d-none d-md-inline">{{ $t('main.most-popular') }}</span>
        </v-tab>

        <v-tab href="?sort=likes" @click.prevent="$inertia.get('?sort=likes')" value="likes">
          <v-icon icon="fas fa-heart" class="d-md-none" />
          <span class="d-none d-md-inline">{{ $t('main.most-liked') }}</span>
        </v-tab>
      </v-tabs>
    </v-col>
  </v-row>

  <v-row>
    <v-col v-for="writing in writings" :key="writing.slug" tag="writing" cols="12" md="6" lg="4">
      <po-writings-entry :alone="false" :data="writing" />
    </v-col>
  </v-row>

  <v-infinite-scroll mode="manual" color="primary" :load-more-text="$t('main.load-more')"
    :empty-text="$t('main.nothing-to-display')" @load="loadMore">

  </v-infinite-scroll>
</template>
