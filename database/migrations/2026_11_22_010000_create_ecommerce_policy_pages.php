<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $pages = [
            ['sort' => 10, 'title' => 'Terms & Conditions', 'slug' => 'terms-and-conditions', 'content' => <<<'HTML'
<p>সর্বশেষ আপডেট: ২২ নভেম্বর ২০২৬</p>
<p>Tori International-এর ecommerce website-এ account তৈরি, order ও কেনাকাটার ক্ষেত্রে নিচের শর্তগুলো প্রযোজ্য। Order দেওয়ার আগে product, মূল্য, payment method ও delivery সংক্রান্ত তথ্য দেখে নিন।</p>
<h2>১. Account ও সঠিক তথ্য</h2>
<p>Registration-এর সময় সঠিক নাম, mobile number ও address দিন। Password, OTP ও transaction PIN গোপন রাখুন। আপনার account-এ অননুমোদিত ব্যবহার সন্দেহ হলে website-এ দেওয়া যোগাযোগের মাধ্যমে support-কে জানান।</p>
<h2>২. Product ও order</h2>
<p>Product-এর বিবরণ, MRP, বিক্রয়মূল্য ও প্রযোজ্য discount product page এবং checkout-এ দেখানো হয়। Order-এর availability, delivery address বা payment-সংক্রান্ত সমস্যা থাকলে support থেকে আপনার সঙ্গে যোগাযোগ করা হতে পারে। Order status account-এর order history থেকে দেখুন।</p>
<h2>৩. Discount ও Special Offers</h2>
<p>General discount, member discount ও campaign offer-এর যোগ্যতা আলাদা হতে পারে। Member discount পেতে সক্রিয় membership এবং নির্ধারিত balance-এর শর্ত পূরণ করতে হবে। Special Offer নির্ধারিত সময় ও product-এর জন্য প্রযোজ্য। Checkout-এ প্রদর্শিত প্রযোজ্য মূল্য ও শর্ত অনুসারে order করুন।</p>
<h2>৪. Payment ও points</h2>
<p>Checkout-এ উপলব্ধ payment method নির্বাচন করুন। Member discount বা Special Offer প্রযোজ্য product থাকলে Cash on Delivery উপলব্ধ নাও থাকতে পারে। Points দিয়ে payment করলে product-এর MRP অনুযায়ী হিসাব হবে; কোনো discount প্রযোজ্য হবে না। Delivery charge-ও points থেকে পরিশোধ হবে এবং checkout-এ point conversion ও মোট প্রয়োজনীয় points দেখানো হবে।</p>
<h2>৫. Delivery</h2>
<p>Delivery charge checkout-এ দেখানো হয়। সঠিক address ও যোগাযোগযোগ্য mobile number দিন। Delivery-সংক্রান্ত প্রশ্ন বা সমস্যায় order reference দিয়ে support-এর সঙ্গে যোগাযোগ করুন।</p>
<h2>৬. Cancellation, return ও refund</h2>
<p>Order cancel, ভুল বা ক্ষতিগ্রস্ত product, return অথবা refund-এর অনুরোধে order number, সমস্যার বিবরণ এবং প্রয়োজন হলে প্রমাণসহ support-এ যোগাযোগ করুন। Order status, payment ও product-এর অবস্থা যাচাই করে প্রযোজ্য ব্যবস্থা জানানো হবে। Account-এ cancellation ও refund-এর status যাচাই করুন।</p>
<h2>৭. Referral</h2>
<p>Referral code দেওয়া optional। Member বা Agent-এর active code registration-এর সময় যাচাই করা হয়। শুধু referral link click বা registration সম্পন্ন করলেই bonus প্রাপ্য হয় না; registration fee এবং প্রযোজ্য referral settings-এর শর্ত পূরণ করতে হয়।</p>
<h2>৮. যোগাযোগ ও পরিবর্তন</h2>
<p>এই শর্ত বা কোনো order নিয়ে প্রশ্ন থাকলে website-এর footer-এ দেওয়া phone, email বা address ব্যবহার করে যোগাযোগ করুন। শর্ত পরিবর্তন হলে এই page-এর updated version প্রকাশ করা হবে।</p>
HTML],
            ['sort' => 20, 'title' => 'Privacy Policy', 'slug' => 'privacy-policy', 'content' => <<<'HTML'
<p>সর্বশেষ আপডেট: ২২ নভেম্বর ২০২৬</p>
<p>এই page-এ Tori International-এর ecommerce website ব্যবহার, registration ও order-এর সঙ্গে সম্পর্কিত ব্যক্তিগত তথ্যের ব্যবহার ব্যাখ্যা করা হয়েছে।</p>
<h2>১. কী তথ্য সংগ্রহ করা হয়</h2>
<p>আপনার দেওয়া নাম, mobile number, email, address, branch ও referral information account-এর সঙ্গে সংরক্ষিত হতে পারে। Order-এর product, delivery, payment method ও transaction reference-ও সংরক্ষণ করা হয়। Membership/KYC service ব্যবহার করলে ওই service-এর জন্য দেওয়া পরিচয় ও document information যুক্ত হতে পারে।</p>
<h2>২. তথ্য কেন ব্যবহার করা হয়</h2>
<p>Account তৈরি ও যাচাই, order পরিচালনা, delivery, payment হিসাব, discount eligibility, referral সম্পর্ক ও bonus হিসাব, customer support এবং অননুমোদিত ব্যবহার শনাক্ত করতে প্রাসঙ্গিক তথ্য ব্যবহার করা হয়।</p>
<h2>৩. Browser storage ও referral link</h2>
<p>Website login session ও cart-এর মতো feature-এর জন্য cookies বা browser storage ব্যবহার করতে পারে। Referral link ব্যবহার করলে referrer-এর type ও code সর্বোচ্চ ৩০ দিন browser-এ মনে রাখা হয়। Registration-এর সময় code পরিবর্তন বা মুছে দিতে পারেন। Browser data মুছে দিলে সংরক্ষিত preference বা session চলে যেতে পারে।</p>
<h2>৪. তথ্য কার সঙ্গে ব্যবহার করা হতে পারে</h2>
<p>Order, delivery, SMS verification, payment ও support পরিচালনার জন্য দায়িত্বপ্রাপ্ত staff এবং সংশ্লিষ্ট service provider-এর কাছে কাজের জন্য প্রয়োজনীয় তথ্য দেওয়া হতে পারে। কোনো service-এর নিজস্ব privacy terms থাকলে সেটিও দেখে নিন।</p>
<h2>৫. Account security</h2>
<p>Password ও PIN সরাসরি পড়ার মতো plain text-এ সংরক্ষণ করা হয় না। নিজের password, OTP, PIN বা অন্য login credential কাউকে দেবেন না। Shared device ব্যবহার শেষে logout করুন। App-এর biometric login ব্যবহার করলে fingerprint-এর ছবি বা template server-এ পাঠানো হয় না; device-specific credential দিয়ে login যাচাই করা হয়।</p>
<h2>৬. তথ্য সংশোধন ও সংরক্ষণ</h2>
<p>Account-এ উপলব্ধ profile option থেকে তথ্য সংশোধন করুন অথবা support-এ অনুরোধ করুন। তথ্য দেখা, সংশোধন বা account deletion-এর অনুরোধে পরিচয় যাচাই প্রয়োজন হতে পারে। Order, transaction, হিসাব ও বিরোধ নিষ্পত্তির জন্য প্রয়োজনীয় record মুছে দেওয়ার অনুরোধের পরেও রাখা প্রয়োজন হতে পারে।</p>
<h2>৭. যোগাযোগ ও policy update</h2>
<p>ব্যক্তিগত তথ্য নিয়ে প্রশ্ন বা অনুরোধে website-এর footer-এ দেওয়া যোগাযোগ ব্যবহার করুন। Policy পরিবর্তন হলে এই page-এ updated version ও তারিখ প্রকাশ করা হবে।</p>
HTML],
        ];
        foreach ($pages as $page) {
            if (! DB::table('pages')->where('slug', $page['slug'])->exists()) {
                DB::table('pages')->insert($page + ['status' => 1, 'created_at' => now(), 'updated_at' => now()]);
            }
        }
    }

    public function down(): void
    {
        // Preserve policy content and any subsequent admin edits on rollback.
    }
};
