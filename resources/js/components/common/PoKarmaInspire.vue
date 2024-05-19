<script setup>
import { inject, onMounted, ref } from 'vue'

defineProps({
  tooltip: {
    type: Boolean,
    default: false
  }
})

const author = inject('author')
const karma = inject('karma')
const helper = inject('helper')
const neededActions = ref(0)

onMounted(() => {
  // Check how many interactions left before user can publish
  if (helper.isNull(author.karma) || ['F', 'D'].includes(author.karma)) {
    neededActions.value = karma.interactionsLow - author.today
  } else if (['C', 'B'].includes(author.karma)) {
    neededActions.value = karma.interactionsMid - author.today
  } else if (['A'].includes(author.karma)) {
    neededActions.value = karma.interactionsHigh - author.today
  }
})
</script>

<style scoped>
p {
  margin-bottom: 1rem;
}
</style>

<template>
  <template v-if="tooltip">
    <span v-if="$helper.isNull(author.karma)">{{ $t('main.karma-null') }}</span>
    <span v-else>{{ $t('main.karma-points', { karma: author.karma }) }}&nbsp;</span>
  </template>

  <template v-else>
    <div>
      <p>
        <span>{{ $t('main.karma-contrib-not-met') }}&nbsp;</span>
        <span>{{ $t('main.karma-contrib-important') }}&nbsp;</span>
        <span>{{ $t('main.karma-contrib-missing', { count: neededActions }) }}&nbsp;</span>
        <span>{{ $t('main.karma-contrib-actions') }}</span>
      </p>

      <po-button color="primary" size="large" class="mb-2" :href="route('home')" block inertia>
        {{ $t('main.go-to-home') }}
      </po-button>
    </div>
  </template>
</template>
