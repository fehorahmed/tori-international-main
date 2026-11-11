<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Keep migration defaults self-contained so future application changes cannot change a fresh install.
    private function defaults(): array
    {
        return [
            'cashout' => ['enabled' => false, 'maximum_amount' => '0.00', 'charge_type' => 'fixed', 'charge_rate' => '0.00', 'hold_minutes' => 10, 'income_head_id' => null],
            'transfer' => ['enabled' => false, 'maximum_amount' => '0.00', 'charge_type' => 'fixed', 'charge_rate' => '0.00', 'income_head_id' => null, 'posting_user_id' => null],
            'cashin' => ['enabled' => false],
        ];
    }

    public function up(): void
    {
        if (! Schema::hasTable('membership_transaction_settings')) {
            Schema::create('membership_transaction_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key', 100)->unique();
                $table->json('value')->nullable();
                $table->timestamps();
            });
        }
        DB::transaction(function () {
            foreach ($this->defaults() as $group => $fields) {
                $legacy = match ($group) {
                    'cashout' => 'membership_cashout_settings', 'transfer' => 'membership_transfer_settings', default => null
                };
                $old = $legacy && Schema::hasTable($legacy) ? DB::table($legacy)->where('id', 1)->first() : null;
                foreach ($fields as $field => $default) {
                    $value = $old ? ($old->{$field} ?? $default) : $default;
                    if ($field === 'enabled') {
                        $value = (bool) $value;
                    }
                    DB::table('membership_transaction_settings')->insertOrIgnore([
                        'key' => $group.'.'.$field, 'value' => json_encode($value, JSON_THROW_ON_ERROR),
                        'created_at' => $old?->created_at ?? now(), 'updated_at' => $old?->updated_at ?? now(),
                    ]);
                }
            }
        });
        // All legacy values have been copied successfully before removing the old stores.
        Schema::dropIfExists('membership_cashout_settings');
        Schema::dropIfExists('membership_transfer_settings');
    }

    public function down(): void
    {
        foreach (['cashout' => 'membership_cashout_settings', 'transfer' => 'membership_transfer_settings'] as $group => $legacy) {
            if (! Schema::hasTable($legacy)) {
                Schema::create($legacy, function (Blueprint $table) use ($group) {
                    $table->id();
                    $table->boolean('enabled')->default(false);
                    $table->decimal('maximum_amount', 12, 2)->default(0);
                    $table->string('charge_type')->default('fixed');
                    $table->decimal('charge_rate', 12, 2)->default(0);
                    if ($group === 'cashout') {
                        $table->unsignedInteger('hold_minutes')->default(10);
                    }
                    $table->unsignedBigInteger('income_head_id')->nullable();
                    if ($group === 'transfer') {
                        $table->unsignedBigInteger('posting_user_id')->nullable();
                    }
                    $table->timestamps();
                });
            }
            $row = ['id' => 1];
            foreach ($this->defaults()[$group] as $field => $default) {
                $stored = DB::table('membership_transaction_settings')->where('key', $group.'.'.$field)->first();
                $row[$field] = $stored ? json_decode($stored->value ?? 'null', true, 512, JSON_THROW_ON_ERROR) : $default;
            }
            DB::table($legacy)->updateOrInsert(['id' => 1], $row + ['created_at' => now(), 'updated_at' => now()]);
        }
        Schema::dropIfExists('membership_transaction_settings');
    }
};
