import { describe, expect, it } from 'vitest'
import {
  findIconDefinition,
  type IconName,
  type IconPrefix
} from '@fortawesome/fontawesome-svg-core'
import { aliases } from 'vuetify/iconsets/fa-svg'
import siteSettings from '../../../json/settings.default.json'
import '../fontawesome'

const ICON_PATTERN = /\b(fa[srb]?) fa-([a-z0-9-]*[a-z0-9])(?![a-z0-9$-])/g

const sources = import.meta.glob<string>('../../**/*.{vue,ts}', {
  query: '?raw',
  import: 'default',
  eager: true
})

// Icons whose class string is assembled at runtime, so they never appear literally.
const DYNAMIC_ICONS = [
  ...['heart', 'bookmark', 'comment'].flatMap((name) => [`fas fa-${name}`, `far fa-${name}`]),
  ...['twitter', 'threads', 'instagram', 'facebook', 'youtube', 'goodreads'].map((network) =>
    network === 'twitter' ? 'fab fa-x-twitter' : `fab fa-${network}`
  )
]

// Vuetify maps this alias to an icon that doesn't exist in any FontAwesome pack.
const NONEXISTENT_ICONS = ['fas fa-fullscreen']

function iconsIn(text: string): string[] {
  return [...text.matchAll(ICON_PATTERN)].map(([icon]) => icon)
}

const usedIcons = new Set([
  ...Object.entries(sources)
    .filter(([path]) => !path.includes('__tests__'))
    .flatMap(([, source]) => iconsIn(source)),
  ...iconsIn(JSON.stringify(aliases)),
  ...iconsIn(JSON.stringify(siteSettings)),
  ...DYNAMIC_ICONS
])

describe('fontawesome library', () => {
  it.each([...usedIcons].filter((icon) => !NONEXISTENT_ICONS.includes(icon)))(
    'registers %s',
    (icon) => {
      const [prefix, iconName] = icon.split(' fa-') as [IconPrefix, IconName]

      expect(findIconDefinition({ prefix, iconName })).toBeDefined()
    }
  )
})
