<?php

namespace Botble\Inventory\Domains\Supplier\DTO;

use Botble\Inventory\Domains\Supplier\Http\Requests\SupplierApprovalRequest;

class SupplierApprovalDTO
{
    public function __construct(
        public readonly ?string $note = null,
    ) {
    }

    public static function fromRequest(SupplierApprovalRequest $request): self
    {
        $data = $request->validated();
        $note = isset($data['note']) ? trim((string) $data['note']) : null;

        return new self(
            note: $note !== '' ? $note : null,
        );
    }
}
