<template>
  <v-row>
    <v-col v-for="n in 20" :key="n" cols="12" md="6" lg="4">
      <v-skeleton-loader class="border" type="image, article" elevation="2"></v-skeleton-loader>
    </v-col>
  </v-row>
</template>