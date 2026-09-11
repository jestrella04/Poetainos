/**
 * Social network link building and sharing.
 */
export function useSocialLinks() {
  function socialLink(user: string, network: string): string {
    let url = ''

    switch (network) {
      case 'twitter':
        url = `https://twitter.com/${user}`
        break

      case 'threads':
        url = `https://threads.net/@${user}`
        break

      case 'instagram':
        url = `https://instagram.com/${user}`
        break

      case 'facebook':
        url = `https://facebook.com/${user}`
        break

      case 'youtube':
        url = `https://youtube.com/user/${user}`
        break

      case 'goodreads':
        url = `https://www.goodreads.com/${user}`
        break

      case 'telegram':
        url = `https://t.me/${user}`
        break
    }

    return url
  }

  function shareLinks(
    title: string,
    url: string
  ): Array<{ name: string; url: string; icon: string }> {
    const facebookBaseUrl = `https://facebook.com/sharer/sharer.php?u=${url}`
    const twitterBaseUrl = `https://twitter.com/intent/tweet/?text=${title}&url=${url}`
    const whatsappBaseUrl = `whatsapp://send?text=${title}%20${url}`
    const telegramBaseUrl = `https://t.me/share/url?url=${url}&text=${title}`

    return [
      {
        name: 'Facebook',
        url: facebookBaseUrl,
        icon: 'fab fa-facebook-f'
      },
      {
        name: 'Twitter',
        url: twitterBaseUrl,
        icon: 'fab fa-x-twitter'
      },
      {
        name: 'Whatsapp',
        url: whatsappBaseUrl,
        icon: 'fab fa-whatsapp'
      },
      {
        name: 'Telegram',
        url: telegramBaseUrl,
        icon: 'fab fa-telegram'
      },
      {
        name: 'copy',
        url: '#',
        icon: 'far fa-clone'
      }
    ]
  }

  function socialIcon(): Record<string, string | undefined> {
    return {
      twitter: 'fab fa-x-twitter',
      threads: 'fab fa-threads',
      instagram: 'fab fa-instagram',
      facebook: 'fab fa-facebook-f',
      youtube: 'fab fa-youtube',
      telegram: 'fab fa-telegram'
    }
  }

  return { socialLink, shareLinks, socialIcon }
}
