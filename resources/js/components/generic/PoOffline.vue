<script setup></script>

<template>
  <v-card>
    <po-head></po-head>
    <v-card-text class="text-center">
      <v-img src="images/logo.svg"></v-img>
      <po-msg-block :msg-title="$t('main.offline')" :msgBody="$t('main.you-are-offline')"
        icon="fas fa-sad-tear"></po-msg-block>
    </v-card-text>
  </v-card>
</template>