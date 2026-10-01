<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * The four assessment areas that are presented as tabs instead of separate
 * sidebar entries.
 */
enum AssessmentTab: string
{
    case TermExam = 'term_exams.view';
    case Diagnostic = 'diagnostics.view';
    case Summative = 'summatives.view';
    case Ecdc = 'ecdcs.view';

    public function label(): string
    {
        return match ($this) {
            self::TermExam => 'Term Exam',
            self::Diagnostic => 'Diagnostic Test',
            self::Summative => 'Summative Test',
            self::Ecdc => 'ECDC',
        };
    }

    public function url(): string
    {
        return match ($this) {
            self::TermExam => '/term-exams',
            self::Diagnostic => '/diagnostics',
            self::Summative => '/summatives',
            self::Ecdc => '/ecdcs',
        };
    }

    /**
     * The tabs the given user is allowed to open, in display order.
     *
     * @return list<self>
     */
    public static function availableFor(?Authenticatable $user): array
    {
        if (! $user instanceof User) {
            return [];
        }

        return array_values(array_filter(
            self::cases(),
            fn (self $tab): bool => $user->can($tab->value),
        ));
    }

    /**
     * The tab the current request belongs to, if any.
     */
    public static function current(): ?self
    {
        $path = '/'.ltrim(request()->path(), '/');

        foreach (self::cases() as $tab) {
            if (str_starts_with($path, $tab->url())) {
                return $tab;
            }
        }

        return null;
    }
}
