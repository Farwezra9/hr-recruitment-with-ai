<?php

namespace App\Support;

use Illuminate\Validation\Rules\Password;

class PasswordPolicy
{
    /** Minimal 10 karakter, huruf besar + kecil, dan angka. */
    public static function rule(): Password
    {
        return Password::min(10)->mixedCase()->numbers();
    }

    public const HINT = 'Minimal 10 karakter, mengandung huruf besar, huruf kecil, dan angka.';
}
