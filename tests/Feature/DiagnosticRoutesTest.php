<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class DiagnosticRoutesTest extends TestCase
{
    private function hasRoute(string $uri, string $method, string $action): bool
    {
        $routes = collect(Route::getRoutes()->getRoutes());

        return $routes->contains(function ($route) use ($uri, $method, $action) {
            $normalizedRouteUri = trim($route->uri(), '/');
            $normalizedExpectedUri = trim($uri, '/');

            return $normalizedRouteUri === $normalizedExpectedUri
                && in_array($method, $route->methods(), true)
                && str_contains($route->getActionName(), $action);
        });
    }

    public function test_diagnostic_routes_are_registered(): void
    {
        $this->assertTrue($this->hasRoute('diagnostics', 'GET', 'DiagnosticController@index'));
        $this->assertTrue($this->hasRoute('diagnostics/upload', 'POST', 'DiagnosticController@upload'));
        $this->assertTrue($this->hasRoute('diagnostics/{assessment}', 'GET', 'DiagnosticController@show'));
    }

    public function test_periodical_routes_still_exist(): void
    {
        $this->assertTrue($this->hasRoute('periodicals', 'GET', 'PeriodicalController@index'));
        $this->assertTrue($this->hasRoute('periodicals/upload', 'POST', 'PeriodicalController@upload'));
        $this->assertTrue($this->hasRoute('periodicals/{assessment}', 'GET', 'PeriodicalController@show'));
    }
}
