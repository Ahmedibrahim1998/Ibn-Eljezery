<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\DTOs\Web\Lead\StoreLeadDTO;
use App\Services\Web\Lead\LeadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function __construct(
        private readonly LeadService $leadService,
    ) {}

    public function store(Request $request): RedirectResponse
    {
        $dto = StoreLeadDTO::fromRequest($request);

        $this->leadService->store($dto);

        $message = $dto->source === 'hero'
            ? trans('site.forms.success_hero')
            : trans('site.forms.success_contact');

        return back()
            ->with('lead_success', $message)
            ->withFragment($dto->source === 'hero' ? 'hero' : 'contact');
    }
}
