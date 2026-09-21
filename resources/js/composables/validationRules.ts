// Input patterns shared by the forms that create or sign in accounts. They
// mirror the rules in app/Http/Controllers/Auth/RegisteredUserController.php,
// which stays the authority: change both together.

export const USERNAME_PATTERN = '^(?!.*\\.\\.)(?!.*\\.$)[^\\W][\\w.]{0,44}$'

export const PASSWORD_PATTERN =
  '(?=^.{8,}$)((?=.*\\d)|(?=.*\\W+))(?![.\\n])(?=.*[A-Z])(?=.*[a-z]).*$'
