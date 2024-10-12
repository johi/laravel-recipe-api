<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponses;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

class ApiController extends Controller
{
    use ApiResponses;

    protected string $policyClass;

    public function __construct()
    {
        Gate::guessPolicyNamesUsing(function () {
            return $this->policyClass;
        });
    }

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

    public function isAble($ability, $targetModel) {
        try {
            Gate::authorize($ability, $targetModel);
            return true;
        } catch (AuthorizationException $exception) {
            return false;
        }
    }
}
