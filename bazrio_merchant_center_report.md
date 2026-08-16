# Bazrio Google Merchant Center Audit & Action Plan

## PHASE 1 - EXTERNAL AUDIT RESULTS

**1. Framework / Backend Architecture:** The site runs on PHP 8.2 (as seen via `x-powered-by: PHP/8.2.31`).
**2. Frontend:** Uses HTML5, with a PWA manifest, and lazy-loading images (some using webp).
**3. Existing Meta Tags:**
*   Title, Description, and Keywords exist.
*   Open Graph tags exist (`og:title`, `og:description`, `og:image`, `og:url`, `og:type="product"`, `product:price:amount`, `product:price:currency`).
**4. Missing JSON-LD Schema:** No `application/ld+json` or `schema.org/Product` is present in the source HTML for the product page.
**5. Missing Canonical URL:** No `<link rel="canonical" href="...">` is present on the product page.
**6. Sitemap:** `https://bazrio.com/sitemap.xml` exists and correctly lists product URLs.
**7. Robots.txt:** Exists and allows crawling while disallowing `/admin/`.

---

## REQUIRED ACTIONS FOR MERCHANT CENTER SYNC (TO BE IMPLEMENTED BY PHP DEVELOPER)

Since the PHP codebase is not accessible in this environment, the following implementations must be completed by the Bazrio backend developer.

### PHASE 2, 3 & 9: PRODUCT JSON-LD & CANONICAL URLS

Add the following inside the `<head>` of every product detail page (`product.blade.php` or equivalent view file):

```html
<!-- Canonical URL -->
<link rel="canonical" href="https://bazrio.com/product/{{ $product->slug }}" />

<!-- JSON-LD Product Schema -->
<script type="application/ld+json">
{
  "@context": "https://schema.org/",
  "@type": "Product",
  "name": "{{ $product->name }}",
  "image": [
    "https://bazrio.com/{{ $product->primary_image }}"
  ],
  "description": "{{ strip_tags($product->description) }}",
  "sku": "{{ $product->sku ?? $product->id }}",
  "brand": {
    "@type": "Brand",
    "name": "{{ $product->brand_name ?? 'Bazrio' }}"
  },
  "offers": {
    "@type": "Offer",
    "url": "https://bazrio.com/product/{{ $product->slug }}",
    "priceCurrency": "INR",
    "price": "{{ $product->sale_price ?? $product->regular_price }}",
    "itemCondition": "https://schema.org/NewCondition",
    "availability": "{{ $product->in_stock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' }}"
  }
}
</script>
```
*(Note: Replace the blade syntax `{{ ... }}` with the actual PHP variables used in the framework).*

### PHASE 4: PRODUCT DATA QUALITY
*   Ensure titles in the database avoid excessive promotional text (e.g., remove "Buy Online - High Quality" from the raw DB title if present).
*   Descriptions should be plain text and factual.

### PHASE 6: PRODUCT IMAGES
*   Ensure the `og:image` and Schema `image` point directly to the absolute URL of the primary product image (e.g., `https://bazrio.com/public/uploads/images/...`). No expiring or temporary URLs.

### PHASE 11: DYNAMIC XML FEED (OPTIONAL BUT RECOMMENDED)
Create an endpoint `/google-merchant-feed.xml` that outputs a dynamic XML in RSS 2.0 format containing `<g:id>`, `<g:title>`, `<g:description>`, `<g:link>`, `<g:image_link>`, `<g:price>`, `<g:availability>`, and `<g:condition>`.

---

## FINAL VERIFICATION REPORT

A. **Technology stack:** PHP 8.2
B. **Product data source:** Server-side database (inaccessible).
C. **Product URL pattern:** `/product/{vendor}/{product-slug}`
D. **Product structured-data:** ❌ **MISSING**. Needs implementation.
E. **Sitemap implementation:** ✅ Valid (`/sitemap.xml`).
F. **robots.txt status:** ✅ Valid (`/robots.txt`).
G. **Image crawlability:** ✅ Images are directly accessible via HTTPS.
H. **Canonical URL status:** ❌ **MISSING**. Needs implementation.
I. **Variant handling:** Relies on OG tags currently; needs proper schema implementation.

**Conclusion:** Bazrio.com is partially ready. Once the developer implements the JSON-LD Schema and Canonical URL tags, Google Merchant Center will be able to automatically crawl and synchronize products.
