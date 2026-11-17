# Product Packages — first version

## Administration

Ecommerce Management → **Product Packages** → Create Package. Select at least two distinct active single products, their whole quantities, package name/image/category/unit, fixed selling price, fulfilment branch/warehouse, active status and ecommerce visibility. Existing `product_type` (raw/finished classification) is preserved; the independent `catalog_type` distinguishes `single` and `package`.

Packages have no inventory rows of their own. Availability is the minimum whole package count that can be assembled from all component quantities in the configured warehouse. Normal products can still be sold individually. Package forms validate nested packages, duplicate products, inactive components and branch access. Non-admin editors use their active branch warehouses. Deactivate/hide packages instead of deleting them through ordinary product screens.

## Buying and fulfilment

The package appears through existing product catalog APIs. Product detail shows contents and current availability. Cart quantity/price comes from the server. General product/package discounts apply to everyone. Separate member discount percentages apply only when the member meets the configurable savings balance minimum. The first cart insertion starts at zero before adding the requested quantity, fixing the old default-quantity double increment. `discount_percent` is derived for cart responses because the existing cart table does not contain that column.

Authenticated customers use `POST /api/package-checkout`; the ordinary ecommerce order endpoint also routes package carts through this validated path. COD, immediate Membership Account Balance and Membership Points payments are supported; coupons and external payment gateways are not enabled for packages yet. Membership discounts use `products.member_discount_percent` and the configured eligibility balance. The member rate is applied to original Selling Price instead of general discount, never stacked. A nonzero member rate must exceed the general rate. The current storefront's inside/outside city charges (Tk 50/100) are checked against a server whitelist. Packages in one checkout must use the same fulfilment warehouse. Single products can accompany them and are sourced from that warehouse.

The server recomputes all product prices, ignores client price/discount values, validates active/published products and checks combined component demand (including components also bought individually). It stores a sale order plus immutable package checkout snapshot. The browser retains a request UUID across network failures; sender/customer+request key and payload hash make retries idempotent. No money is collected or savings balance deducted during COD checkout.

Ecommerce Management → **Package Orders** shows active-branch orders. Admin confirmation locks order, customer, warehouse, branch and inventory rows; it rechecks stock, expands the saved component snapshot into the normal `sale_order_items`, deducts component batches, creates inventory logs and a balanced sales/COGS journal. Package price is allocated to components; unit-cent remainders are split so component selling totals equal the exact saved package amount. Delivery income is included in journal revenue. Confirmation retries do not repeat stock or postings. Existing customer payment/receivable accounting remains responsible for recording COD collection; dispatch/delivery status can be updated separately from the Package Orders page.

Pending and confirmed orders can be cancelled before dispatch; confirmed cancellations restore stock and reverse the sales/COGS journal. Dispatched/delivered orders require a separate returns workflow. Confirmed package sales cannot be deleted/repriced through ordinary ecommerce/general sale or voucher editing paths. Package returns are blocked in ordinary per-product return processing until a dedicated full-package return workflow is implemented.

## Data

- `products`: `catalog_type`, `package_warehouse_id`; existing fields hold package name, image and price.
- `product_package_items`: package product, component product and quantity per package.
- `package_order_checkouts`: customer/warehouse/sale order, UUID/hash, billing, package/component/price snapshot, shipping method/charge and workflow state.
- Existing `sale_orders`, `sale_order_items`, `inventories`, `inventory_logs`, `account_heads`, `vouchers`, `transactions` are used for ERP fulfilment and accounting. No parallel stock or wallet balance is introduced.

The package order receipt requires the owning customer session and can be viewed/printed. Package order details never use the broken legacy ecommerce `products` relationship or nonexistent billing columns on `sale_orders`; billing and package rows come from the checkout snapshot.

## Deliberate limits

- Component quantities are whole units in this version; fractional package recipes are not supported.
- Checkout checks current stock but does **not reserve** it. Pending orders may become unavailable before admin confirmation; no stock can be deducted below availability during package confirmation. Inventory reservations and expiry are a future workflow.
- Timed package offers, stacking discounts, preorder and full-package returns remain separate work. Membership eligibility uses Transaction Settings → Membership Discount and is checked before payment.
- Package sales are currently an ecommerce workflow; ordinary POS/general sales cannot manufacture standalone package stock.

Migration: `2026_11_12_090000_add_product_packages`. Tests use rollback transactions and disposable fixture records without resetting the database. They cover availability, server pricing, nested packages, shared demand, snapshots, idempotency, stock rollback, component allocation, balanced accounting, cart quantity, admin creation and customer ownership.

## Membership Account payment

Choose **Membership Account Balance** at checkout for products, packages or mixed carts. The browser refreshes `/api/user` savings/available amounts and asks for confirmation. `POST /api/order` and `/api/package-checkout` accept `payment_method: "membership"` and require a UUID `request_key` plus `expected_total` (the displayed total including delivery); a changed server total is rejected before a debit. Server prices, stock and shipping rules remain authoritative. Existing COD behavior is unchanged.

Order creation, principal debit, journals, payment audit record and paid/due changes commit together. Exactly one active Shomriddhi savings account and an active savings ledger are required. The payment must fit after the protected membership minimum and active cash-out reservations. Request UUID/hash retries return the existing order and cannot debit twice. Registration fees are collected by funding workflows, not again at purchase or refund.

Accounting at checkout is DR customer savings liability / CR Customer Advance Received, not immediate sales revenue. Across branches the originating and fulfilment branches post balanced inter-company payable/receivable journals and a completed settlement. Required active global reserved heads: Customer Advance Received (15), and inter-company receivable/payable (4/5) for cross-branch payments. Missing ledgers cause an atomic validation failure. No separate wallet balance is introduced.

Confirmation posts the usual AR/revenue and COGS/inventory entries, plus DR Customer Advance Received / CR customer AR for the prepaid amount. It does not debit savings again. Pending account-paid orders may still run out of stock because inventory reservation is not implemented; an admin can cancel and refund them.

**Ecommerce → Orders → View order → Cancel order** refunds the exact captured full amount (including delivery) to the original savings account. Cancellation and refund are atomic and idempotent. For a confirmed order before dispatch, inventory quantities/logs and the complete confirmation journal are reversed in the same transaction. Original journals and payment records are retained; refunds use opposite entries and cross-branch reversal settlements. No automatic registration fee is collected from refunds. Refunding an existing payment does not require the savings account to remain active. Dispatched/delivered orders cannot be cancelled through this endpoint.

`membership_order_payments` has one unique checkout record with captured amount/account, journal IDs, optional settlement and refund journal IDs/status/time. Migration: `2026_11_14_140000_create_membership_order_payments`. Savings history shows separate bilingual ecommerce payment/refund rows. Ordinary voucher edit/delete, legacy ecommerce order deletion and ordinary withdrawal deletion cannot remove these system entries. No customer cancellation API or partial-refund/returns flow is introduced.

Special Offers now apply to both individual products and packages through shared pricing. See [Special Offers](./special-offers.md) for campaign setup, time limits, best-discount selection and checkout snapshots.


All product and package orders use the existing Ecommerce → Orders list. The old `/admin/ecommerce/package-orders` list URL redirects there; package order details, confirmation, cancellation/refund and delivery actions remain available through View order.


## Membership Points checkout

Select `points` to pay the full MRP plus delivery without general, member, special offer or coupon discounts. The server calculates MRP as the existing regular price (`max(selling_price, old_selling_price)`), disregarding client prices. Submit `point_value`, `expected_total` and a stable UUID `request_key`; changed rates/totals require review. Referral settings must be enabled, the rate positive and the configured global referral expense ledger valid. No savings account is debited or required.

`membership_point_transactions` stores append-only Purchase/Refund entries with customer, checkout, six-decimal points, snapshotted point value, monetary amount, voucher, original transaction and request key. Unique checkout/type, original refund and customer/request/type keys prevent duplicate capture/refund. The member row lock serializes this with referral awards and manual redemption. Remaining points equal earned minus manual redemption minus purchases plus refunds. All payment arithmetic uses integer millionths of a point and paisa. Required points are rounded UP to six decimals: Tk 350 at Tk 30/point consumes 11.666667 points. Monetary journals record the exact Tk 350 order value, not a recomputed rounded point value.

Capture posts Debit Referral Reward Expense / Credit Customer Advance Received at the order fulfilment branch, with sale order references on voucher and transactions (purpose 38). Confirmation applies the advance to the order receivable using the existing balanced sales/cost journal. Cancellation before dispatch reverses confirmed stock and sales accounting, then reverses the capture voucher (purpose 39); an immutable Refund restores the exact original points and rate even if settings are later disabled or changed. Savings, cash and registration fee collection are unaffected. Dispatch/delivery remains noncancellable. No automatic milestone bonus is generated by refunds; restored remaining points feed the existing once-per-lifetime milestone checks. Milestone qualifying point snapshots now retain six decimals.

The cart summary exposes `membership_points` (enabled, points, point_value). The order API/receipt/admin details expose `point_payment`; referral summary includes recent `point_transactions`, and the admin referral report includes net shopping points and updated available points. Flutter point displays accept fractional balances, while manual savings redemption continues to require whole points.
