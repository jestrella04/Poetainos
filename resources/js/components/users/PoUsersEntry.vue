<script setup lang="ts">
import { computed, provide } from 'vue'
import PoUsersStats from './partials/PoUsersStats.vue'
import PoUserDropdown from './partials/PoUserDropdown.vue'
import { userKey } from '@/composables/keys'
import { useTypeGuards } from '@/composables/useTypeGuards'
import { useFormatting } from '@/composables/useFormatting'
import { useSocialLinks } from '@/composables/useSocialLinks'
import type { User } from '@/types/models'

const props = defineProps<{
  data: User
}>()

const { isEmpty, strNullOrEmpty } = useTypeGuards()
const { userDisplayName, relativeDate } = useFormatting()
const { socialLink } = useSocialLinks()

provide(userKey, props.data)

const socialLinks = computed<Record<string, string>>(() =>
  props.data.social ? (JSON.parse(props.data.social) as Record<string, string>) : {}
)
</script>

<template>
  <v-card class="mb-5 position-relative">
    <po-user-dropdown />

    <v-card-text>
      <div class="d-flex flex-column flex-sm-row ga-6 align-center align-sm-start">
        <po-avatar-award :user="data" avatar-size="104" avatar-color="secondary" />

        <div class="flex-grow-1 text-center text-sm-left">
          <p
            v-if="!strNullOrEmpty(data.location)"
            class="text-caption text-uppercase text-eyebrow text-on-surface-variant mb-1"
          >
            {{ data.location }}
          </p>
          <p class="text-h4 mb-1">{{ userDisplayName(data) }}</p>
          <p class="text-on-surface-variant mb-4">@{{ data.username }}</p>

          <p v-if="!strNullOrEmpty(data.bio)" class="po-prose">{{ data.bio }}</p>

          <template v-if="!strNullOrEmpty(data.website) || !isEmpty(socialLinks)">
            <div class="d-flex flex-wrap justify-center justify-sm-start ga-3 mt-4">
              <template v-if="!strNullOrEmpty(data.website)">
                <div>
                  <po-button
                    icon
                    color="primary"
                    size="x-small"
                    :href="data.website"
                    target="_blank"
                  >
                    <v-icon icon="fas fa-globe" />
                  </po-button>
                </div>
              </template>

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
            </div>
          </template>
        </div>
      </div>
    </v-card-text>

    <v-divider />
    <v-card-actions>
      <po-users-stats :data="data" :alone="true" />
    </v-card-actions>
  </v-card>

  <v-card :subtitle="$t('main.more-info').toUpperCase()">
    <v-card-text>
      <v-row v-if="!strNullOrEmpty(data.created_at)">
        <v-col cols="12" md="4">
          <v-icon icon="fas fa-calendar" class="mr-2" />
          {{ $t('main.registered') }}:
        </v-col>
        <v-col cols="12" md="8">
          {{ relativeDate(data.created_at ?? '') }}
        </v-col>
      </v-row>

      <v-row v-if="!strNullOrEmpty(data.occupation)">
        <v-col cols="12" md="4">
          <v-icon icon="fas fa-toolbox" class="mr-2" />
          {{ $t('main.occupation') }}:
        </v-col>
        <v-col cols="12" md="8">
          {{ data.occupation }}
        </v-col>
      </v-row>

      <v-row v-if="!strNullOrEmpty(data.interests)">
        <v-col cols="12" md="4">
          <v-icon icon="fas fa-masks-theater" class="mr-2" />
          {{ $t('main.interests') }}:</v-col
        >
        <v-col cols="12" md="8">
          {{ data.interests }}
        </v-col>
      </v-row>
    </v-card-text>
  </v-card>
</template>
