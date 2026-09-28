<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import { useDisplay } from 'vuetify'

const page = usePage()
const { mdAndDown } = useDisplay()
</script>

<template>
  <v-navigation-drawer :rail="mdAndDown" permanent>
    <v-list class="h-100">
      <v-defaults-provider :defaults="{ VListItem: { class: 'border-b' } }">
        <po-list-item prepend-avatar="/images/logo.svg" class="py-md-2">
          <div class="d-none d-lg-inline">
            <p class="po-prose text-title-large ma-0">{{ page.props.site.name }}</p>
            <p class="ma-0 text-uppercase text-eyebrow text-medium-emphasis">
              {{ $t('admin.admin-panel') }}
            </p>
          </div>
        </po-list-item>

        <po-list-item
          :href="route('home')"
          prepend-icon="fas fa-home"
          :title="$t('main.home')"
          inertia
        />

        <po-list-item
          :href="route('admin.index')"
          prepend-icon="fas fa-list"
          :title="$t('admin.summary')"
          inertia
        />

        <po-list-item
          :href="route('admin.activity')"
          prepend-icon="fas fa-clock"
          :title="$t('admin.activity')"
          inertia
        />

        <po-list-item
          :href="route('admin.settings')"
          prepend-icon="fas fa-cogs"
          :title="$t('admin.settings')"
          inertia
        />

        <po-list-item
          :href="route('admin.categories')"
          prepend-icon="fas fa-folder-open"
          :title="$t('categories.category')"
          inertia
        />

        <po-list-item
          :href="route('admin.tags')"
          prepend-icon="fas fa-hashtag"
          :title="$t('tags.tags')"
          inertia
        />

        <po-list-item
          :href="route('admin.pages')"
          prepend-icon="fas fa-file"
          :title="$t('pages.pages')"
          inertia
        />

        <po-list-item
          :href="route('admin.users')"
          prepend-icon="fas fa-users"
          :title="$t('users.users')"
          inertia
        />

        <po-list-item
          :href="route('admin.writings')"
          prepend-icon="fa fa-feather"
          :title="$t('writings.writings')"
          inertia
        />

        <po-list-item
          :href="route('admin.complaints')"
          prepend-icon="fas fa-scale-balanced"
          :title="$t('complaints.complaints')"
          inertia
        />

        <po-list-item
          :href="route('admin.logs')"
          prepend-icon="fas fa-file-lines"
          :title="$t('admin.logs')"
          inertia
        />

        <po-list-item
          :href="route('admin.analytics')"
          prepend-icon="fas fa-chart-pie"
          :title="$t('admin.analytics')"
          inertia
        />
      </v-defaults-provider>
    </v-list>
  </v-navigation-drawer>
</template>
