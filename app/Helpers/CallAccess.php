<?php

namespace App\Helpers;

use Illuminate\Support\Str;
use Hexters\HexaLite\Models\HexaOption;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

class CallAccess
{
    public function can($permissions)
    {
        // dd($permissions);
        return Gate::allows($permissions) ?? false;
    }
}
