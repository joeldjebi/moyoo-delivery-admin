@include('layouts.header')

@include('layouts.menu')

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Gestion des Ramassages</h5>
                    <a href="{{ route('ramassages.create') }}" class="btn btn-primary">
                        <i class="ti ti-plus me-1"></i>
                        Nouveau Ramassage
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Marchand</th>
                                    <th>Boutique</th>
                                    <th>Date Demande</th>
                                    <th>Date de ramassage</th>
                                    <th>Livreur</th>
                                    <th>Statut</th>
                                    <th>Colis Estimés</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ramassages as $ramassage)
                                    <tr>
                                        <td>
                                            <span class="fw-semibold">{{ $ramassage->code_ramassage }}</span>
                                        </td>
                                        <td>{{ $ramassage->marchand->first_name ?? '' }} {{ $ramassage->marchand->last_name ?? '' }}</td>
                                        <td>{{ $ramassage->boutique->libelle ?? 'N/A' }}</td>
                                        <td>{{ $ramassage->date_demande->format('d/m/Y') }}</td>
                                        <td>
                                            @if($ramassage->date_planifiee)
                                                {{ $ramassage->date_planifiee->format('d/m/Y') }}
                                            @else
                                                <span class="text-muted">Non planifié</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($ramassage->planifications->count() > 0)
                                                @php
                                                    $livreur = $ramassage->planifications->first()->livreur;
                                                @endphp
                                                @if($livreur)
                                                    <span class="fw-semibold">{{ $livreur->first_name }} {{ $livreur->last_name }}</span>
                                                @else
                                                    <span class="text-muted">Livreur non trouvé</span>
                                                @endif
                                            @else
                                                <span class="text-muted">Non assigné</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $ramassage->statut_color }}">
                                                {{ $ramassage->statut_label }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $ramassage->nombre_colis_estime }}</span>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="{{ route('ramassages.show', $ramassage->id) }}">
                                                        <i class="ti ti-eye me-1"></i> Voir
                                                    </a>
                                                    <a class="dropdown-item" href="{{ route('ramassages.edit', $ramassage->id) }}">
                                                        <i class="ti ti-pencil me-1"></i> Modifier
                                                    </a>
                                                    @if($ramassage->statut === 'demande')
                                                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#planifierModal{{ $ramassage->id }}">
                                                            <i class="ti ti-calendar me-1"></i> Planifier
                                                        </a>
                                                    @endif
                                                    <div class="dropdown-divider"></div>
                                                    <form action="{{ route('ramassages.destroy', $ramassage->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce ramassage ?')">
                                                            <i class="ti ti-trash me-1"></i> Supprimer
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="ti ti-package-off display-4 text-muted mb-2"></i>
                                                <p class="text-muted">Aucun ramassage trouvé</p>
                                                <a href="{{ route('ramassages.create') }}" class="btn btn-primary">
                                                    <i class="ti ti-plus me-1"></i>
                                                    Créer le premier ramassage
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($ramassages->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $ramassages->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@include('layouts.footer')
