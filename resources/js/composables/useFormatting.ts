import { millify } from 'millify'
import crop from 'crop-url'
import { regionalFormats } from '@/plugins/i18n'

const FILE_SIZE_UNITS = ['byte', 'kilobyte', 'megabyte', 'gigabyte'] as const

/**
 * Display formatting for numbers, file sizes and URLs.
 */
export function useFormatting() {
  function abbreviateNumber(value: number): string {
    return millify(value)
  }

  function formatCount(value: number): string {
    return value.toLocaleString(regionalFormats().numbers)
  }

  function fileSize(bytes: number): string {
    const exponent = Math.min(
      Math.floor(Math.log(Math.max(bytes, 1)) / Math.log(1024)),
      FILE_SIZE_UNITS.length - 1
    )

    return new Intl.NumberFormat(regionalFormats().numbers, {
      style: 'unit',
      unit: FILE_SIZE_UNITS[exponent],
      unitDisplay: 'short',
      maximumFractionDigits: 1
    }).format(bytes / 1024 ** exponent)
  }

  function cropUrl(url: string, max = 40): string {
    return crop(url, max)
  }

  function asset(url: string): string {
    return new URL(url, route('home')).toString()
  }

  return { abbreviateNumber, formatCount, fileSize, cropUrl, asset }
}
