@php
    $status = $status ?: 'draft';
    $canEdit = ! in_array($status, ['completed', 'cancelled'], true);
    $user = auth()->user();
    $canApprove = $user && ((method_exists($user, 'isSuperUser') && $user->isSuperUser()) || $user->hasPermission('transfer.approve'));
    $canExport = $user && ((method_exists($user, 'isSuperUser') && $user->isSuperUser()) || $user->hasPermission('transfer.export'));
    $canReceive = $user && ((method_exists($user, 'isSuperUser') && $user->isSuperUser()) || $user->hasPermission('transfer.receive'));
    $canCancel = $user && ((method_exists($user, 'isSuperUser') && $user->isSuperUser()) || $user->hasPermission('transfer.cancel'));
@endphp

@if($canEdit)
    <div class="transfer-workflow-actions">
        @if($status === 'draft')
            <button type="submit" class="transfer-action transfer-action--secondary" data-transfer-action="save_draft">
                Lưu nháp
            </button>
        @endif

        @if($status === 'draft' && $canApprove)
            <button type="submit" class="transfer-action transfer-action--primary" data-transfer-action="confirm">
                Xác nhận
            </button>
        @endif

        @if($status === 'confirmed' && $canExport)
            <button
                type="submit"
                class="transfer-action transfer-action--primary"
                data-transfer-action="export"
            >
                Xuất chuyển
            </button>
        @endif

        @if($status === 'exporting' && $canReceive)
            <button
                type="submit"
                class="transfer-action transfer-action--primary"
                data-transfer-action="complete"
            >
                Hoàn tất nhập kho
            </button>
        @endif

        @if(in_array($status, ['draft', 'confirmed'], true) && $canCancel)
            <button type="submit" class="transfer-action transfer-action--secondary" data-transfer-action="cancel">
                Hủy phiếu
            </button>
        @endif
    </div>

    <style>
        .transfer-workflow-actions {
            border-top: 1px solid #D9DEE6;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: flex-end;
            margin-top: 18px;
            padding-top: 18px;
        }

        .transfer-action {
            align-items: center;
            border-radius: 10px;
            display: inline-flex;
            font-weight: 700;
            justify-content: center;
            min-height: 48px;
            padding: 12px 20px;
            transition: border-color .16s ease, background-color .16s ease, color .16s ease;
        }

        .transfer-action--primary {
            background: #2C5EF5;
            border: 1px solid #2C5EF5;
            color: #FFFFFF;
        }

        .transfer-action--secondary {
            background: #FFFFFF;
            border: 1px solid #D9DEE6;
            color: #0F1419;
        }

        .transfer-action--secondary:hover {
            border-color: #4A5568;
            color: #0F1419;
        }

        @media (max-width: 575.98px) {
            .transfer-workflow-actions {
                display: grid;
                grid-template-columns: 1fr;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const actionInput = document.getElementById('transfer-workflow-action');

            if (!actionInput) {
                return;
            }

            document.querySelectorAll('[data-transfer-action]').forEach(function (button) {
                button.addEventListener('click', function () {
                    actionInput.value = button.dataset.transferAction || 'save';
                });
            });
        });
    </script>
@endif
