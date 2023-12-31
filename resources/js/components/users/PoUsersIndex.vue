<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import PoUsersEntry from './PoUsersEntry.vue'

const page = computed(() => usePage())
</script>

<template>
  <po-head />

  <v-row>
    <v-col cols="12">
      <v-tabs v-model="page.props.sort" fixed-tabs>
        <v-tab href="?sort=featured" @click.prevent="$inertia.get('?sort=featured')" value="featured">
          <v-icon icon="fas fa-fan" class="d-md-none" />
          <span class="d-none d-md-inline">{{ $t('main.featured') }}</span>
        </v-tab>

        <v-tab href="?sort=latest" @click.prevent="$inertia.get('?sort=latest')" value="latest">
          <v-icon icon="fas fa-clock" class="d-md-none" />
          <span class="d-none d-md-inline">{{ $t('main.most-recent') }}</span>
        </v-tab>

        <v-tab href="?sort=popular" @click.prevent="$inertia.get('?sort=popular')" value="popular">
          <v-icon icon="fas fa-fire-flame-curved" class="d-md-none" />
          <span class="d-none d-md-inline">{{ $t('main.most-popular') }}</span></v-tab>
      </v-tabs>
    </v-col>
  </v-row>

  <v-row>
    <v-col v-for="user in page.props.users.data" :key="user.id" tag="user" cols="12" sm="6" lg="4">
      <po-users-entry :alone="false" :data="user"></po-users-entry>
    </v-col>
  </v-row>
</template>
