<script setup>
import { computed, ref, inject, onMounted, nextTick } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import PoWritingsEntry from './PoWritingsEntry.vue'
import axios from 'axios'
import { useSwipe } from '@vueuse/core'
import MasonrySimple from 'masonry-simple'

const page = computed(() => usePage())
const helper = inject('helper')
const writings = ref([])
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
  if ("latest" === page.value.props.sort) {
    document.querySelector('.v-tab[value="popular"]').click()
  } else if ("popular" === page.value.props.sort) {
    document.querySelector('.v-tab[value="likes"]').click()
  }
}

function swipeLeft() {
  if ("likes" === page.value.props.sort) {
    document.querySelector('.v-tab[value="popular"]').click()
  } else if ("popular" === page.value.props.sort) {
    document.querySelector('.v-tab[value="latest"]').click()
  }
}

onMounted(async () => {
  await router.reload({
    only: ['writings'],
    onSuccess: (page) => {
      update(page.props.writings.data, page.props.writings.next_page_url)
    }
  })
})

function update(writingsData, nextPage) {
  writings.value.push(...writingsData)
  next.value = nextPage
  fetched.value = true

  nextTick(() => {
    new MasonrySimple({ container: '.masonry' }).init()
  })
}

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
  <po-wrapper>
    <po-head></po-head>

    <v-row class="sticky-tabs">
      <v-col cols="12">
        <v-tabs v-model="page.props.sort" fixed-tabs>
          <po-tab href="?sort=latest" value="latest" :aria-label="$t('main.most-recent')" inertia>
            <v-icon icon="fas fa-clock" class="d-md-none" />
            <span class="d-none d-md-inline">{{ $t('main.most-recent') }}</span>
          </po-tab>

          <po-tab href="?sort=popular" value="popular" :aria-label="$t('main.most-popular')" inertia>
            <v-icon icon="fas fa-fire" class="d-md-none" />
            <span class="d-none d-md-inline">{{ $t('main.most-popular') }}</span>
          </po-tab>

          <po-tab href="?sort=likes" value="likes" :aria-label="$t('main.most-liked')" inertia>
            <v-icon icon="fas fa-heart" class="d-md-none" />
            <span class="d-none d-md-inline">{{ $t('main.most-liked') }}</span>
          </po-tab>
        </v-tabs>
      </v-col>
    </v-row>

    <template v-if="!fetched">
      <po-loading></po-loading>
    </template>

    <template v-else-if="!$helper.isEmpty(writings)">
      <div class="masonry">
        <template v-for="writing in writings" :key="writing.slug">
          <writing class="masonry__item">
            <po-writings-entry @liked="liked" :alone="false" :data="writing" />
          </writing>
        </template>
      </div>

      <po-infinite-scroll v-if="!$helper.strNullOrEmpty(next)" @load="loadMore"></po-infinite-scroll>
    </template>

    <template v-else>
      <po-msg-block class="py-15" msg-title="" :msg-body="$t('main.nothing-to-display')"
        icon="fas fa-sad-tear"></po-msg-block>
    </template>
  </po-wrapper>
</template>
