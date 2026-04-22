<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkshopRequest;
use App\Http\Requests\UpdateWorkshopRequest;
use App\Models\Workshop;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class WorkshopController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Workshops/Index', [
            'workshops' => Workshop::all(),
        ]);
    }

    public function store(StoreWorkshopRequest $request): RedirectResponse
    {
        Workshop::create($request->validated());

        return redirect()->back();
    }

    public function update(UpdateWorkshopRequest $request, Workshop $workshop): RedirectResponse
    {
        $workshop->update($request->validated());

        return redirect()->back();
    }

    public function destroy(Workshop $workshop): RedirectResponse
    {
        $workshop->delete();

        return redirect()->back();
    }
}
