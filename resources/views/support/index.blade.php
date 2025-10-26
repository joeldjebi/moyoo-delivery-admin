@include('layouts.header')
@include('layouts.menu')


                <div class="container-xxl flex-grow-1 container-p-y">
                    <!-- Header -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body text-center py-4">
                                    <div class="avatar avatar-xl mx-auto mb-3">
                                        <span class="avatar-initial rounded bg-primary">
                                            <i class="ti ti-headset fs-1"></i>
                                        </span>
                                    </div>
                                    <h1 class="display-6 fw-bold text-primary mb-2">Support & Tickets</h1>
                                    <p class="fs-5 text-muted mb-0">Gérez vos demandes de support et suivez vos tickets</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistiques -->
                    <div class="row mb-4">
                        <div class="col-md-2 col-sm-6 mb-3">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-lg mx-auto mb-2">
                                        <span class="avatar-initial rounded bg-primary">
                                            <i class="ti ti-ticket fs-4"></i>
                                        </span>
                                    </div>
                                    <h4 class="mb-1">{{ $stats['total'] }}</h4>
                                    <p class="text-muted mb-0 small">Total</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-6 mb-3">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-lg mx-auto mb-2">
                                        <span class="avatar-initial rounded bg-info">
                                            <i class="ti ti-circle-plus fs-4"></i>
                                        </span>
                                    </div>
                                    <h4 class="mb-1">{{ $stats['open'] }}</h4>
                                    <p class="text-muted mb-0 small">Ouverts</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-6 mb-3">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-lg mx-auto mb-2">
                                        <span class="avatar-initial rounded bg-warning">
                                            <i class="ti ti-clock fs-4"></i>
                                        </span>
                                    </div>
                                    <h4 class="mb-1">{{ $stats['in_progress'] }}</h4>
                                    <p class="text-muted mb-0 small">En cours</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-6 mb-3">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-lg mx-auto mb-2">
                                        <span class="avatar-initial rounded bg-success">
                                            <i class="ti ti-check fs-4"></i>
                                        </span>
                                    </div>
                                    <h4 class="mb-1">{{ $stats['resolved'] }}</h4>
                                    <p class="text-muted mb-0 small">Résolus</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-6 mb-3">
                            <div class="card border-0 shadow-sm">
                                <div class="avatar avatar-lg mx-auto mb-2">
                                    <span class="avatar-initial rounded bg-secondary">
                                            <i class="ti ti-x fs-4"></i>
                                        </span>
                                    </div>
                                    <h4 class="mb-1">{{ $stats['closed'] }}</h4>
                                    <p class="text-muted mb-0 small">Fermés</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-6 mb-3">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <a href="{{ route('support.create') }}" class="btn btn-primary w-100">
                                        <i class="ti ti-plus me-2"></i>Nouveau Ticket
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Liste des tickets -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="ti ti-list me-2"></i>Mes Tickets de Support
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if($tickets->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>N° Ticket</th>
                                                        <th>Sujet</th>
                                                        <th>Catégorie</th>
                                                        <th>Priorité</th>
                                                        <th>Statut</th>
                                                        <th>Date</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($tickets as $ticket)
                                                        <tr>
                                                            <td>
                                                                <span class="fw-bold text-primary">{{ $ticket->ticket_number }}</span>
                                                            </td>
                                                            <td>
                                                                <div class="d-flex flex-column">
                                                                    <span class="fw-medium">{{ Str::limit($ticket->subject, 50) }}</span>
                                                                    <small class="text-muted">{{ Str::limit($ticket->description, 80) }}</small>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-light text-dark">{{ $ticket->category_label }}</span>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-{{ $ticket->priority_color }}">{{ $ticket->priority_label }}</span>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-{{ $ticket->status_color }}">{{ $ticket->status_label }}</span>
                                                            </td>
                                                            <td>
                                                                <div class="d-flex flex-column">
                                                                    <small class="text-muted">{{ $ticket->created_at->format('d/m/Y') }}</small>
                                                                    <small class="text-muted">{{ $ticket->created_at->format('H:i') }}</small>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="dropdown">
                                                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                                        <i class="ti ti-dots-vertical"></i>
                                                                    </button>
                                                                    <ul class="dropdown-menu">
                                                                        <li>
                                                                            <a class="dropdown-item" href="{{ route('support.show', $ticket->id) }}">
                                                                                <i class="ti ti-eye me-2"></i>Voir détails
                                                                            </a>
                                                                        </li>
                                                                        @if($ticket->isOpen() || $ticket->isInProgress())
                                                                            <li>
                                                                                <form action="{{ route('support.update', $ticket->id) }}" method="POST" class="d-inline">
                                                                                    @csrf
                                                                                    @method('PATCH')
                                                                                    <input type="hidden" name="status" value="resolved">
                                                                                    <button type="submit" class="dropdown-item text-success" onclick="return confirm('Marquer ce ticket comme résolu ?')">
                                                                                        <i class="ti ti-check me-2"></i>Marquer résolu
                                                                                    </button>
                                                                                </form>
                                                                            </li>
                                                                        @endif
                                                                    </ul>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Pagination -->
                                        <div class="d-flex justify-content-center mt-4">
                                            {{ $tickets->links() }}
                                        </div>
                                    @else
                                        <div class="text-center py-5">
                                            <div class="avatar avatar-4xl mx-auto mb-3">
                                                <span class="avatar-initial rounded bg-light">
                                                    <i class="ti ti-ticket fs-1 text-muted"></i>
                                                </span>
                                            </div>
                                            <h5 class="text-muted mb-2">Aucun ticket de support</h5>
                                            <p class="text-muted mb-4">Vous n'avez pas encore créé de ticket de support.</p>
                                            <a href="{{ route('support.create') }}" class="btn btn-primary">
                                                <i class="ti ti-plus me-2"></i>Créer votre premier ticket
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Guide rapide -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="ti ti-info-circle me-2"></i>Guide Rapide
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-start">
                                                <div class="avatar avatar-sm me-3">
                                                    <span class="avatar-initial rounded bg-primary">
                                                        <i class="ti ti-plus fs-4"></i>
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">Créer un ticket</h6>
                                                    <p class="text-muted small mb-0">Décrivez votre problème en détail pour une résolution rapide.</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-start">
                                                <div class="avatar avatar-sm me-3">
                                                    <span class="avatar-initial rounded bg-warning">
                                                        <i class="ti ti-clock fs-4"></i>
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">Suivi en temps réel</h6>
                                                    <p class="text-muted small mb-0">Consultez l'état de vos tickets à tout moment.</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-start">
                                                <div class="avatar avatar-sm me-3">
                                                    <span class="avatar-initial rounded bg-success">
                                                        <i class="ti ti-headset fs-4"></i>
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">Support 24/7</h6>
                                                    <p class="text-muted small mb-0">Notre équipe vous répond dans les plus brefs délais.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


@include('layouts.footer')
