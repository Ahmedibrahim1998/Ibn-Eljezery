<?php

namespace App\Http\Controllers\Web;

use App\Exceptions\Booking\AlreadyBookedException;
use App\Http\Controllers\Controller;
use App\Http\DTOs\Web\Booking\StoreBookingDTO;
use App\Services\Web\Booking\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService,
    ) {}

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $dto = StoreBookingDTO::fromRequest($request);

        try {
            $this->bookingService->store($dto);
        } catch (AlreadyBookedException) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => trans('site.booking.already')], 422);
            }

            return back()->with('booking_error', trans('site.booking.already'))->withFragment('enroll');
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => trans('site.booking.success')]);
        }

        return redirect()
            ->route('courses.show', $dto->course_id)
            ->with('booking_success', trans('site.booking.success'))
            ->withFragment('enroll');
    }
}
