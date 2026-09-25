# Frontend languages

Public pages support Nepali (`?lang=ne`, the default) and English (`?lang=en`).
The header switch keeps the current page and filters. A session preference keeps
forms and subsequent visits in the selected language. Public navigation also
carries the language in its URL so shared links open in the intended language.

The admin panel is excluded. The public `/members` directory now renders
`resources/views/members/index.blade.php`; its former admin-folder template is
untouched. Shared camera and marriage components translate only on frontend
requests.

Translations are maintained in `lang/en.json` and `lang/ne.json`. Nepali validation,
authentication, and pagination messages live in `lang/ne/`. `FrontendLocale::text`
provides a source-text fallback and preserves the existing admin wording.

Names, uploaded documents, biographies, and CMS-authored articles/notices keep
their stored language. No machine translation or database content rewriting is
performed. Known navigation labels are translated; custom menu labels can be
added to the translation catalogs.

The frontend emits matching HTML language, social locale, structured-data language,
canonical URLs, and English/Nepali alternate links. The sitemap lists both variants.
No migrations or additional packages are needed. Deploy the application, views,
language catalogs, and compiled build assets together and refresh normal caches.

Checks: `php artisan test`, `php artisan view:cache`, and `npm run build`.
