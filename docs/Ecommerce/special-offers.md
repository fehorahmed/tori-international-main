# Special Offers

## Admin workflow

Ecommerce → Special Offers (`/admin/ecommerce/special-offers`) lists campaigns as Scheduled, Live, Expired or Inactive. Admins or users with `ecommerce_product_edit` may create/edit campaigns. Set name, optional unique URL slug, start/end in Bangladesh time, and campaign status. Add active, visible products or packages and enter each row's fixed-Tk or percentage discount and active state. The form previews the price. The backend independently validates every row under transaction/locks: unique product per campaign, positive discount, positive final price, and end after start. Campaign schedule is converted from Bangladesh time to the application's configured timezone for storage/comparison.

Multiple campaigns can run simultaneously. Removing an existing row disables it rather than deleting the historical offer ID. Existing row IDs are preserved on updates. There is no destructive campaign deletion endpoint. Expiry needs no cron job: start-inclusive/end-exclusive time conditions are checked on the server.

## Storage

Migration `2026_11_16_090000_create_offer_campaigns.php` creates:

- `offer_campaigns`: ID, name, unique slug, starts_at, ends_at, is_active, created_by and timestamps.
- `offer_campaign_products`: ID, campaign FK, product ID, discount_type (`fixed`/`percentage`), decimal discount_value, is_active and timestamps; unique campaign/product and indexed product/status.

No product images, stock or balances are copied into campaign tables. Existing inventory/package component stock remains authoritative.

## Pricing

[OfferService](../../app/Services/Offers/OfferService.php) resolves active offers and [DiscountService](../../app/Services/Membership/DiscountService.php) compares their monetary discounts with general and eligible member pricing. All are based on the existing original Selling Price (`max(selling_price, old_selling_price)`). The lowest price wins; discounts do not stack. Equal monetary offers use stable entry ID order. General/member pricing retains priority on a tie with an offer.

A discount that becomes invalid after a product price edit (zero saving or no positive final price) is ignored and excluded from the Special Offers listing. Catalog sorting/range filtering uses a SQL expression matching final campaign/member pricing. Catalog lists, details, cart, wishlist, compare and checkout all resolve through the shared pricing service. Special Offer badges indicate an active offer even if a member receives a better price.

## Frontend and API

- `/special-offers`: all active products/packages, unique product rows, campaign filters, pagination and countdown.
- `/special-offers/{slug}`: products participating in that active campaign; a better overlapping campaign can still determine the actual price.
- `GET /api/special-offers?campaign={slug}&page=1&per_page=24`: active campaign metadata and a paginated `products` object. `per_page` is 1–60. Public product fields only; no purchase costs are exposed. Products include the normal presented price fields, `discount_source`, `offer_discount`, and `special_offer` metadata (offer/campaign IDs, campaign name/slug, type/value, expiry ISO time).

Desktop and mobile catalog navigation both include Special Offers. The page refreshes on offer/campaign expiry. Existing Products/Packages pages retain their separate catalog types and show the same effective price.

## Checkout and accounting

Cart always reprices from server data. Ecommerce checkout sends `expected_total` for COD and membership payment. If the current total changed, checkout rejects before saving/debiting; the frontend refreshes the cart for another review. Membership minimum balance, stock validation and account protections remain in place.

Order JSON line snapshots add `discount_source`, `special_offer` (the winning offer's type/value/campaign metadata), and `offer_discount_total`, alongside existing original unit price, general/member discounts and net line total. Campaign edits/expiry cannot reprice saved orders or retries. Order accounting allocates the resulting net price as before. Membership payment captures the discounted total plus shipping immediately; cancellation refunds only the amount captured and remains idempotent.

Validation: ProductPackageTest includes best-price/overlap rules, boundaries/expiry/cart repricing, public listing/filter/privacy, net catalog price filtering, admin persistence/invalid discount handling, COD stale-total rejection and immutable snapshots, and membership discounted capture/refund.

## Admin DataTables

Special Offers and Product Packages use server-side DataTables with the shared Products-page controls (search, sorting, length selection, responsive columns, export buttons and pagination). JSON endpoints are the respective `/datatable` paths under `/admin/ecommerce/special-offers` and `/admin/ecommerce/packages`. The same page permissions and existing package/order branch restrictions apply to every Ajax request. Names and user-controlled content are escaped; only rendered links/component cells use explicitly escaped HTML. Computed stock/status/component cells are not SQL-sortable. Ordinary Blade pagination is removed so the table can search across the full permitted dataset.

## Campaign MRP and banner

The campaign product table displays each selected product/package's current MRP (the original Selling Price used for discounts: `max(selling_price, old_selling_price)`) beside its discount and offer-price preview. It is read-only and updates when a product is selected.

The multipart create/edit form accepts an optional campaign banner: JPG/PNG/WebP, maximum 5 MB and 4096×4096 pixels, recommended 1600×500. Uploads are converted to WebP using the existing image upload flow and stored under `public/uploads/offer-campaigns`. Saving without a new file preserves the current banner. Replacing/removing it deletes the previous owned file after successful persistence; a failed campaign save cleans up its new upload. The migration `2026_11_17_120000_add_banner_to_offer_campaigns.php` adds nullable `offer_campaigns.banner_image`.

`GET /api/special-offer-banners` exposes only currently active campaigns with a banner and at least one active, published, non-deleted product/package with a valid offer price. It returns `banners` containing campaign ID/name/slug, image_url and ISO expiry. Scheduled, expired, inactive or empty campaigns are excluded. The home page displays these banners below its main hero, linked to `/special-offers/{slug}`. Several campaign banners can appear together; the section is hidden if none are eligible. Client expiry hides ended banners and refreshes eligibility every 30 seconds.
