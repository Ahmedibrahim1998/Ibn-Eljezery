<?php

namespace App\Services\Web\Lead;

use App\Enum\Lead\LeadStatusEnum;
use App\Http\DTOs\Web\Lead\StoreLeadDTO;
use App\Models\Lead;
use App\Repositories\Lead\LeadRepositoryInterface;

readonly class LeadService
{
    public function __construct(
        private LeadRepositoryInterface $leadRepository,
    ) {}

    /**
     * Persist a new lead coming from a public landing-page form.
     */
    public function store(StoreLeadDTO $dto): Lead
    {
        $data = $dto->toArray();
        $data['status'] = LeadStatusEnum::NEW->value;

        // Empty course selection ("" / 0) should stay null, not a bad FK.
        if (empty($data['course_id'])) {
            $data['course_id'] = null;
        }

        /** @var Lead $lead */
        $lead = $this->leadRepository->create($data);

        return $lead;
    }
}
