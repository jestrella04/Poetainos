<script setup></script>

<template>
  <v-infinite-scroll color="primary" class="mt-10" mode="manual">
    <template v-slot:load-more="{ props }">
      <v-btn variant="tonal" v-bind="props">{{ $t('main.load-more') }}</v-btn>
    </template>

    <template v-slot:empty>
      <span class="text-disabled">{{ $t('main.nothing-to-display') }}</span>
    </template>
  </v-infinite-scroll>
</template>