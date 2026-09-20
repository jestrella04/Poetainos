<script setup lang="ts">
import { computed, provide } from 'vue'
import { useI18n } from 'vue-i18n'
import PoUserDropdown from './partials/PoUserDropdown.vue'
import { userKey } from '@/composables/keys'
import { useTypeGuards } from '@/composables/useTypeGuards'
import { useFormatting } from '@/composables/useFormatting'
import { useSocialLinks } from '@/composables/useSocialLinks'
import type { User } from '@/types/models'

const props = defineProps<{
  data: User
}>()

const { t } = useI18n()
const { isEmpty, strNullOrEmpty } = useTypeGuards()
const { userDisplayName, relativeDate, readable, cropUrl } = useFormatting()
const { socialLink } = useSocialLinks()

provide(userKey, props.data)

const headlineStats = computed<{ label: string; value: string }[]>(() => [
  { label: t('writings.writings'), value: readable(props.data.writings_count) },
  { label: t('main.likes'), value: readable(props.data.likes_count) },
  { label: t('main.profile-views'), value: readable(props.data.profile_views) }
])

const socialLinks = computed<Record<string, string>>(() =>
  props.data.social ? (JSON.parse(props.data.social) as Record<string, string>) : {}
)
</script>

<template>
  <div class="position-relative mb-8">
    <div class="d-flex flex-column flex-md-row ga-8 align-center align-md-start">
      <po-avatar-award :user="data" avatar-size="112" avatar-color="secondary" />

      <div class="flex-grow-1 text-center text-md-left">
        <p
          v-if="!strNullOrEmpty(data.location)"
          class="text-uppercase text-eyebrow text-primary ma-0 mb-2"
        >
          {{ data.location }}
        </p>

        <p class="text-display-large po-prose ma-0 mb-1">{{ userDisplayName(data) }}</p>
        <p class="ma-0 mb-4">@{{ data.username }}</p>

        <p v-if="!strNullOrEmpty(data.bio)" class="text-title-large po-prose ma-0 mb-4">
          {{ data.bio }}
        </p>

        <div class="d-flex flex-wrap justify-center justify-md-start ga-3">
          <template v-if="!isEmpty(socialLinks)">
            <template v-for="(user, network) in socialLinks" :key="network">
              <div v-if="!strNullOrEmpty(user)">
                <po-button
                  icon
                  color="primary"
                  size="x-small"
                  :href="socialLink(user, network)"
                  target="_blank"
                >
                  <v-icon v-if="network === 'twitter'" :icon="`fab fa-x-${network}`" />
                  <v-icon v-else :icon="`fab fa-${network}`" />
                </po-button>
              </div>
            </template>
          </template>

          <po-user-dropdown />
        </div>
      </div>

      <div class="d-flex ga-8 pt-md-2 text-center text-md-left">
        <div v-for="stat in headlineStats" :key="stat.label">
          <p class="text-display-small po-prose ma-0">{{ stat.value }}</p>
          <p class="text-uppercase text-eyebrow mt-1 mb-0">
            {{ stat.label }}
          </p>
        </div>
      </div>
    </div>

    <v-divider class="my-6" />

    <v-row>
      <v-col v-if="!strNullOrEmpty(data.created_at)" cols="12" sm="6" md="3">
        <p class="text-uppercase text-eyebrow text-medium-emphasis ma-0 mb-1">
          {{ $t('main.registered') }}
        </p>
        <p class="ma-0">{{ relativeDate(data.created_at ?? '') }}</p>
      </v-col>

      <v-col v-if="!strNullOrEmpty(data.website)" cols="12" sm="6" md="3">
        <p class="text-uppercase text-eyebrow text-medium-emphasis ma-0 mb-1">
          {{ $t('main.website') }}
        </p>

        <p class="ma-0">
          <po-link
            :href="data.website"
            class="text-primary"
            target="_blank"
            rel="noopener noreferrer"
          >
            {{ cropUrl(data.website ?? '') }}
          </po-link>
        </p>
      </v-col>

      <v-col v-if="!strNullOrEmpty(data.occupation)" cols="12" sm="6" md="3">
        <p class="text-uppercase text-eyebrow text-medium-emphasis ma-0 mb-1">
          {{ $t('main.occupation') }}
        </p>
        <p class="ma-0">{{ data.occupation }}</p>
      </v-col>

      <v-col v-if="!strNullOrEmpty(data.interests)" cols="12" sm="6" md="3">
        <p class="text-uppercase text-eyebrow text-medium-emphasis ma-0 mb-1">
          {{ $t('main.interests') }}
        </p>
        <p class="ma-0">{{ data.interests }}</p>
      </v-col>
    </v-row>

    <v-divider class="my-6" />
    <!-- <po-users-stats :data="data" :alone="true" />
    <v-divider class="mt-6" /> -->
  </div>
</template>
