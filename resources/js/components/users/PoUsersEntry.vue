<script setup>
import { provide } from 'vue'
import PoUsersStats from './PoUsersStats.vue'
import PoUserDropdown from './partials/PoUserDropdown.vue'

const props = defineProps({
  data: { type: Object, required: true },
})

provide('user', props.data)
</script>

<template>
  <v-card class="mb-5 pos-relative" elevation="2" rounded>
    <po-user-dropdown></po-user-dropdown>

    <v-card-text>
      <div class="text-center mb-5">
        <po-avatar size="96" color="secondary" class="mb-2" :user="data" />
        <p class="font-weight-bold">{{ $helper.userDisplayName(data) }}</p>
        <p class="text-medium-emphasis">@{{ data.username }}</p>
      </div>

      <template v-if="!$helper.strNullOrEmpty(data.website) || !$helper.isEmpty(JSON.parse(data.social))">
        <div class="d-flex flex-wrap justify-center ga-5 mb-5">
          <template v-if="!$helper.strNullOrEmpty(data.website)">
            <div>
              <po-link :href="data.website" target="_blank">
                <v-icon icon="fas fa-globe"></v-icon>
              </po-link>
            </div>
          </template>

          <template v-for="(user, network) in JSON.parse(data.social)" :key="network">
            <div>
              <po-link :href="$helper.socialLink(user, network)" target="_blank">
                <v-icon v-if="network === 'twitter'" :icon="`fab fa-x-${network}`"></v-icon>
                <v-icon v-else :icon="`fab fa-${network}`"></v-icon>
              </po-link>
            </div>
          </template>
        </div>
      </template>

      <div class="mx-auto" style="width: 100%; max-width: 400px;">
        <p>{{ data.bio }}</p>
      </div>
    </v-card-text>

    <v-divider></v-divider>
    <v-card-actions>
      <po-users-stats :data="data" :alone="true" />
    </v-card-actions>
  </v-card>

  <v-card :subtitle="$t('main.more-info').toUpperCase()">
    <v-card-text>
      <v-row v-if="!$helper.strNullOrEmpty(data.created_at)">
        <v-col cols="12" md="4">
          <v-icon icon="fas fa-calendar" class="mr-2"></v-icon>
          {{ $t('main.registered') }}:
        </v-col>
        <v-col cols="12" md="8">
          {{ $helper.relativeDate(data.created_at) }}
        </v-col>
      </v-row>

      <v-row v-if="!$helper.strNullOrEmpty(data.location)">
        <v-col cols="12" md="4">
          <v-icon icon="fas fa-map-marker-alt" class="mr-2"></v-icon>
          {{ $t('main.location') }}:
        </v-col>
        <v-col cols="12" md="8">
          {{ data.location }}
        </v-col>
      </v-row>

      <v-row v-if="!$helper.strNullOrEmpty(data.occupation)">
        <v-col cols="12" md="4">
          <v-icon icon="fas fa-toolbox" class="mr-2"></v-icon>
          {{ $t('main.occupation') }}:
        </v-col>
        <v-col cols="12" md="8">
          {{ data.occupation }}
        </v-col>
      </v-row>

      <v-row v-if="!$helper.strNullOrEmpty(data.interests)">
        <v-col cols="12" md="4">
          <v-icon icon="fas fa-masks-theater" class="mr-2"></v-icon>
          {{ $t('main.interests') }}:</v-col>
        <v-col cols="12" md="8">
          {{ data.interests }}
        </v-col>
      </v-row>
    </v-card-text>
  </v-card>
</template>