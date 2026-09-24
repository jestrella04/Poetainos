<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3'
import type { InertiaPageProps } from '@/types/inertia'

interface PageMeta {
  title?: string
  canonical?: string | null
  description?: string
  image?: string | null
}

const DEFAULT_IMAGE_PATH = '/images/cover.jpg'

const page = usePage<InertiaPageProps<{ meta: PageMeta }>>()
const title = (page.props.meta.title ??= page.props.site.name)
const canonical = (page.props.meta.canonical ??= null)
const description = (page.props.meta.description ??= page.props.site.slogan)
// Link previews (Facebook, X) only accept absolute image URLs.
const image = page.props.meta.image ?? new URL(DEFAULT_IMAGE_PATH, page.props.ziggy.url).href
</script>

<template>
  <Head :title="title">
    <link v-if="null !== canonical" rel="canonical" :href="canonical" head-key="canonical" />
    <meta name="keywords" :content="$t('main.keywords')" />
    <meta name="description" :content="description" head-key="description" />
    <meta property="og:type" content="website" />
    <meta v-if="null !== canonical" property="og:url" :content="canonical" head-key="og-url" />
    <meta property="og:title" :content="title" />
    <meta property="og:description" :content="description" head-key="og-description" />
    <meta property="og:image" :content="image" head-key="og-image" />
    <meta property="twitter:card" content="summary_large_image" />
    <meta v-if="null !== canonical" property="twitter:url" :content="canonical" head-key="tw-url" />
    <meta property="twitter:title" :content="title" />
    <meta property="twitter:description" :content="description" head-key="tw-description" />
    <meta property="twitter:image" :content="image" head-key="tw-image" />
  </Head>
</template>
