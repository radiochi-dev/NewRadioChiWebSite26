<?php

namespace App\Http\Controllers\Backoffice;

use App\Actions\Backoffice\BuildBackofficeDashboardPayloadAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly BuildBackofficeDashboardPayloadAction $dashboardPayload,
    ) {
    }

    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Backoffice/Dashboard/Index', $this->dashboardPayload->execute($user));
    }
}
