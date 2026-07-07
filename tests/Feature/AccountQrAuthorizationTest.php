<?php

namespace Tests\Feature;

use App\Models\Person;
use App\Models\School;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountQrAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.user_qr_encryption_key', 'test-qr-key-123');
    }

    public function test_account_page_renders_qr_panel_for_authenticated_user(): void
    {
        $user = $this->createTeacherUser();

        $response = $this->actingAs($user)->get('/account');

        $response->assertOk();
        $response->assertSee('Mobile Authorization QR');
        $response->assertSee('Scan this QR code in the mobile app to prefill account details.');
    }

    public function test_account_qr_endpoint_returns_payload_and_svg(): void
    {
        $user = $this->createTeacherUser();

        $response = $this->actingAs($user)->getJson('/account/qr');

        $response->assertOk();
        $response->assertJsonStructure([
            'qr_svg',
            'payload' => [
                'username',
                'userId',
                'host',
                'firstName',
                'middleName',
                'lastName',
                'suffix',
                'schoolName',
            ],
        ]);
        $response->assertJsonMissingPath('payload.passKey');
    }

    public function test_password_update_requires_confirmation(): void
    {
        $user = $this->createTeacherUser();

        $response = $this->actingAs($user)->post('/account/update_password', [
            'password' => 'NewPassword123',
            'password_confirmation' => 'Mismatch123',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_password_update_changes_stored_password_hash(): void
    {
        $user = $this->createTeacherUser();

        $response = $this->actingAs($user)->post('/account/update_password', [
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        $user->refresh();

        $this->assertTrue(Hash::check('NewPassword123', $user->password));
    }

    private function createTeacherUser(): User
    {
        $person = new Person;
        $person->first_name = 'Jampol';
        $person->middle_name = '';
        $person->last_name = 'Dela Cruz';
        $person->suffix = '';
        $person->gender = 'M';
        $person->birth_date = '1990-01-01';
        $person->save();

        $school = new School;
        $school->code = 'SANTA-MARIA';
        $school->name = 'Santa Maria Integrated NS';
        $school->address = 'Santa Maria';
        $school->school_category_id = 1;
        $school->school_type_id = 1;
        $school->district_id = 1;
        $school->save();

        $user = new User;
        $user->username = 'jampol@deped.gov.ph';
        $user->password = Hash::make('password123');
        $user->classification = 'Teacher';
        $user->status = true;
        $user->person_id = $person->id;
        $user->save();

        $teacher = new Teacher;
        $teacher->email = 'jampol@deped.gov.ph';
        $teacher->user_id = $user->id;
        $teacher->school_id = $school->id;
        $teacher->save();

        return $user;
    }
}
