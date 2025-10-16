@include('layouts.header')
@include('layouts.menu')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="card-title mb-0">
                        <h5 class="m-0 me-2">Rapports et Statistiques</h5>
                        <small class="text-muted">Vue d'ensemble des performances de livraison</small>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-text-secondary rounded-pill text-muted border-0 p-2 me-n1" type="button" id="rapportsDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="ti ti-dots-vertical"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="rapportsDropdown">
                            <a class="dropdown-item" href="#" onclick="exportRapport('general')">
                                <i class="ti ti-download me-2"></i>Exporter le rapport général
                            </a>
                            <a class="dropdown-item" href="#" onclick="exportRapport('livraisons')">
                                <i class="ti ti-truck me-2"></i>Exporter les livraisons
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Statistiques générales -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <i class="ti ti-package ti-48px"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h4 class="mb-0">{{ number_format($stats['colis']['total']) }}</h4>
                                            <p class="mb-0">Total Colis</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <i class="ti ti-truck ti-48px"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h4 class="mb-0">{{ number_format($stats['livraisons']['livrees']) }}</h4>
                                            <p class="mb-0">Livraisons Réussies</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <i class="ti ti-clock ti-48px"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h4 class="mb-0">{{ number_format($stats['livraisons']['en_cours']) }}</h4>
                                            <p class="mb-0">En Cours</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <i class="ti ti-percentage ti-48px"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h4 class="mb-0">{{ $stats['livraisons']['taux_reussite'] }}%</h4>
                                            <p class="mb-0">Taux de Réussite</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Graphique d'évolution -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Évolution des 12 derniers mois</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="evolutionChart" height="100"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rapports disponibles -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="ti ti-truck ti-48px text-primary mb-3"></i>
                                    <h5 class="card-title">Rapport de Livraisons</h5>
                                    <p class="card-text">Analyse détaillée des livraisons et performances</p>
                                    <a href="{{ route('rapports.show', 'livraisons') }}" class="btn btn-primary">
                                        <i class="ti ti-eye me-1"></i>Voir le rapport
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="ti ti-package ti-48px text-success mb-3"></i>
                                    <h5 class="card-title">Rapport de Colis</h5>
                                    <p class="card-text">Statistiques et analyse des colis traités</p>
                                    <a href="{{ route('rapports.show', 'colis') }}" class="btn btn-success">
                                        <i class="ti ti-eye me-1"></i>Voir le rapport
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="ti ti-package-up ti-48px text-warning mb-3"></i>
                                    <h5 class="card-title">Rapport de Ramassages</h5>
                                    <p class="card-text">Analyse des ramassages et collectes</p>
                                    <a href="{{ route('rapports.show', 'ramassages') }}" class="btn btn-warning">
                                        <i class="ti ti-eye me-1"></i>Voir le rapport
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="ti ti-currency-franc ti-48px text-info mb-3"></i>
                                    <h5 class="card-title">Rapport de Frais</h5>
                                    <p class="card-text">Analyse des frais de livraison appliqués</p>
                                    <a href="{{ route('rapports.show', 'frais') }}" class="btn btn-info">
                                        <i class="ti ti-eye me-1"></i>Voir le rapport
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Graphique d'évolution
    const ctx = document.getElementById('evolutionChart').getContext('2d');
    const evolutionData = @json($chartData);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: evolutionData.map(item => item.month),
            datasets: [{
                label: 'Colis',
                data: evolutionData.map(item => item.colis),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }, {
                label: 'Livraisons',
                data: evolutionData.map(item => item.livraisons),
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});

function exportRapport(type) {
    // Implémentation de l'export
    alert('Fonction d\'export à implémenter pour le type: ' + type);
}
</script>

@include('layouts.footer')
