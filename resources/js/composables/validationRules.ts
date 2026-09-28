// Username and password patterns shared by the forms that create or sign in
// accounts, and the @mention rule comments use. They mirror User::USERNAME_PATTERN,
// User::PASSWORD_PATTERN and User::MENTION_PATTERN, which stay the authority:
// change both together.

export const USERNAME_PATTERN = '^(?!.*\\.\\.)(?!.*\\.$)[^\\W][\\w.]{0,44}$'

export const PASSWORD_PATTERN =
  '(?=^.{8,}$)((?=.*\\d)|(?=.*\\W+))(?![.\\n])(?=.*[A-Z])(?=.*[a-z]).*$'

const MENTION_PATTERN = /\B@(\w[\w.]{0,44})/g

/**
 * The usernames @mentioned in a text, in order and without repeats. A
 * sentence's closing dot is trimmed, since no username ends with one.
 */
export function mentionedUsernames(text: string): string[] {
  const usernames = [...text.matchAll(MENTION_PATTERN)].map((match) =>
    (match[1] ?? '').replace(/\.+$/, '')
  )

  return [...new Set(usernames)]
}
