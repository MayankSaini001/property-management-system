<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AvailabilityController extends Controller
{
    public function __construct(private AvailabilityService $availability)
    {
    }

    /**
     * Search which rooms or villa units can be booked for the given dates and guests.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Property::class);

        // Only active properties of the logged-in user's company can be searched.
        $properties = $request->user()
            ->company
            ->properties()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $submitted = $request->filled('property_id')
            || $request->filled('check_in')
            || $request->filled('check_out');

        $search = null;
        $searchErrors = [];

        if ($submitted) {
            $property = $properties->firstWhere('id', (int) $request->input('property_id'));
            $today = now($property?->timezone ?? config('app.timezone'))->toDateString();

            $validator = Validator::make($request->all(), [
                'property_id' => ['required', Rule::in($properties->pluck('id')->all())],
                'check_in' => ['required', 'date', 'after_or_equal:' . $today],
                'check_out' => ['required', 'date', 'after:check_in'],
                'adults' => ['required', 'integer', 'min:1', 'max:20'],
                'children' => ['nullable', 'integer', 'min:0', 'max:20'],
            ]);

            $validator->after(function ($validator) use ($request) {
                if ($validator->errors()->isEmpty()) {
                    $nights = Carbon::parse($request->input('check_in'))
                        ->diffInDays(Carbon::parse($request->input('check_out')));

                    if ($nights > 60) {
                        $validator->errors()->add('check_out', 'A stay can be searched for up to 60 nights.');
                    }
                }
            });

            if ($validator->fails()) {
                $searchErrors = $validator->errors()->all();
            } else {
                $search = $this->availability->search(
                    $property,
                    $request->input('check_in'),
                    $request->input('check_out'),
                    (int) $request->input('adults'),
                    (int) $request->input('children', 0)
                );
            }
        }

        return view('company.availability.index', compact('properties', 'search', 'searchErrors', 'submitted'));
    }
}