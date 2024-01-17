<script setup></script>

<template>
  <po-wrapper>
    <po-head></po-head>

    <v-card>
      <v-card-text class="text-center">
        <v-img src="/images/logo.svg"></v-img>
        <po-msg-block :msg-title="$t('main.offline')" :msgBody="$t('main.you-are-offline')"
          icon="fas fa-sad-tear"></po-msg-block>
      </v-card-text>
    </v-card>
  </po-wrapper>
</template>