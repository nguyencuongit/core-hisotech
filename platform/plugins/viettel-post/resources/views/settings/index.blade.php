@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
<div class="row">
    <div class="col-md-12">
        @include('plugins/viettel-post::settings.shipping-method')
    </div>
</div>
@stop