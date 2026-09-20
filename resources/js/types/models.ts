// Structural types for domain objects sent down as Inertia page/shared
// props. There is no API Resource layer on the PHP side (controllers pass
// Eloquent models/arrays straight to Inertia::render()), so the exact set of
// fields present varies by endpoint — these types cover the fields actually
// read by the frontend, with fields that aren't consistently present marked
// optional, rather than asserting a single canonical shape the backend
// doesn't actually guarantee.

export interface UserLike {
  id?: number
  username: string
  name?: string | null
  last_name?: string | null
  avatar?: string | null
  extra_info?: {
    avatar?: string | null
    social?: Record<string, string>
  } | null
  karma?: 'A' | 'B' | 'C' | 'D' | 'F' | null
  writings_count?: number
}

// The full profile shape returned by UsersController::show() — a User
// model select() with several extra_info->x AS x JSON extractions (raw
// JSON text, not auto-decoded, hence `social` staying a JSON string) plus
// withCount() aggregates.
export interface User extends UserLike {
  id: number
  profile_views: number
  aura: string
  writings_count: number
  awards_count: number
  likes_count: number
  comments_count: number
  shelf_count: number
  // `bio` and `location` are selected by both index() and show(); the rest
  // only by UsersController::show() — optional to match.
  bio?: string
  location?: string
  created_at?: string
  social?: string
  website?: string
  occupation?: string
  interests?: string
}

// CommentsController::index()'s author select() + withCount(['likes']).
export interface Comment {
  id: number
  message: string
  created_at: string
  author: UserLike
  likes_count: number
}

export interface Paginated<T> {
  data: T[]
  next_page_url: string | null
}

// UsersNotificationsController::index() resolves these two relations
// manually onto each Laravel notification (id is a UUID string, not a DB
// auto-increment integer).
export interface AppNotification {
  id: string
  type: string
  created_at: string
  notifier_user: UserLike | null
  notifier_writing: { id: number; title: string; slug: string } | null
}

export interface CategoryLike {
  id: number
  name: string
  slug: string
  description?: string | null
  writings_count?: number
}

export interface TagLike {
  id: number
  name: string
  slug: string
  writings_count?: number
}

// WritingsController::show()/index() shapes: a full (unselected) Writing
// model plus withCount() aggregates and a narrow `author` relation select.
// Related-writings lists (`related.from_author`/`from_category`) reuse this
// same type without the count fields, hence those staying optional.
export interface Writing {
  id: number
  title: string
  slug: string
  text: string
  created_at: string
  views: number
  aura: string
  home_posted_at?: string | null
  extra_info?: {
    cover?: string
    link?: string
  } | null
  author: UserLike
  categories?: CategoryLike[]
  tags?: TagLike[]
  likes_count: number
  comments_count: number
  shelf_count: number
}
