<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import { useFormatting } from '@/composables/useFormatting'
import type { InertiaPageProps } from '@/types/inertia'

interface StaticPage {
  title: string
  text: string
}

const { markdown } = useFormatting()
const page = usePage<InertiaPageProps<{ page: StaticPage }>>()
const data = page.props.page
</script>

<template>
  <po-wrapper>
    <po-head />

    <p class="text-uppercase text-eyebrow text-primary">{{ data.title }}</p>
    <div v-html="markdown(data.text)" class="text-justify" />
  </po-wrapper>
</template>
