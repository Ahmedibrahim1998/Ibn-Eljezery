<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\DTOs\Web\Testimonial\StoreTestimonialDTO;
use App\Services\Web\Testimonial\TestimonialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function __construct(
        private readonly TestimonialService $testimonialService,
    ) {}

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $dto = StoreTestimonialDTO::fromRequest($request);

        $this->testimonialService->store($dto);

        $message = trans('site.review.success');

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('review_success', $message)->withFragment('contact');
    }
}
