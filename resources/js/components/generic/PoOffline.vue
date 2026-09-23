<script setup lang="ts"></script>

<template>
  <po-wrapper>
    <po-head />

    <v-card>
      <v-card-text class="text-center">
        <v-img src="/images/logo.svg" />
        <po-msg-block
          :msg-title="$t('main.offline')"
          :msgBody="$t('main.you-are-offline')"
          icon="fas fa-sad-tear"
        />
      </v-card-text>
    </v-card>
  </po-wrapper>
</template>
