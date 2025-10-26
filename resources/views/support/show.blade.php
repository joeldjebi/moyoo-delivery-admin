@include('layouts.header')
@include('layouts.menu')


            <!-- Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h1 class="h3 mb-1">Ticket #{{ $ticket->ticket_number }}</h1>
                                    <p class="text-muted mb-0">{{ $ticket->subject }}</p>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('support.index') }}" class="btn btn-outline-secondary">
                                        <i class="ti ti-arrow-left me-2"></i>Retour
                                    </a>
                                    @if($ticket->isOpen() || $ticket->isInProgress())
                                        <form action="{{ route('support.update', $ticket->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="resolved">
                                            <button type="submit" class="btn btn-success" onclick="return confirm('Marquer ce ticket comme résolu ?')">
                                                <i class="ti ti-check me-2"></i>Marquer résolu
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Informations principales -->
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="ti ti-file-text me-2"></i>Description du problème
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="bg-light p-3 rounded">
                                <p class="mb-0">{{ $ticket->description }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Notes admin -->
                    @if($ticket->admin_notes)
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="ti ti-message-circle me-2"></i>Notes de l'équipe support
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="bg-info bg-opacity-10 p-3 rounded">
                                    <p class="mb-0">{{ $ticket->admin_notes }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Informations du ticket -->
                <div class="col-lg-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="ti ti-info-circle me-2"></i>Informations du ticket
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-medium">Statut</label>
                                    <div>
                                        <span class="badge bg-{{ $ticket->status_color }} fs-6">{{ $ticket->status_label }}</span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-medium">Priorité</label>
                                    <div>
                                        <span class="badge bg-{{ $ticket->priority_color }} fs-6">{{ $ticket->priority_label }}</span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-medium">Catégorie</label>
                                    <div>
                                        <span class="badge bg-light text-dark fs-6">{{ $ticket->category_label }}</span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-medium">Date de création</label>
                                    <div class="text-muted">
                                        {{ $ticket->created_at->format('d/m/Y à H:i') }}
                                    </div>
                                </div>
                                @if($ticket->resolved_at)
                                    <div class="col-12">
                                        <label class="form-label fw-medium">Date de résolution</label>
                                        <div class="text-muted">
                                            {{ $ticket->resolved_at->format('d/m/Y à H:i') }}
                                        </div>
                                    </div>
                                @endif
                                <div class="col-12">
                                    <label class="form-label fw-medium">Dernière mise à jour</label>
                                    <div class="text-muted">
                                        {{ $ticket->updated_at->format('d/m/Y à H:i') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informations de contact -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="ti ti-user me-2"></i>Informations de contact
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-medium">Créé par</label>
                                    <div class="text-muted">
                                        {{ $ticket->user->name ?? 'Utilisateur' }}
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-medium">Email de contact</label>
                                    <div class="text-muted">
                                        {{ $ticket->contact_email }}
                                    </div>
                                </div>
                                @if($ticket->contact_phone)
                                    <div class="col-12">
                                        <label class="form-label fw-medium">Téléphone</label>
                                        <div class="text-muted">
                                            {{ $ticket->contact_phone }}
                                        </div>
                                    </div>
                                @endif
                                @if($ticket->assignedTo)
                                    <div class="col-12">
                                        <label class="form-label fw-medium">Assigné à</label>
                                        <div class="text-muted">
                                            {{ $ticket->assignedTo->name }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Actions rapides -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="ti ti-settings me-2"></i>Actions
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                @if($ticket->isOpen() || $ticket->isInProgress())
                                    <form action="{{ route('support.update', $ticket->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="resolved">
                                        <button type="submit" class="btn btn-success w-100" onclick="return confirm('Marquer ce ticket comme résolu ?')">
                                            <i class="ti ti-check me-2"></i>Marquer résolu
                                        </button>
                                    </form>
                                @endif

                                @if($ticket->isResolved())
                                    <form action="{{ route('support.update', $ticket->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="closed">
                                        <button type="submit" class="btn btn-secondary w-100" onclick="return confirm('Fermer ce ticket ?')">
                                            <i class="ti ti-x me-2"></i>Fermer le ticket
                                        </button>
                                    </form>
                                @endif

                                <a href="mailto:{{ $ticket->contact_email }}?subject=Re: {{ $ticket->subject }}" class="btn btn-outline-primary w-100">
                                    <i class="ti ti-mail me-2"></i>Envoyer un email
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Historique des statuts -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="ti ti-history me-2"></i>Historique du ticket
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-primary"></div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title">Ticket créé</h6>
                                        <p class="timeline-text text-muted">Le ticket a été créé avec le statut "Ouvert"</p>
                                        <small class="text-muted">{{ $ticket->created_at->format('d/m/Y à H:i') }}</small>
                                    </div>
                                </div>

                                @if($ticket->isInProgress())
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-warning"></div>
                                        <div class="timeline-content">
                                            <h6 class="timeline-title">En cours de traitement</h6>
                                            <p class="timeline-text text-muted">L'équipe support a pris en charge votre ticket</p>
                                            <small class="text-muted">{{ $ticket->updated_at->format('d/m/Y à H:i') }}</small>
                                        </div>
                                    </div>
                                @endif

                                @if($ticket->isResolved())
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-success"></div>
                                        <div class="timeline-content">
                                            <h6 class="timeline-title">Ticket résolu</h6>
                                            <p class="timeline-text text-muted">Votre problème a été résolu</p>
                                            <small class="text-muted">{{ $ticket->resolved_at->format('d/m/Y à H:i') }}</small>
                                        </div>
                                    </div>
                                @endif

                                @if($ticket->isClosed())
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-secondary"></div>
                                        <div class="timeline-content">
                                            <h6 class="timeline-title">Ticket fermé</h6>
                                            <p class="timeline-text text-muted">Le ticket a été fermé</p>
                                            <small class="text-muted">{{ $ticket->updated_at->format('d/m/Y à H:i') }}</small>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>


<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #e9ecef;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid #dee2e6;
}

.timeline-title {
    margin-bottom: 5px;
    font-weight: 600;
}

.timeline-text {
    margin-bottom: 5px;
}
</style>

@include('layouts.footer')
