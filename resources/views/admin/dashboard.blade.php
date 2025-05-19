@extends('admin.layouts.app')

@section('content')
    <div class="container py-4">

        <h3 class="fw-bold mb-4 text-center">📊 hazley Admin Dashboard</h3>

        {{-- Top Cards --}}
        <div class="row g-4 mb-4">
            <x-dashboard.card title="Monthly Income" value="$15,500" icon="fas fa-wallet" color="bg-success" />
            <x-dashboard.card title="Daily Average Orders" value="78" icon="fas fa-chart-bar" color="bg-primary" />
            <x-dashboard.card title="Active Products" value="420" icon="fas fa-boxes" color="bg-info" />
            <x-dashboard.card title="Customers" value="1,204" icon="fas fa-users" color="bg-secondary" />
        </div>

        {{-- Monthly Sales Chart --}}
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title fw-bold mb-3">📈 Monthly Sales Overview</h5>
                <canvas id="salesChart" height="100"></canvas>
            </div>
        </div>

        {{-- Recent Orders --}}
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title fw-bold mb-3">🧾 Recent Orders</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Date</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach(range(1,5) as $i)
                            <tr>
                                <td>#ORD00{{ $i }}</td>
                                <td>Customer {{ $i }}</td>
                                <td>
                                    <span class="badge bg-{{ ['success','warning','danger','info','secondary'][$i % 5] }}">
                                        {{ ['Completed','Pending','Cancelled','Shipped','Processing'][$i % 5] }}
                                    </span>
                                </td>
                                <td>${{ rand(50, 300) }}</td>
                                <td>{{ now()->subDays($i)->format('Y-m-d') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('salesChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug'],
                datasets: [{
                    label: 'Sales in USD',
                    data: [1200, 1900, 3000, 2500, 3200, 2800, 3500, 4200],
                    backgroundColor: 'rgba(54, 162, 235, 0.3)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    tension: 0.3,
                    borderWidth: 3,
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 8,
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
@endsection
