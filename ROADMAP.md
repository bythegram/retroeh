# RetroEh! Plugin — Block Roadmap

This document outlines potential Gutenberg blocks that could be built on top of the
[RetroAchievements API](https://github.com/RetroAchievements/api-docs/tree/main).
All blocks would follow the same patterns already established in the plugin: server-side
rendering, secure API-key storage in `wp_options`, 1-hour transient caching, and
full WordPress input/output sanitisation.

---

## ✅ Implemented Blocks

| Block | Shortcode | API Endpoint | Description |
|---|---|---|---|
| `retroeh/game-display` | `[retroeh_game_display]` | `API_GetGame.php` / `API_GetUserRecentlyPlayedGames.php` | Shows box art, in-game screenshot background, console, and last-played time for a specific game or a user's most-recently played game. |
| `retroeh/user-profile` | `[retroeh_user_profile]` | `API_GetUserProfile.php` | Shows a player profile card: avatar, username, motto, hardcore/true-point totals, member-since date, and rich-presence activity. |

---

## 🗺️ Planned / Future Blocks

### 1. Recent Achievements Feed — `retroeh/recent-achievements`
**API:** `API_GetUserRecentAchievements.php`

Display a feed of the most recently unlocked achievements for a given user. Each
achievement entry would show the badge image, achievement name, description, points
value, game title, and unlock date/time.

**Block attributes:** `username`, `count` (number of achievements to show, default 5)

**Use case:** Embed a live activity feed on a streamer's "About" page or gaming blog
sidebar to showcase recent progress.

---

### 2. Game Achievements Showcase — `retroeh/game-achievements`
**API:** `API_GetGameExtended.php`

Display a grid or list of all achievements for a specific game, including badge images,
titles, descriptions, point values, and unlock percentages. Could be filtered to show
only obtained/not-obtained achievements when combined with a username.

**Block attributes:** `game_id`, `username` (optional, to highlight earned achievements)

**Use case:** Create a game-specific page that documents every achievement alongside
a walkthrough or guide.

---

### 3. Game Leaderboard — `retroeh/game-leaderboard`
**API:** `API_GetGameRankAndScore.php`

Show the top hardcore-point scorers or latest masters for a specific game. Displays
rank, username, points, and date achieved in a styled table.

**Block attributes:** `game_id`, `count` (rows to display, default 10)

**Use case:** Highlight competition and community ranking on a game's dedicated page.

---

### 4. Achievement of the Week — `retroeh/achievement-of-week`
**API:** `API_GetAchievementOfTheWeek.php`

Feature the current RetroAchievements community "Achievement of the Week" with its
badge, name, description, game title, console, and point value. Auto-refreshes every
hour via transient cache.

**Block attributes:** *(none — auto-populated from the API)*

**Use case:** Add a dynamic sidebar or homepage widget that always highlights the
current community event.

---

### 5. User Awards / Badges — `retroeh/user-awards`
**API:** `API_GetUserAwards.php`

Display a user's earned site awards and badges (e.g. Mastery awards, Event awards)
in a visually rich grid. Each badge shows the award icon, title, and date earned.

**Block attributes:** `username`

**Use case:** Showcase a player's accomplishments on a personal gaming profile page.

---

### 6. Completion Progress — `retroeh/completion-progress`
**API:** `API_GetUserCompletionProgress.php`

Render a progress dashboard showing a user's completion percentage across all games
they have played, with sorting options (by completion %, last played, alphabetical).

**Block attributes:** `username`, `count` (games per page), `sort`

**Use case:** Embed a personal stats widget on a gaming portfolio or blog profile.

---

### 7. Custom Leaderboard Entries — `retroeh/leaderboard-entries`
**API:** `API_GetLeaderboardEntries.php`

Display entries for a specific leaderboard within a game (e.g. speed-run times,
high scores). Shows rank, username, score/time, and submission date.

**Block attributes:** `leaderboard_id`, `count`

**Use case:** Embed competition standings directly on a page dedicated to a particular
game or leaderboard challenge.

---

### 8. Top-Ranked Users — `retroeh/top-users`
**API:** `API_GetTopTenUsers.php`

Show the global top-10 (or configurable number) of highest-ranked RetroAchievements
users, with their points and rank badge.

**Block attributes:** *(none — auto-populated)*

**Use case:** Add a community leaderboard widget to a retro gaming club or community site.

---

### 9. User Game Rank & Score — `retroeh/user-game-rank`
**API:** `API_GetUserGameRankAndScore.php`

Display how a specific user ranks on a specific game — their score, position on the
leaderboard, and what percentage of players they outrank.

**Block attributes:** `username`, `game_id`

**Use case:** Let a player embed their personal ranking for a favourite game on their
profile or gaming resume.

---

### 10. Want-to-Play List — `retroeh/want-to-play`
**API:** `API_GetUserWantToPlayList.php`

Show a stylised list of games a user has flagged as "Want to Play" on RetroAchievements,
with box art, console name, and achievement count.

**Block attributes:** `username`, `count`

**Use case:** Share a public gaming backlog or wishlist on a blog or streamer page.

---

## Implementation Notes

- All future blocks will share the existing `retroeh-block` editor script handle to
  minimize asset weight.
- New API endpoints that return paginated data (e.g. completion progress, want-to-play)
  will default to sensible limits configurable via block attributes.
- Caching TTL will be scoped per user/game and API-key hash, consistent with the existing
  transient pattern.
- Each block will be implemented as a dynamic (server-side-rendered) block with `save()`
  returning `null`, exactly like the existing blocks.
- Block attributes will be declared in both PHP (`register_block_type`) and JavaScript
  (`registerBlockType`) to stay in sync.
