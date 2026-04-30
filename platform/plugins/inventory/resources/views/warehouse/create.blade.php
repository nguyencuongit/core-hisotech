@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="page-body warehouse-product-page">
        <div class="container-fluid">
            <div class="warehouse-hero mb-4">
                <div class="d-flex justify-content-between gap-3 flex-wrap">
                    <div>
                        <div class="warehouse-kicker">Kho mới</div>
                        <h2 class="warehouse-title">Tạo kho</h2>
                        <div class="warehouse-muted">Sau khi tạo kho, bạn có thể chọn mẫu kho có sẵn để sinh cây vị trí tự động.</div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="warehouse-soft-card h-100">
                        <h3 class="warehouse-section-title mb-3">Thông tin kho</h3>
                        {!! \Botble\Inventory\Domains\Warehouse\Forms\WarehouseForm::create()->renderForm() !!}
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="warehouse-soft-card h-100">
                        <h3 class="warehouse-section-title mb-3">Bắt đầu từ đâu?</h3>
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="warehouse-product-card">
                                    <div class="fw-semibold mb-1">Tạo thủ công</div>
                                    <div class="warehouse-muted">Phù hợp nếu bạn muốn tự xây cây vị trí từ đầu.</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <a href="{{ route('inventory.warehouse.templates.index') }}" class="btn btn-primary warehouse-primary-btn">Chọn mẫu kho có sẵn</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
