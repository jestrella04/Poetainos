<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3'
import _ from 'lodash'

const page = usePage()
const site = computed(() => page.props.site)

defineProps({ title: String })

function getFullTitle(title) {
  if (!_.isNil(title) && '' !== title) {
    return `${title} – ${site.value.name}`
  }

  return site.value.name
}
</script>

<template>
  <Head :title="getFullTitle(title)">
    <meta type="description" :content="site.slogan" head-key="description">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ window.location.href }}">
    <meta property="og:title" :content="getFullTitle(title)">
    <meta property="og:description" :content="site.slogan" head-key="og-description">
    <meta property="og:image" content="/images/cover.jpg" head-key="og-image">
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ window.location.href }}">
    <meta property="twitter:title" :content="getFullTitle(title)">
    <meta property="twitter:description" :content="site.slogan" head-key="tw-description">
    <meta property="twitter:image" content="/images/cover.jpg" head-key="tw-image">
  </Head>
</template>