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

<!-- Modals de planification -->
@foreach($ramassages as $ramassage)
    @if($ramassage->statut === 'demande')
        <div class="modal fade" id="planifierModal{{ $ramassage->id }}" tabindex="-1" aria-labelledby="planifierModalLabel{{ $ramassage->id }}" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="planifierModalLabel{{ $ramassage->id }}">
                            <i class="ti ti-calendar me-2"></i>
                            Planifier le ramassage {{ $ramassage->code_ramassage }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('ramassages.planifier', $ramassage->id) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="row g-3">
                                <!-- Informations du ramassage -->
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <h6 class="mb-2"><i class="ti ti-info-circle me-1"></i> Informations du ramassage</h6>
                                        <p class="mb-1"><strong>Marchand:</strong> {{ $ramassage->marchand->first_name }} {{ $ramassage->marchand->last_name }}</p>
                                        <p class="mb-1"><strong>Boutique:</strong> {{ $ramassage->boutique->libelle }}</p>
                                        <p class="mb-0"><strong>Colis estimés:</strong> {{ $ramassage->nombre_colis_estime }}</p>
                                    </div>
                                </div>

                                <!-- Sélection du livreur -->
                                <div class="col-md-6">
                                    <label for="livreur_id{{ $ramassage->id }}" class="form-label">Livreur <span class="text-danger">*</span></label>
                                    <select class="form-select @error('livreur_id') is-invalid @enderror" id="livreur_id{{ $ramassage->id }}" name="livreur_id" required>
                                        <option value="">Sélectionner un livreur</option>
                                        @foreach(\App\Models\Livreur::where('status', 'actif')->get() as $livreur)
                                            <option value="{{ $livreur->id }}" {{ old('livreur_id') == $livreur->id ? 'selected' : '' }}>
                                                {{ $livreur->first_name }} {{ $livreur->last_name }} - {{ $livreur->engin->libelle ?? 'N/A' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('livreur_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Date de planification -->
                                <div class="col-md-6">
                                    <label for="date_planifiee{{ $ramassage->id }}" class="form-label">Date de ramassage <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('date_planifiee') is-invalid @enderror"
                                           id="date_planifiee{{ $ramassage->id }}" name="date_planifiee"
                                           value="{{ old('date_planifiee', date('Y-m-d', strtotime('+1 day'))) }}"
                                           min="{{ date('Y-m-d') }}" required>
                                    @error('date_planifiee')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Heure de planification -->
                                <div class="col-md-4">
                                    <label for="heure_planifiee{{ $ramassage->id }}" class="form-label">Heure de ramassage <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control @error('heure_planifiee') is-invalid @enderror"
                                           id="heure_planifiee{{ $ramassage->id }}" name="heure_planifiee"
                                           value="{{ old('heure_planifiee', '08:00') }}" required>
                                    @error('heure_planifiee')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Heure de début -->
                                <div class="col-md-4">
                                    <label for="heure_debut{{ $ramassage->id }}" class="form-label">Heure de début <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control @error('heure_debut') is-invalid @enderror"
                                           id="heure_debut{{ $ramassage->id }}" name="heure_debut"
                                           value="{{ old('heure_debut', '08:00') }}" required>
                                    @error('heure_debut')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Heure de fin -->
                                <div class="col-md-4">
                                    <label for="heure_fin{{ $ramassage->id }}" class="form-label">Heure de fin <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control @error('heure_fin') is-invalid @enderror"
                                           id="heure_fin{{ $ramassage->id }}" name="heure_fin"
                                           value="{{ old('heure_fin', '17:00') }}" required>
                                    @error('heure_fin')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Adresse de ramassage -->
                                <div class="col-12">
                                    <label for="adresse_ramassage{{ $ramassage->id }}" class="form-label">Adresse de ramassage <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('adresse_ramassage') is-invalid @enderror"
                                              id="adresse_ramassage{{ $ramassage->id }}" name="adresse_ramassage"
                                              rows="3" placeholder="Adresse complète du ramassage" required>{{ old('adresse_ramassage', $ramassage->boutique->adresse ?? '') }}</textarea>
                                    @error('adresse_ramassage')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Notes de planification -->
                                <div class="col-12">
                                    <label for="notes_planification{{ $ramassage->id }}" class="form-label">Notes de planification</label>
                                    <textarea class="form-control @error('notes_planification') is-invalid @enderror"
                                              id="notes_planification{{ $ramassage->id }}" name="notes_planification"
                                              rows="2" placeholder="Instructions spéciales ou notes pour le livreur">{{ old('notes_planification') }}</textarea>
                                    @error('notes_planification')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="ti ti-x me-1"></i>
                                Annuler
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-calendar me-1"></i>
                                Planifier le ramassage
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endforeach

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validation des heures
    const modals = document.querySelectorAll('[id^="planifierModal"]');
    modals.forEach(modal => {
        const modalId = modal.id;
        const ramassageId = modalId.replace('planifierModal', '');

        const heureDebut = document.getElementById('heure_debut' + ramassageId);
        const heureFin = document.getElementById('heure_fin' + ramassageId);

        if (heureDebut && heureFin) {
            heureDebut.addEventListener('change', function() {
                if (this.value && heureFin.value && this.value >= heureFin.value) {
                    heureFin.value = '';
                    heureFin.setCustomValidity('L\'heure de fin doit être après l\'heure de début');
                } else {
                    heureFin.setCustomValidity('');
                }
            });

            heureFin.addEventListener('change', function() {
                if (this.value && heureDebut.value && this.value <= heureDebut.value) {
                    this.setCustomValidity('L\'heure de fin doit être après l\'heure de début');
                } else {
                    this.setCustomValidity('');
                }
            });
        }
    });
});
</script>
