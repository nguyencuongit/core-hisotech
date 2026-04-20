@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')

<style>
    .card-box {
        border-radius: 12px;
    }
    .progress {
        height: 6px;
    }
</style>

<div class="container py-4 bg-light border rounded-3">

    <h4 class="mb-4">Dashboard</h4>

    <!-- TOP CARDS -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card card-box p-3">
                <div class="d-flex justify-content-between">
                    <span class="text-success">
                        <i class="fas fa-truck"></i> LOCKER PACKAGES
                    </span>
                    <strong>19</strong>
                </div>
                <div class="progress mt-3">
                    <div class="progress-bar bg-success w-100"></div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-box p-3">
                <div class="d-flex justify-content-between">
                    <span class="text-primary">
                        <i class="fas fa-shipping-fast"></i> SHIPPINGS
                    </span>
                    <strong>33</strong>
                </div>
                <div class="progress mt-3">
                    <div class="progress-bar bg-primary w-100"></div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-box p-3">
                <div class="d-flex justify-content-between">
                    <span class="text-warning">
                        <i class="fas fa-hand-holding"></i> PICKUP
                    </span>
                    <strong>0</strong>
                </div>
                <div class="progress mt-3">
                    <div class="progress-bar bg-warning w-100"></div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-box p-3">
                <div class="d-flex justify-content-between">
                    <span class="text-info">
                        <i class="fas fa-snowflake"></i> CONSOLIDATED
                    </span>
                    <strong>1</strong>
                </div>
                <div class="progress mt-3">
                    <div class="progress-bar bg-info w-100"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- CHARTS -->
    <div class="row g-4">
        <!-- Pie -->
        <div class="col-md-6">
            <div class="card p-3">
                <div class="d-flex justify-content-between mb-3">
                    <h6>Package by Status</h6>
                    <i class="fas fa-bars"></i>
                </div>
                <div style="height:300px">
                    <canvas id="pieChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Line -->
        <div class="col-md-6">
            <div class="card p-3">
                <div class="d-flex justify-content-between mb-3">
                    <h6>Package Sales Graph</h6>
                    <i class="fas fa-bars"></i>
                </div>
                <div style="height:300px">
                    <canvas id="lineChart"></canvas>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection


@push('footer')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // PIE CHART
    const pieEl = document.getElementById('pieChart');
    if (pieEl) {
        new Chart(pieEl, {
            type: 'doughnut',
            data: {
                labels: ['Rejected', 'Pending', 'Picked up', 'Delivered', 'Consolidate'],
                datasets: [{
                    data: [5, 60, 10, 10, 15],
                    backgroundColor: [
                        '#0d6efd',
                        '#6f42c1',
                        '#198754',
                        '#20c997',
                        '#fd7e14'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    // LINE CHART
    const lineEl = document.getElementById('lineChart');
    if (lineEl) {
        new Chart(lineEl, {
            type: 'line',
            data: {
                labels: [
                    'January','February','March','April','May','June',
                    'July','August','September','October','November','December'
                ],
                datasets: [{
                    label: 'USD',
                    data: [0,0,0,0,0,0,0,0,0,0,0,0],
                    borderColor: '#0d6efd',
                    tension: 0.3,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

});
</script>
@endpush