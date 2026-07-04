<?php

namespace App\Http\Controllers\Web;

use App\Exceptions\Booking\AlreadyBookedException;
use App\Exceptions\Booking\SessionFullException;
use App\Http\Controllers\Controller;
use App\Http\DTOs\Web\Booking\StoreBookingDTO;
use App\Models\CourseSession;
use App\Services\Web\Booking\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService,
    ) {}

    public function store(Request $request): RedirectResponse
    {
        $dto = StoreBookingDTO::fromRequest($request);

        $session = CourseSession::with('course')->findOrFail($dto->course_session_id);
        $courseId = $session->course_id;

        try {
            $this->bookingService->store($dto);
        } catch (AlreadyBookedException) {
            return back()->with('booking_error', trans('site.booking.already'))->withFragment('sessions');
        } catch (SessionFullException) {
            return back()->with('booking_error', trans('site.booking.full'))->withFragment('sessions');
        }

        // Online sessions expose the Zoom join link on success.
        $joinUrl = $session->isOnline() ? $session->zoom_join_url : null;

        return redirect()
            ->route('courses.show', $courseId)
            ->with('booking_success', trans('site.booking.success'))
            ->with('booking_join_url', $joinUrl)
            ->withFragment('sessions');
    }
}
