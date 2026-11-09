# Global Architecture Migration Plan (Module by Module)

এই ডকুমেন্টে পুরো প্রজেক্টটিকে "Global Accounts & Parties" আর্কিটেকচারে নিয়ে যাওয়ার জন্য মডিউল অনুযায়ী কী কী পরিবর্তন করতে হবে, তার একটি চেকলিস্ট দেওয়া হলো। আমরা একটি একটি করে মডিউল ধরে কাজ করব যাতে সিস্টেমে কোনো কনফ্লিক্ট না হয়।

---

## 🛠️ মডিউল ১: Database & Migrations (প্রাথমিক কাজ)
ডাটাবেস স্ট্রাকচার পরিবর্তন করা হলো প্রথম ধাপ।

- [ ] **Suppliers Table:** `suppliers` টেবিল থেকে `sister_concern_id` কলামটি ড্রপ (Drop) বা ইগনোর করতে হবে।
- [ ] **Account Groups Table:** `account_groups` টেবিল থেকে `sister_concern_id` কলামটি ড্রপ করতে হবে।
- [ ] **Account Heads Table:** `account_heads` টেবিলে `sister_concern_id` কলামটিকে `nullable()` করতে হবে। (যাতে গ্লোবাল হেডগুলোর id null থাকে এবং স্পেসিফিক ব্যাংকের id নির্দিষ্ট থাকে)।
- [ ] **Account Heads Table:** `opening_balance` এবং `current_balance` কলাম দুটি সরিয়ে ফেলতে হবে বা এদের ওপর নির্ভরতা শূন্য করতে হবে।

---

## 👥 মডিউল ২: Parties & Contacts (Supplier/Customer Management)
কাস্টমার এবং সাপ্লায়ারকে গ্লোবাল করার কাজ।

- [ ] **Supplier Model (`app/Models/Supplier.php`):** 
  - `sisterConcern()` রিলেশনশিপ রিমুভ করা।
  - `getCustomerBalance()` মেথডটি রি-রাইট করা (সরাসরি `Transaction` টেবিল থেকে ডাটা এনে ব্যালেন্স ক্যালকুলেট করা, `AccountHead` বা `SaleOrder` থেকে নয়)।
- [ ] **Supplier Controller:** கাস্টমার বা সাপ্লায়ার সেভ করার সময় `sister_concern_id` ইনসার্ট করার লজিক রিমুভ করা।
- [ ] **Supplier List View:** কাস্টমার/সাপ্লায়ার দেখার লিস্টে `where('sister_concern_id', ...)` ফিল্টার রিমুভ করে সবাইকে দেখানো।

---

## 🏦 মডিউল ৩: Accounts Configuration (Groups & Heads)
চার্ট অফ অ্যাকাউন্টস গ্লোবাল করা।

- [ ] **AccountGroupController:** `index()`, `store()`, `update()` মেথড থেকে `activeConcernId()` এর ফিল্টার বাদ দেওয়া। 
- [ ] **AccountHeadController:** 
  - হেড লিস্ট ভিউতে গ্লোবাল এবং ব্রাঞ্চ-স্পেসিফিক উভয় হেড দেখানোর লজিক দেওয়া।
  - ফর্ম সেভ করার সময় ইউজারকে অপশন দেওয়া যে, সে কি গ্লোবাল হেড (Cash/Rent) বানাচ্ছে নাকি নির্দিষ্ট ব্রাঞ্চের (DBBL Bank) হেড বানাচ্ছে।
- [ ] **Account Group/Head Views:** UI-তে ফিল্টারগুলো আপডেট করা।

---

## 💵 মডিউল ৪: Transactions & Vouchers (Receipt & Payment)
সবচেয়ে সেনসিটিভ মডিউল। এখানে সিস্টার কনসার্ন ফিল্টার কড়াকড়িভাবে থাকবে।

- [ ] **ReceiptPaymentController (`create` & `edit`):** 
  - পেমেন্ট মোড (Bank/Cash) ড্রপডাউনে শুধুমাত্র গ্লোবাল হেড (`sister_concern_id = null`) এবং কারেন্ট ব্রাঞ্চের হেডগুলো ফায়ার করা।
  - পার্টি (Customer/Supplier) ড্রপডাউনে পুরো গ্লোবাল লিস্ট দেখানো।
- [ ] **ReceiptPaymentController (`store` & `update`):** 
  - ভাউচার এবং ট্রানজেকশনে `sister_concern_id` আগের মতোই সেভ হবে (এটি পরিবর্তন হবে না)।

---

## 📊 মডিউল ৫: Accounting Reports (লেজার, ব্যালেন্স শিট)
রিপোর্টিংয়ের লজিক সম্পূর্ণ চেঞ্জ হবে।

- [ ] **ReportController (Ledger):** নির্দিষ্ট কাস্টমার বা হেডের লেজার তৈরি করার সময় `AccountHead` এর `opening_balance` এর বদলে, `Transaction` টেবিল থেকে ডেবিট-ক্রেডিট হিসেব করে লেজার তৈরি করতে হবে। 
- [ ] **ReportController (Trial Balance & Balance Sheet):** গ্লোবাল হেডগুলোর লিস্ট নিয়ে এসে, প্রতিটি হেডের জন্য `Transaction` টেবিল থেকে নির্দিষ্ট ব্রাঞ্চের টোটাল ডেবিট ও ক্রেডিট বের করে রিপোর্ট জেনারেট করতে হবে।

---

## 🛒 মডিউল ৬: Sales & Purchase Module
সেলস এবং পারচেস করার সময় পার্টি সিলেকশন গ্লোবাল করা।

- [ ] **SaleOrderController / PosController:** সেলস করার সময় কাস্টমার ড্রপডাউনে যেন কোনো ব্রাঞ্চ ফিল্টার না থাকে।
- [ ] **PurchaseOrderController:** সাপ্লায়ার ড্রপডাউনে গ্লোবাল সাপ্লায়ার শো করানো। 
- [ ] **Sales/Purchase Reports:** ব্রাঞ্চ-ওয়াইজ সেলস ঠিক রাখতে ভাউচার এবং ইনভয়েসে `sister_concern_id` এর অস্তিত্ব নিশ্চিত করা।

---

## 🚀 পরবর্তী পদক্ষেপ (Next Steps):
আমরা চাইলে এই চেকলিস্ট ধরে কাজ শুরু করতে পারি। কাজ শুরু করার জন্য সবচেয়ে নিরাপদ জায়গা হলো **"মডিউল ২: Parties & Contacts (Supplier/Customer Management)"**।

আপনি কি **"মডিউল ২"** দিয়ে কাজ শুরু করতে চান? জানালে আমি কোড মডিফিকেশন শুরু করব।
