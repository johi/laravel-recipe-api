<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApiController extends Controller
{

    public function includes(array $relationships): array
    {
        $param = request()->get('include');
        $includes = [];
        $includeValues = explode(',', strtolower($param));
        foreach ($relationships as $relationship) {
            if (in_array(strtolower($relationship), $includeValues)) {
                $includes[] = $relationship;
            }
        }
        return $includes;
    }
}
