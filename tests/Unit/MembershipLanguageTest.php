<?php

namespace Tests\Unit;

use Tests\TestCase;

class MembershipLanguageTest extends TestCase
{
    public function test_membership_errors_follow_accept_language(): void
    {
        config(['cache.default' => 'array']);

        foreach ([
            'en' => 'Your verification token has expired.',
            'en-US,en;q=0.9' => 'Your verification token has expired.',
            'bn' => 'ভেরিফিকেশন টোকেনটির মেয়াদ শেষ হয়ে গেছে।',
            'bn-BD,bn;q=0.9' => 'ভেরিফিকেশন টোকেনটির মেয়াদ শেষ হয়ে গেছে।',
        ] as $language => $message) {
            $this->withHeader('Accept-Language', $language)
                ->postJson('/api/membership/v1/auth/set-pin', [
                    'verification_token' => 'missing-language-test-token',
                    'pin' => '763921',
                    'pin_confirmation' => '763921',
                ])
                ->assertUnauthorized()
                ->assertJsonPath('message', $message);
        }
    }

    public function test_existing_clients_without_language_header_keep_bengali(): void
    {
        config(['cache.default' => 'array']);

        $this->withServerVariables(['HTTP_ACCEPT_LANGUAGE' => null])
            ->postJson('/api/membership/v1/auth/verify-otp', [
            'mobile_no' => '01900000211',
            'otp' => '123456',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'ওটিপির মেয়াদ শেষ হয়ে গেছে। অনুগ্রহ করে পুনরায় ওটিপি পাঠান।');
    }
}
