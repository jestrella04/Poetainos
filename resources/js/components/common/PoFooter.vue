<script setup lang="ts">
import { computed, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useTypeGuards } from '@/composables/useTypeGuards'
import { useSocialLinks } from '@/composables/useSocialLinks'

const { isEmpty, strNullOrEmpty } = useTypeGuards()
const { socialLink, socialIcon } = useSocialLinks()
const page = computed(() => usePage())
const relatedApps = ref<RelatedApplication[]>([])

if (navigator.getInstalledRelatedApps !== undefined) {
  void navigator.getInstalledRelatedApps().then((related) => {
    relatedApps.value = related
  })
}
</script>

<style scoped>
footer {
  max-height: 96px !important;
  font-size: 0.7rem !important;
  padding: 1rem !important;
  text-align: center;
}

@media screen and (max-width: 1280px) {
  footer {
    margin-bottom: 56px !important;
  }
}
</style>

<template>
  <v-footer :elevation="2" class="d-flex flex-wrap align-center justify-space-around ga-2">
    <div class="d-inline-flex ga-3">&copy; 2020 {{ page.props.site.name }}</div>

    <div v-if="isEmpty(relatedApps)" class="d-inline-flex ga-3">
      <template v-for="(app, store) in page.props.site.stores" :key="store">
        <po-button
          v-if="'' !== app.value"
          :href="app.value"
          :prepend-icon="app.icon"
          color="secondary"
          size="x-small"
        >
          {{ store }}
        </po-button>
      </template>
    </div>

    <div class="d-inline-flex ga-3">
      <template v-for="(user, social) in page.props.site.social" :key="social">
        <template v-if="!strNullOrEmpty(user.value)">
          <po-button
            icon
            color="primary"
            size="x-small"
            :href="socialLink(user.value, social)"
            :title="$t('main.follow-on', { app: social })"
          >
            <v-icon :icon="socialIcon()[social]"></v-icon>
          </po-button>
        </template>
      </template>
    </div>
  </v-footer>
</template>
