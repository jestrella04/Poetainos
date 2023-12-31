<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import PoWritingsEntry from './PoWritingsEntry.vue'

const page = computed(() => usePage())
</script>

<template>
  <po-head v-if="'writings.awards' === page.props.route.name" :title="$t('main.awards')" />

  <v-row>
    <v-col cols="12">
      <v-tabs v-model="page.props.sort" fixed-tabs>
        <v-tab href="?sort=latest" @click.prevent="$inertia.get('?sort=latest')" value="latest">
          <v-icon icon="fas fa-clock" class="d-md-none" />
          <span class="d-none d-md-inline">{{ $t('main.most-recent') }}</span>
        </v-tab>

        <v-tab href="?sort=popular" @click.prevent="$inertia.get('?sort=popular')" value="popular">
          <v-icon icon="fas fa-fire-flame-curved" class="d-md-none" />
          <span class="d-none d-md-inline">{{ $t('main.most-popular') }}</span>
        </v-tab>

        <v-tab href="?sort=likes" @click.prevent="$inertia.get('?sort=likes')" value="likes">
          <v-icon icon="fas fa-heart" class="d-md-none" />
          <span class="d-none d-md-inline">{{ $t('main.most-liked') }}</span></v-tab>
      </v-tabs>
    </v-col>
  </v-row>

  <v-row>
    <v-col v-for="writing in page.props.writings.data" :key="writing.slug" tag="writing" cols="12" md="6" lg="4">
      <po-writings-entry :alone="false" :data="writing"></po-writings-entry>
    </v-col>
  </v-row>
</template>
