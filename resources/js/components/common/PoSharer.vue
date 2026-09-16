<script setup lang="ts">
import { sharerKey } from '@/composables/keys'
import { injectStrict } from '@/composables/injectStrict'
import { useSocialLinks } from '@/composables/useSocialLinks'
import { useFormatting } from '@/composables/useFormatting'

const props = defineProps<{
  linkTitle: string
  linkUrl: string
}>()

const { shareLinks } = useSocialLinks()
const { cropUrl } = useFormatting()
const sharer = injectStrict(sharerKey)
const social = shareLinks(props.linkTitle, props.linkUrl)

function copy(event: MouseEvent): void {
  const target = event.target as HTMLElement
  const socialEl = target.closest('.social')

  if (socialEl !== null && 'copy' === socialEl.id) {
    event.preventDefault()
    void navigator.clipboard.writeText(props.linkUrl)
  }

  sharer.value = false
}
</script>

<template>
  <v-dialog width="500" persistent>
    <v-card :title="$t('main.share-content')">
      <po-modal-close @click.prevent="sharer = false" />
      <v-card-text class="text-center">
        <p class="text-bold">{{ linkTitle }}</p>
        <p class="text-disabled">{{ cropUrl(linkUrl) }}</p>
      </v-card-text>

      <div class="d-flex flex-wrap pa-5 ga-3 w-100 justify-center">
        <template v-for="data in social" :key="data.name">
          <div :id="data.name" class="social">
            <po-button
              icon
              color="primary"
              size="64"
              :href="data.url"
              rel="noindex noopener"
              target="_blank"
              :title="'copy' === data.name ? $t('main.copy-link') : data.name"
              @click="copy"
            >
              <v-icon :icon="data.icon" />
            </po-button>
          </div>
        </template>
      </div>
    </v-card>
  </v-dialog>
</template>
