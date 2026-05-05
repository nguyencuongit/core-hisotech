@extends($layout ?? BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="stock-page">
    <style>
        .stock-page {
            background: #f8fafc;
            min-height: 100vh;
            padding: 24px;
        }

        .stock-card {
            border: 1px solid #eef2f7;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
        }

        .stock-summary-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            flex-shrink: 0;
        }

        .stock-product-thumb {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: #f1f5f9;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .stock-table th,
        .stock-detail-table th {
            background: #f8fafc;
            color: #64748b;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
        }

        .stock-table td,
        .stock-detail-table td {
            font-size: 14px;
            vertical-align: middle;
            white-space: nowrap;
        }

        .stock-table tbody tr.active {
            background: #eff6ff;
        }

        .stock-table tbody tr.active td {
            background: transparent;
        }

        .stock-tabs {
            display: flex;
            gap: 20px;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 18px;
        }

        .stock-tabs button {
            border: 0;
            background: transparent;
            padding: 0 0 12px;
            color: #64748b;
            font-weight: 600;
        }

        .stock-tabs button.active {
            color: #0d8bff;
            border-bottom: 2px solid #0d8bff;
        }

        .stock-detail-stats {
            background: #f8fafc;
            border-radius: 14px;
            padding: 16px;
        }

        .stock-detail-stats span {
            display: block;
            color: #94a3b8;
            font-size: 13px;
        }

        .stock-detail-stats strong {
            display: block;
            margin-top: 6px;
            color: #0f172a;
            font-size: 18px;
        }

        .stock-badge {
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }

        .stock-badge-success {
            background: #dcfce7;
            color: #16a34a;
        }

        .stock-badge-info {
            background: #e0f2fe;
            color: #0284c7;
        }

        .stock-badge-warning {
            background: #ffedd5;
            color: #ea580c;
        }

        .stock-text-success {
            color: #16a34a !important;
        }

        .stock-text-warning {
            color: #f97316 !important;
        }

        @media (max-width: 1199px) {
            .stock-page {
                padding: 16px;
            }
        }
    </style>

    @php
        $products = [
            [
                'name' => 'Áo thun nam',
                'variant' => 'Màu trắng - Size L',
                'sku' => 'ATN-L-W',
                'unit' => 'Cái',
                'quantity' => 1200,
                'reserved' => 120,
                'available' => 1080,
                'value' => '24,000,000',
            ],
            [
                'name' => 'Quần jean nam',
                'variant' => 'Xanh - Size 32',
                'sku' => 'QJN-32-X',
                'unit' => 'Cái',
                'quantity' => 800,
                'reserved' => 80,
                'available' => 720,
                'value' => '32,000,000',
            ],
            [
                'name' => 'Giày thể thao',
                'variant' => 'Trắng - Size 42',
                'sku' => 'GTTH-42-W',
                'unit' => 'Đôi',
                'quantity' => 350,
                'reserved' => 50,
                'available' => 300,
                'value' => '52,500,000',
                'active' => true,
            ],
            [
                'name' => 'Ba lô du lịch',
                'variant' => 'Đen',
                'sku' => 'BLDL-001',
                'unit' => 'Cái',
                'quantity' => 150,
                'reserved' => 10,
                'available' => 140,
                'value' => '15,000,000',
            ],
            [
                'name' => 'Nón lưỡi trai',
                'variant' => 'Đen',
                'sku' => 'NLT-001',
                'unit' => 'Cái',
                'quantity' => 600,
                'reserved' => 60,
                'available' => 540,
                'value' => '12,000,000',
            ],
            [
                'name' => 'Tất nam cổ cao',
                'variant' => 'Trắng',
                'sku' => 'TAT-001',
                'unit' => 'Đôi',
                'quantity' => 3000,
                'reserved' => 300,
                'available' => 2700,
                'value' => '9,000,000',
            ],
            [
                'name' => 'Áo khoác gió',
                'variant' => 'Đen - Size XL',
                'sku' => 'AKG-001',
                'unit' => 'Cái',
                'quantity' => 120,
                'reserved' => 0,
                'available' => 120,
                'value' => '36,000,000',
            ],
        ];

        $stockDetails = [
            ['lot' => 'LOT20240501', 'expiry' => '01/05/2026', 'location' => 'Kệ A01', 'quantity' => 120, 'reserved' => 20, 'available' => 100],
            ['lot' => 'LOT20240615', 'expiry' => '15/06/2026', 'location' => 'Kệ A02', 'quantity' => 100, 'reserved' => 10, 'available' => 90],
            ['lot' => 'LOT20240720', 'expiry' => '20/07/2026', 'location' => 'Kệ B01', 'quantity' => 80, 'reserved' => 10, 'available' => 70],
            ['lot' => 'LOT20240830', 'expiry' => '30/08/2026', 'location' => 'Kệ B02', 'quantity' => 50, 'reserved' => 10, 'available' => 40],
        ];

        $reservations = [
            ['code' => 'SO24050123', 'customer' => 'Công ty TNHH XYZ', 'qty' => 30, 'status' => 'Đã xác nhận', 'class' => 'stock-badge-info'],
            ['code' => 'SO24050145', 'customer' => 'Cửa hàng ABC', 'qty' => 20, 'status' => 'Đang lấy hàng', 'class' => 'stock-badge-warning'],
        ];
    @endphp

    <div class="container-fluid px-0">
        <div class="row g-3 mb-4 align-items-center">
            <div class="col-xl-2 col-lg-3 col-md-6">
                <select class="form-control">
                    <option>Kho: Kho chính</option>
                    <option>Kho Hà Nội</option>
                    <option>Kho Hồ Chí Minh</option>
                </select>
            </div>

            <div class="col-xl-4 col-lg-3 col-md-6">
                <div class="position-relative">
                    <input type="text" class="form-control pe-5" placeholder="Tìm kiếm sản phẩm...">
                    <i class="ti ti-search position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
                </div>
            </div>

            <div class="col-xl-2 col-lg-2 col-md-6">
                <select class="form-control">
                    <option>Danh mục: Tất cả</option>
                    <option>Áo</option>
                    <option>Quần</option>
                    <option>Giày</option>
                </select>
            </div>

            <div class="col-xl-2 col-lg-2 col-md-6">
                <select class="form-control">
                    <option>Trạng thái: Tất cả</option>
                    <option>Còn hàng</option>
                    <option>Sắp hết</option>
                    <option>Hết hàng</option>
                </select>
            </div>

            <div class="col-xl-2 col-lg-2 col-md-12">
                <button type="button" class="btn btn-primary w-100">
                    <i class="ti ti-download me-1"></i>
                    Xuất báo cáo
                </button>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-xl col-lg-4 col-md-6">
                <div class="card stock-card border-0 h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stock-summary-icon bg-primary-subtle text-primary">
                            <i class="ti ti-box"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Tổng sản phẩm</div>
                            <div class="h3 mb-0 fw-bold">256</div>
                            <div class="text-muted small">SKU</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl col-lg-4 col-md-6">
                <div class="card stock-card border-0 h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stock-summary-icon bg-success-subtle text-success">
                            <i class="ti ti-package"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Tổng tồn kho</div>
                            <div class="h3 mb-0 fw-bold">12,458</div>
                            <div class="text-muted small">Đơn vị</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl col-lg-4 col-md-6">
                <div class="card stock-card border-0 h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stock-summary-icon bg-warning-subtle text-warning">
                            <i class="ti ti-lock"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Đã giữ</div>
                            <div class="h3 mb-0 fw-bold">1,234</div>
                            <div class="text-muted small">Reserved</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl col-lg-4 col-md-6">
                <div class="card stock-card border-0 h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stock-summary-icon bg-success-subtle text-success">
                            <i class="ti ti-circle-check"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Có thể xuất</div>
                            <div class="h3 mb-0 fw-bold">11,224</div>
                            <div class="text-muted small">Đơn vị</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl col-lg-4 col-md-6">
                <div class="card stock-card border-0 h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stock-summary-icon bg-purple-subtle text-purple">
                            <i class="ti ti-currency-dollar"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Giá trị tồn kho</div>
                            <div class="h3 mb-0 fw-bold">2.45 tỷ</div>
                            <div class="text-muted small">VNĐ</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-xl-8">
                <div class="card stock-card border-0">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="mb-0 fw-bold">Danh sách sản phẩm</h5>
                    </div>

                    <div class="card-body px-0 pb-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 stock-table">
                                <thead>
                                    <tr>
                                        <th class="ps-4">#</th>
                                        <th>Sản phẩm</th>
                                        <th class="text-end">Tổng tồn</th>
                                        <th class="text-end">Đã giữ</th>
                                        <th class="text-end">Có thể xuất</th>
                                        <th class="text-end">Giá trị tồn kho</th>
                                        <th class="text-end pe-4"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($products as $index => $product)
                                        <tr class="{{ !empty($product['active']) ? 'active' : '' }}">
                                            <td class="ps-4">{{ $index + 1 }}</td>
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="stock-product-thumb">
                                                        <i class="ti ti-package"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold text-dark">{{ $product['name'] }}</div>
                                                        <div class="small text-muted">{{ $product['sku'] }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-end">{{ number_format($product['quantity']) }}</td>
                                            <td class="text-end stock-text-warning">{{ number_format($product['reserved']) }}</td>
                                            <td class="text-end stock-text-success">{{ number_format($product['available']) }}</td>
                                            <td class="text-end">{{ $product['value'] }}</td>
                                            <td class="text-end pe-4">
                                                <a href="#" class="text-muted">
                                                    <i class="ti ti-chevron-right"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top">
                            <div class="small text-muted">
                                Hiển thị 1 đến 7 của 256 kết quả
                            </div>

                            <nav>
                                <ul class="pagination pagination-sm mb-0">
                                    <li class="page-item active">
                                        <a class="page-link" href="#">1</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="#">2</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="#">3</a>
                                    </li>
                                    <li class="page-item disabled">
                                        <a class="page-link" href="#">...</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="#">32</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card stock-card border-0 mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stock-product-thumb">
                                    <i class="ti ti-shoe"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1 fw-bold">Giày thể thao</h5>
                                    <div class="text-muted small">Trắng - Size 42</div>
                                </div>
                            </div>

                            <span class="stock-badge stock-badge-success">Đang kinh doanh</span>
                        </div>

                        <div class="stock-tabs">
                            <button type="button" class="active">Theo lô - HSD</button>
                            <button type="button">Theo vị trí</button>
                            <button type="button">Giữ hàng</button>
                        </div>

                        <div class="row g-0 text-center stock-detail-stats mb-4">
                            <div class="col">
                                <span>Tổng tồn</span>
                                <strong>350</strong>
                            </div>
                            <div class="col border-start">
                                <span>Đã giữ</span>
                                <strong>50</strong>
                            </div>
                            <div class="col border-start">
                                <span>Có thể xuất</span>
                                <strong class="stock-text-success">300</strong>
                            </div>
                        </div>

                        <h6 class="fw-bold mb-3">Theo lô - Hạn sử dụng</h6>

                        <div class="table-responsive">
                            <table class="table stock-detail-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Lot / Lô</th>
                                        <th>HSD</th>
                                        
                                        <th class="text-end">Tồn</th>
                                        <th class="text-end">Giữ</th>
                                        <th class="text-end">Xuất</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stockDetails as $detail)
                                        <tr>
                                            <td>{{ $detail['lot'] }}</td>
                                            <td>{{ $detail['expiry'] }}</td>
                                            <td class="text-end">{{ number_format($detail['quantity']) }}</td>
                                            <td class="text-end">{{ number_format($detail['reserved']) }}</td>
                                            <td class="text-end stock-text-success">{{ number_format($detail['available']) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <button type="button" class="btn btn-light border w-100 mt-3">
                            Xem tất cả lô hàng
                        </button>
                    </div>
                </div>

                <!-- <div class="card stock-card border-0">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Phân bổ giữ hàng</h6>

                        <div class="table-responsive">
                            <table class="table stock-detail-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Đơn hàng</th>
                                        <th>Khách hàng</th>
                                        <th class="text-end">SL</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($reservations as $reservation)
                                        <tr>
                                            <td>
                                                <a href="#" class="fw-semibold text-primary">
                                                    {{ $reservation['code'] }}
                                                </a>
                                            </td>
                                            <td>{{ $reservation['customer'] }}</td>
                                            <td class="text-end">{{ number_format($reservation['qty']) }}</td>
                                            <td>
                                                <span class="stock-badge {{ $reservation['class'] }}">
                                                    {{ $reservation['status'] }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <button type="button" class="btn btn-light border w-100 mt-3">
                            Xem tất cả phân bổ
                        </button>
                    </div>
                </div> -->
            </div>
        </div>
    </div>
</div>
@endsection