<style scoped>
* {
  box-sizing: border-box;
}

.text-h6 {
  text-align: center;
  position: relative;
}

.text-h6:after {
  position: absolute;
  content: '';
  animation: Dots 2s cubic-bezier(0, 0.39, 1, 0.68) infinite;
}

.loader {
  margin: 0 auto 30px;
}

.book {
  border: 2px solid rgb(var(--v-theme-on-surface));
  width: 60px;
  height: 45px;
  position: relative;
  perspective: 150px;
  background: rgba(var(--v-theme-primary), 0.3);
}

.page {
  display: block;
  width: 30px;
  height: 45px;
  border: 2px solid rgb(var(--v-theme-on-surface));
  border-left: 1px solid rgb(var(--v-theme-on-surface));
  margin: 0;
  position: absolute;
  right: -2px;
  top: -2px;
  overflow: hidden;
  background: rgba(var(--v-theme-primary), 0.6);
  transform-style: preserve-3d;
  transform-origin: left center;
}

.book .page:nth-child(1) {
  animation: pageTurn 1.2s cubic-bezier(0, 0.39, 1, 0.68) 1.6s infinite;
}

.book .page:nth-child(2) {
  animation: pageTurn 1.2s cubic-bezier(0, 0.39, 1, 0.68) 1.45s infinite;
}

.book .page:nth-child(3) {
  animation: pageTurn 1.2s cubic-bezier(0, 0.39, 1, 0.68) 1.2s infinite;
}

/* Page turn */

@keyframes pageTurn {
  0% {
    transform: rotateY(0deg);
  }

  20% {
    background: rgba(var(--v-theme-primary), 0.7);
  }

  40% {
    background: rgba(var(--v-theme-primary), 0.5);
    transform: rotateY(-180deg);
  }

  100% {
    background: rgba(var(--v-theme-primary), 0.3);
    transform: rotateY(-180deg);
  }
}

/* Dots */

@keyframes Dots {
  0% {
    content: '';
  }

  33% {
    content: '.';
  }

  66% {
    content: '..';
  }

  100% {
    content: '...';
  }
}
</style>

<template>
  <div class="d-flex align-center h-100">
    <div class="w-100">
      <div class="loader book">
        <figure class="page"></figure>
        <figure class="page"></figure>
        <figure class="page"></figure>
      </div>

      <p class="text-h6 text-uppercase">{{ $t('main.reading') }}</p>
    </div>
  </div>
</template>
