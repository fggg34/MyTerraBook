# Deploy notes

Do this on the server after `git pull` of `main`. Do not run it from the design machine.

## 1. Code

```bash
git fetch origin
git checkout main
git pull origin main
```

The backup of `main` before this redesign is the tag `pre-redesign-backup`.

## 2. Backend

From the Laravel app directory (`backend` on this repo, often `/backend` on the live site):

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan db:seed --class=Database\\Seeders\\BlogPostSeeder --force
php artisan storage:link
php artisan optimize:clear
```

`BlogPostSeeder` only creates a post when its slug is missing. If a post already exists and its `blocks` column is empty, the seeder fills `blocks` and leaves the title, body, and other edited fields alone.

### Migrations in this release

- `backend/database/migrations/2026_10_28_200000_add_article_author_fields_to_blog_posts.php`
  - `blog_posts.author_name`, `author_role`, `author_photo`, `tags` (all nullable)
- `backend/database/migrations/2026_10_28_201000_add_blocks_to_blog_posts.php`
  - `blog_posts.blocks` (nullable JSON)
- `backend/database/migrations/2026_10_28_201100_add_unsubscribe_reason_to_newsletter_subscribers.php`
  - `newsletter_subscribers.unsubscribe_reason` (nullable)

They were rolled back and reapplied on the local database.

## 3. Frontend

From `frontend`:

```bash
npm ci
npm run build
```

The production files are written to `frontend/dist`. Publish that folder as the storefront document root (`index.html`, `assets/`, `calendar-embed.html`). A backend pull does not update the storefront.

## 4. Images that must exist

These are files in the frontend `public` folder, shipped with the build, not Laravel storage uploads:

- `/figma/login-hero.png` (coming soon background and login photo)
- `/figma/about-camper.png`, `/figma/about-car.png`, `/figma/about-stay.png`
- `/figma/camper-card.png` (article route card)
- `/figma/logo.svg`, `/figma/logo-light.svg`, `/figma/logo-auth.svg`

No new files have to be copied into `storage/app/public` for the pages to render. Uploaded Site content and post images still need the public storage link from `php artisan storage:link`.

## 5. Cache

```bash
php artisan optimize:clear
```

If the admin still shows the previous Site content cards, open:

`https://myterrabook.com/backend/refresh-admin-cache.php?k=tb-admin-refresh`

Then hard-refresh the browser. Admin CSS is cached for up to four hours.

## 6. Environment

No new environment variables. Newsletter unsubscribe and the contact form keep using the existing mail and `app.frontend_url` settings.

## 7. Rollback

Find the merge commit on `main` (`Redesign: admin + public frontend from Figma`) and revert it. That creates a new commit and does not rewrite history:

```bash
git revert -m 1 <merge-commit-sha>
git push origin main
```

To return the code to the pre-redesign `main` without a revert commit, check out the tag in a maintenance window. That moves the working tree only; do not force-push `main`.

```bash
git checkout pre-redesign-backup
```

After a code rollback, roll the three migrations back only if the new columns are empty and you are sure no editor has saved `blocks` or an unsubscribe reason:

```bash
php artisan migrate:rollback --step=3
```

Then rebuild and republish `frontend/dist`, and run `php artisan optimize:clear`.
