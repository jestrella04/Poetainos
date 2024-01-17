<script setup>
import { inject } from 'vue'

const props = defineProps({
  linkTitle: { type: String, required: true },
  linkUrl: { type: String, required: true },
})

const helper = inject('helper')
const sharer = inject('sharer')
const social = helper.shareLinks(props.linkTitle, props.linkUrl)

function copy(event) {
  if ('copy' === event.target.closest('.social').id) {
    event.preventDefault()
    navigator.clipboard.writeText(props.linkUrl);
  }

  sharer.value = false
}
</script>

<template>
  <v-dialog width="500" persistent>
    <v-card :title="$t('main.share-content')">
      <po-modal-close @click.prevent="sharer = false"></po-modal-close>
      <v-card-text class="text-center">
        <p class="text-bold">{{ linkTitle }}</p>
        <p class="text-disabled">{{ $helper.cropUrl(linkUrl) }}</p>
      </v-card-text>

      <v-card-actions>
        <div class="d-flex flex-wrap pa-5 ga-5 w-100 justify-center">
          <template v-for="data in social" :key="data.name">
            <div :id="data.name" class="social">
              <po-link :href="data.url" rel="noindex noopener" target="_blank"
                :title="'copy' === data.name ? $t('main.copy-link') : data.name" @click="copy">
                <v-icon :icon="data.icon" size="48"></v-icon>
              </po-link>
            </div>
          </template>
        </div>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>