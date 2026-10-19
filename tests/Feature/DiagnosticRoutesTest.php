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

    public function test_term_exam_routes_are_registered(): void
    {
        $this->assertTrue($this->hasRoute('term-exams', 'GET', 'TermExamController@index'));
        $this->assertTrue($this->hasRoute('term-exams/upload', 'POST', 'TermExamController@upload'));
        $this->assertTrue($this->hasRoute('term-exams/{assessment}', 'GET', 'TermExamController@show'));
    }
}
