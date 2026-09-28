import { getCurrentInstance, onMounted, ref } from 'vue'
import { intlFormatDistance } from 'date-fns'
import { regionalFormats } from '@/plugins/i18n'

// Matches config/app.php. The SSR server (UTC) and the browser would format the
// same timestamp to different calendar days, which breaks hydration, so both
// render in this zone until the app has hydrated.
const SERVER_TIME_ZONE = 'UTC'

// Shared by every component: once one has mounted, the page has hydrated
const isHydrated = ref(false)

/**
 * Date formatting: in the server's time zone until the page has hydrated,
 * then in the viewer's own, re-rendering the dates shown so far.
 */
export function useDates() {
  if (getCurrentInstance() !== null && isHydrated.value === false) {
    onMounted(() => {
      isHydrated.value = true
    })
  }

  function displayTimeZone(): string | undefined {
    return isHydrated.value ? undefined : SERVER_TIME_ZONE
  }

  function toLocaleDate(date: string | number | Date): string {
    return new Date(date).toLocaleDateString(regionalFormats().dates, {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      timeZone: displayTimeZone()
    })
  }

  function toLocaleDateTime(date: string | number | Date): string {
    return new Date(date).toLocaleString(regionalFormats().dates, {
      dateStyle: 'medium',
      timeStyle: 'medium',
      timeZone: displayTimeZone()
    })
  }

  function relativeDate(date: string | number | Date): string {
    return intlFormatDistance(new Date(date), new Date(), { locale: regionalFormats().language })
  }

  return { toLocaleDate, toLocaleDateTime, relativeDate }
}
