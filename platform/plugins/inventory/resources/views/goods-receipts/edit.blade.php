@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="page-body">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">{{ trans('plugins/inventory::inventory.goods_receipt.edit') }}</h3>
                    <div>{!! $goodsReceipt->status?->toHtml() !!}</div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('inventory.goods-receipts.update', $goodsReceipt) }}">
                        @csrf
                        @method('PUT')
                        @include('plugins/inventory::goods-receipts.partials.form', ['goodsReceipt' => $goodsReceipt])
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
