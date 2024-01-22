<?php

namespace App\Traits;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Request;

trait Authorizable
{
    private array $abilities = [
        'index' => 'show',
        'edit' => 'edit',
        'show' => 'show',
        'update' => 'edit',
        'create' => 'create',
        'store' => 'create',
        'destroy' => 'delete',
    ];

    /**
     * Override of callAction to perform the authorization before
     *
     * @return mixed
     *
     * @throws AuthorizationException
     */
    public function callAction($method, $parameters)
    {
        if (in_array($method, ['index', 'show'])) {
        } elseif ($ability = $this->getAbility($method)) {
            $this->authorize($ability);
        }

        return parent::callAction($method, $parameters);
    }

    public function getAbility($method): ?string
    {
        $routeName = explode('.', Request::route()->getName());
        $action = Arr::get($this->getAbilities(), $method);

        return $action ? "{$routeName[0]}.{$action}" : null;
    }

    private function getAbilities(): array
    {
        return $this->abilities;
    }

    public function setAbilities($abilities)
    {
        $this->abilities = $abilities;
    }
}
