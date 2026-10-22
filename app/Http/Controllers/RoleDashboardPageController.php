<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\RoleDashboardData;
use App\Services\RoleDashboardWelcomeContext;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class RoleDashboardPageController extends Controller
{
    public function index(
        Request $request,
        RoleDashboardController $dashboardController,
        RoleDashboardData $dashboardData,
        RoleDashboardWelcomeContext $welcomeContext,
    ): View {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        return $dashboardController
            ->index($request, $dashboardData)
            ->with('welcomeContext', $welcomeContext->forUser($user));
    }
}
