import { describe, expect, it } from 'vitest'
import { useSocialLinks } from '../useSocialLinks'

const { socialLink, socialNetworkName } = useSocialLinks()

describe('socialLink', () => {
  it('builds a profile URL for a known network', () => {
    expect(socialLink('jane', 'twitter')).toBe('https://twitter.com/jane')
  })

  it('returns an empty string for an unknown network', () => {
    expect(socialLink('jane', 'myspace')).toBe('')
  })
})

describe('socialNetworkName', () => {
  it('names a known network by its brand', () => {
    expect(socialNetworkName('twitter')).toBe('X (Twitter)')
  })

  it('falls back to the key for an unknown network', () => {
    expect(socialNetworkName('myspace')).toBe('myspace')
  })
})
