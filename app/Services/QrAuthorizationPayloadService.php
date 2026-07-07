<?php

namespace App\Services;

use App\Models\DepartmentHead;
use App\Models\School;
use App\Models\SchoolSupervisor;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Str;
use RuntimeException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrAuthorizationPayloadService
{
    public function buildForUser(User $user): array
    {
        $payload = [
            'username' => $user->username,
            'userId' => $user->id,
            'host' => $this->host(),
            'passKey' => $user->password,
            'firstName' => $user->person?->first_name ?? '',
            'middleName' => $user->person?->middle_name ?? '',
            'lastName' => $user->person?->last_name ?? '',
            'suffix' => $user->person?->suffix ?? '',
            'schoolName' => $this->resolveSchoolName($user),
        ];

        $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($jsonPayload === false) {
            throw new RuntimeException('Unable to encode QR payload as JSON.');
        }

        $encryptedPayload = $this->encrypter()->encryptString($jsonPayload);

        return [
            'payload' => $payload,
            'encrypted_payload' => $encryptedPayload,
            'qr_svg' => QrCode::format('svg')->size(260)->margin(1)->generate($encryptedPayload),
        ];
    }

    private function host(): string
    {
        $appUrl = (string) config('app.url');

        if ($appUrl === '') {
            return request()->getHttpHost();
        }

        $host = parse_url($appUrl, PHP_URL_HOST) ?: '';
        $port = parse_url($appUrl, PHP_URL_PORT);

        if ($host === '') {
            return request()->getHttpHost();
        }

        return $port ? $host.':'.$port : $host;
    }

    private function resolveSchoolName(User $user): string
    {
        $school = null;

        if ($user->classification === 'Teacher') {
            $schoolId = Teacher::where('user_id', $user->id)->value('school_id');
            $school = School::find($schoolId);
        }

        if ($user->classification === 'Student') {
            $schoolId = Student::where('user_id', $user->id)->value('school_id');
            $school = School::find($schoolId);
        }

        if ($user->classification === 'School Head') {
            $schoolId = SchoolSupervisor::where('user_id', $user->id)->value('school_id');
            $school = School::find($schoolId);
        }

        if ($user->classification === 'Department Head') {
            $schoolId = DepartmentHead::where('user_id', $user->id)->value('school_id');
            $school = School::find($schoolId);
        }

        return $school?->name ?? '';
    }

    private function encrypter(): Encrypter
    {
        $rawKey = (string) config('services.user_qr_encryption_key');

        if ($rawKey === '') {
            throw new RuntimeException('USER_QR_ENCRYPTION_KEY is not configured.');
        }

        if (Str::startsWith($rawKey, 'base64:')) {
            $decoded = base64_decode(Str::after($rawKey, 'base64:'), true);
            $rawKey = $decoded === false ? '' : $decoded;
        }

        if ($rawKey === '') {
            throw new RuntimeException('Invalid USER_QR_ENCRYPTION_KEY value.');
        }

        return new Encrypter(
            hash('sha256', $rawKey, true),
            (string) config('app.cipher', 'AES-256-CBC')
        );
    }
}
