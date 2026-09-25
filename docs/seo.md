# SEO configuration

The shared public layout provides escaped titles and descriptions, canonical links,
Open Graph and Twitter metadata, and Organization/WebSite/WebPage JSON-LD.
CMS pages use their existing meta title and meta description fields; empty values
fall back to the page title and a plain-text excerpt of its content.

`SEO_URL` defaults to `https://menyanbo.org`. Set it to the preferred public origin
if the production domain differs. Update the Sitemap line in `public/robots.txt`
to match when changing domains. Canonical URLs omit tracking and filter queries;
gallery pagination retains `page` so older photos remain discoverable.

`/sitemap.xml` dynamically lists the main public sections, published CMS pages,
and active notices. Individual member profiles and the directory are not added
to the sitemap. Existing public profile indexing remains enabled. Account,
admin, JSON data, and request pages receive noindex headers. These directives
control search indexing; they do not replace authentication or access controls.
Robots.txt allows crawling so crawlers can read the noindex directives.

Deployment:

1. Deploy the application and views through the normal deployment process.
2. Rebuild configuration and view caches if the deployment uses them.
3. Verify `/sitemap.xml`, `/robots.txt`, and the page source on the public domain.
4. Submit `https://menyanbo.org/sitemap.xml` in Google Search Console using an
   account with access to the property. Submission and indexing are external
   follow-up steps; they are not performed by these code changes.

The existing square organization logo is used for social previews. CMS editors
should write useful page-specific descriptions and descriptive gallery titles.
No database migrations or new packages are required.

Validation: `php artisan test --filter=SeoTest` and `php artisan view:cache`.

References:
- https://developers.google.com/search/docs/crawling-indexing/sitemaps/build-sitemap
- https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag
