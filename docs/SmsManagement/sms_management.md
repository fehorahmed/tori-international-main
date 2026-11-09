# SMS Management Module Architecture

## 1. Overview
The **SMS Management Module** provides a centralized, decoupled way to manage and dispatch SMS communications across the enterprise system. It uses an **Event-Driven Architecture (EDA)** to remain loosely coupled from other modules and employs the **Strategy Pattern** to dynamically route messages through multiple SMS gateways with fallback capabilities.

## 2. Design Patterns Applied

### 2.1. Observer Pattern (Event-Driven)
Other modules (e.g., Sales, HRM) **do not** call the SMS service directly. Instead, they fire Laravel Events (e.g., `SaleInvoiceCreated`). The SMS module registers Listeners for these events. This ensures that adding or removing SMS capabilities does not require changing core business logic in other modules.

### 2.2. Strategy Pattern (Gateway Management)
To handle multiple SMS providers (e.g., SSL Wireless, BulkSMS BD) without tightly coupling code to their specific APIs, we use an `SmsGatewayInterface`. Each provider has its own Strategy class implementing this interface. The active strategy is determined at runtime based on the database configuration.

---

## 3. Database Architecture
We require three core tables to manage gateways, templates, and logging.

### 3.1. `sms_gateways`
Stores API credentials and configurations for various SMS providers.
- `id` (PK)
- `provider_name` (string)
- `api_url` (string)
- `api_key` (string)
- `sender_id` (string, nullable)
- `is_active` (boolean) - Marks the primary gateway.
- `status` (boolean)
- *Timestamps & SoftDeletes*

### 3.2. `sms_templates`
Stores pre-defined message formats linked to specific Events/Modules. This table also acts as the master toggle for each event.
- `id` (PK)
- `module_name` (string) - Links the template to a specific module (e.g., 'Sales').
- `event_name` (string) - The exact Laravel Event this template responds to (e.g., 'sale_created').
- `title` (string) - A human-readable title for the admin panel.
- `body` (text) - e.g., "Dear {{name}}, your invoice {{invoice_no}} is created."
- `available_variables` (string) - e.g., "{{name}}, {{invoice_no}}"
- `is_active` (boolean) - Allows turning off SMS for this specific event.
- *Timestamps & SoftDeletes*

### 3.3. `sms_logs`
A ledger of every SMS sent, tracking status and API responses.
- `id` (PK)
- `module_name` (string, nullable)
- `event_name` (string, nullable)
- `mobile_no` (string)
- `message` (text)
- `response_text` (text, nullable)
- `status` (enum: 'Pending', 'Sent', 'Failed')
- `sent_by` (foreign_key -> users, nullable)
- `sent_at` (datetime, nullable)
- *Timestamps*

---

## 4. Core System Components

### 4.1. `SmsGatewayInterface` & Strategies
- **`SmsGatewayInterface`**: Requires a `send(string $mobile, string $message)` method.
- **Implementations**: `SslWirelessStrategy`, `BulkSmsBdStrategy`, etc.

### 4.2. `SmsService` (Central Engine)
The central service responsible for routing messages.
- Method: `dispatch(SmsTemplate $template, $mobile, $parsedMessage)`
- **Safety Checks:** 
  - Verifies if the `$template->is_active` is true. If false, aborts sending.
- Uses the `SmsGatewayInterface` (resolved via dependency injection based on the active gateway).
- Triggers Fallback if the primary gateway fails.

### 4.3. Listeners & Queue Jobs
- **Event Listeners**: Classes like `SendSaleCreatedSms` that listen to `SaleInvoiceCreated`. They fetch the correct template, parse variables, and call `SmsService`.
- **`SendSmsJob`**: Dispatched by `SmsService` to Laravel's Queue to prevent blocking the main HTTP request during API calls.

---

## 5. UI & Navigation Integration
Controllers located in `App\Http\Controllers\SmsManagement\`:
1. **`SmsSettingController`**: Manage Module Settings (ON/OFF).
2. **`SmsGatewayController`**: CRUD operations and toggling active providers.
3. **`SmsTemplateController`**: CRUD for templates and dynamic variables.
4. **`SmsCampaignController`**: Bulk SMS sender UI.
5. **`SmsLogController`**: Datatable view to monitor sent and failed messages.

Modular menu integration under `resources/views/layouts/menu/modular/__sms_management.blade.php`.
