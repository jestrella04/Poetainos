<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = computed(() => usePage())
</script>

<style scoped>
nav {
  position: fixed;
  top: 0;
  bottom: 0;
  left: 0;
  z-index: 999;
  width: 60px;
}

.v-list {
  overflow-x: hidden !important;
}

@media screen and (min-width: 960px) {
  nav {
    width: 250px;
  }
}
</style>

<template>
  <nav>
    <v-list>
      <po-list-item prepend-avatar="/images/logo.svg">
        <div class="d-none d-md-inline">
          <p>{{ page.props.site.name }}</p>
          <p class="text-disabled text-caption">{{ $t('admin.admin-panel') }}</p>
        </div>
      </po-list-item>
    </v-list>

    <v-list class="h-100" nav>
      <v-divider class="my-0"></v-divider>

      <po-list-item :href="$route('home')" prepend-icon="fas fa-home" inertia>
        <span class="d-none d-md-inline">{{ $t('main.home') }}</span>
      </po-list-item>
      <v-divider class="my-0"></v-divider>

      <po-list-item :href="$route('admin.index')" prepend-icon="fas fa-list" inertia>
        <span class="d-none d-md-inline">{{ $t('admin.summary') }}</span>
      </po-list-item>
      <v-divider class="my-0"></v-divider>

      <po-list-item :href="$route('admin.settings')" prepend-icon="fas fa-cogs" inertia>
        <span class="d-none d-md-inline">{{ $t('admin.settings') }}</span>
      </po-list-item>
      <v-divider class="my-0"></v-divider>

      <po-list-item :href="$route('admin.categories')" prepend-icon="fas fa-folder-open" inertia>
        <span class="d-none d-md-inline">{{ $t('categories.category') }}</span>
      </po-list-item>
      <v-divider class="my-0"></v-divider>

      <po-list-item :href="$route('admin.tags')" prepend-icon="fas fa-hashtag" inertia>
        <span class="d-none d-md-inline">{{ $t('tags.tags') }}</span>
      </po-list-item>
      <v-divider class="my-0"></v-divider>

      <po-list-item :href="$route('admin.pages')" prepend-icon="fas fa-file" inertia>
        <span class="d-none d-md-inline">{{ $t('pages.pages') }}</span>
      </po-list-item>
      <v-divider class="my-0"></v-divider>

      <po-list-item :href="$route('admin.users')" prepend-icon="fas fa-users" inertia>
        <span class="d-none d-md-inline">{{ $t('users.users') }}</span>
      </po-list-item>
      <v-divider class="my-0"></v-divider>

      <po-list-item :href="$route('admin.writings')" prepend-icon="fa fa-feather" inertia>
        <span class="d-none d-md-inline">{{ $t('writings.writings') }}</span>
      </po-list-item>
      <v-divider class="my-0"></v-divider>

      <po-list-item :href="$route('admin.complaints')" prepend-icon="fas fa-scale-balanced" inertia>
        <span class="d-none d-md-inline">{{ $t('complaints.complaints') }}</span>
      </po-list-item>
      <v-divider class="my-0"></v-divider>

      <po-list-item :href="$route('admin.tools')" prepend-icon="fas fa-tools" inertia>
        <span class="d-none d-md-inline">{{ $t('admin.tools') }}</span>
      </po-list-item>
      <v-divider class="my-0"></v-divider>

      <po-list-item :href="$route('admin.websockets')" prepend-icon="fas fa-network-wired" inertia>
        <span class="d-none d-md-inline">{{ $t('admin.websockets') }}</span>
      </po-list-item>
      <v-divider class="my-0"></v-divider>

      <po-list-item :href="$route('admin.websockets')" prepend-icon="fas fa-chart-pie" inertia>
        <span class="d-none d-md-inline">{{ $t('admin.analytics') }}</span>
      </po-list-item>
      <v-divider class="my-0"></v-divider>
    </v-list>
  </nav>
</template>