<script setup lang="ts">
import { computed, ref, onMounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import PoWritingsEntry from './PoWritingsEntry.vue'
import axios from 'axios'
import { useSwipe } from '@vueuse/core'
import type { UseSwipeDirection } from '@vueuse/core'
import { useTypeGuards } from '@/composables/useTypeGuards'
import type { InertiaPageProps } from '@/types/inertia'
import type { Writing } from '@/types/models'

type InfiniteScrollStatus = 'ok' | 'empty' | 'loading' | 'error'

interface WritingsPage {
  data: Writing[]
  next_page_url: string | null
}

const page = computed(() => usePage<InertiaPageProps<{ sort: string; writings: WritingsPage }>>())
const { isEmpty, strNullOrEmpty } = useTypeGuards()
const writings = ref<Writing[]>([])
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
  if (!strNullOrEmpty(next.value)) {
    await axios
      .get<WritingsPage>(next.value)
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
  if ('latest' === page.value.props.sort) {
    document.querySelector<HTMLElement>('.v-tab[value="popular"]')?.click()
  } else if ('popular' === page.value.props.sort) {
    document.querySelector<HTMLElement>('.v-tab[value="likes"]')?.click()
  }
}

function swipeLeft() {
  if ('likes' === page.value.props.sort) {
    document.querySelector<HTMLElement>('.v-tab[value="popular"]')?.click()
  } else if ('popular' === page.value.props.sort) {
    document.querySelector<HTMLElement>('.v-tab[value="latest"]')?.click()
  }
}

onMounted(() => {
  router.reload({
    only: ['writings'],
    onSuccess: (successPage) => {
      const successProps = successPage.props as unknown as InertiaPageProps<{
        writings: WritingsPage
      }>
      update(successProps.writings.data, successProps.writings.next_page_url)
    }
  })
})

function update(writingsData: Writing[], nextPage: string | null) {
  writings.value.push(...writingsData)
  next.value = nextPage ?? ''
  fetched.value = true
}
</script>

<template>
  <po-wrapper class="h-100">
    <po-head></po-head>

    <v-row class="sticky-tabs">
      <v-col cols="12">
        <v-tabs :model-value="page.props.sort" fixed-tabs>
          <po-tab href="?sort=latest" value="latest" :aria-label="$t('main.most-recent')" inertia>
            <v-icon icon="fas fa-clock" class="d-md-none" />
            <span class="d-none d-md-inline">{{ $t('main.most-recent') }}</span>
          </po-tab>

          <po-tab
            href="?sort=popular"
            value="popular"
            :aria-label="$t('main.most-popular')"
            inertia
          >
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

    <template v-else-if="!isEmpty(writings)">
      <template v-for="writing in writings" :key="writing.slug">
        <po-writings-entry :alone="false" :data="writing" />
      </template>

      <po-infinite-scroll v-if="!strNullOrEmpty(next)" @load="loadMore"></po-infinite-scroll>
    </template>

    <template v-else>
      <po-msg-block
        class="py-15"
        msg-title=""
        :msg-body="$t('main.nothing-to-display')"
        icon="fas fa-sad-tear"
      ></po-msg-block>
    </template>
  </po-wrapper>
</template>
