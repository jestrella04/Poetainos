<script setup lang="ts">
import { computed } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'
import type { InertiaPageProps } from '@/types/inertia'

interface PageMeta {
  title?: string
  canonical?: string | null
  description?: string
}

const page = computed(() => usePage<InertiaPageProps<{ meta: PageMeta }>>())
const title = (page.value.props.meta.title ??= page.value.props.site.name)
const canonical = (page.value.props.meta.canonical ??= null)
const description = (page.value.props.meta.description ??= page.value.props.site.slogan)
</script>

<template>
  <Head :title="title">
    <link v-if="null !== canonical" rel="canonical" :href="canonical" head-key="canonical" />
    <meta name="keywords" :content="$t('main.keywords')" />
    <meta name="description" :content="description" head-key="description" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ window.location.href }}" />
    <meta property="og:title" :content="title" />
    <meta property="og:description" :content="description" head-key="og-description" />
    <meta property="og:image" content="/images/cover.jpg" head-key="og-image" />
    <meta property="twitter:card" content="summary_large_image" />
    <meta property="twitter:url" content="{{ window.location.href }}" />
    <meta property="twitter:title" :content="title" />
    <meta property="twitter:description" :content="description" head-key="tw-description" />
    <meta property="twitter:image" content="/images/cover.jpg" head-key="tw-image" />
  </Head>
</template>
