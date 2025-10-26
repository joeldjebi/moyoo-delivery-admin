@include('layouts.header')
@include('layouts.menu')


                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body text-center py-4">
                                    <div class="avatar avatar-xl mx-auto mb-3">
                                        <span class="avatar-initial rounded bg-primary">
                                            <i class="ti ti-plus fs-1"></i>
                                        </span>
                                    </div>
                                    <h1 class="display-6 fw-bold text-primary mb-2">Nouveau Ticket de Support</h1>
                                    <p class="fs-5 text-muted mb-0">Décrivez votre problème et nous vous aiderons rapidement</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Formulaire -->
                    <div class="row">
                        <div class="col-lg-8 mx-auto">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="ti ti-edit me-2"></i>Informations du Ticket
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('support.store') }}" method="POST">
                                        @csrf

                                        <!-- Sujet -->
                                        <div class="mb-4">
                                            <label for="subject" class="form-label">
                                                Sujet <span class="text-danger">*</span>
                                            </label>
                                            <input type="text"
                                                   class="form-control @error('subject') is-invalid @enderror"
                                                   id="subject"
                                                   name="subject"
                                                   value="{{ old('subject') }}"
                                                   placeholder="Résumez votre problème en quelques mots"
                                                   required>
                                            @error('subject')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Catégorie et Priorité -->
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <label for="category" class="form-label">
                                                    Catégorie <span class="text-danger">*</span>
                                                </label>
                                                <select class="form-select @error('category') is-invalid @enderror"
                                                        id="category"
                                                        name="category"
                                                        required>
                                                    <option value="">Sélectionnez une catégorie</option>
                                                    <option value="technical" {{ old('category') == 'technical' ? 'selected' : '' }}>Technique</option>
                                                    <option value="billing" {{ old('category') == 'billing' ? 'selected' : '' }}>Facturation</option>
                                                    <option value="feature_request" {{ old('category') == 'feature_request' ? 'selected' : '' }}>Demande de fonctionnalité</option>
                                                    <option value="bug_report" {{ old('category') == 'bug_report' ? 'selected' : '' }}>Rapport de bug</option>
                                                    <option value="general" {{ old('category') == 'general' ? 'selected' : '' }}>Général</option>
                                                </select>
                                                @error('category')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label for="priority" class="form-label">
                                                    Priorité <span class="text-danger">*</span>
                                                </label>
                                                <select class="form-select @error('priority') is-invalid @enderror"
                                                        id="priority"
                                                        name="priority"
                                                        required>
                                                    <option value="">Sélectionnez une priorité</option>
                                                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Faible</option>
                                                    <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Moyen</option>
                                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>Élevé</option>
                                                    <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                                </select>
                                                @error('priority')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- Description -->
                                        <div class="mb-4">
                                            <label for="description" class="form-label">
                                                Description détaillée <span class="text-danger">*</span>
                                            </label>
                                            <textarea class="form-control @error('description') is-invalid @enderror"
                                                      id="description"
                                                      name="description"
                                                      rows="6"
                                                      placeholder="Décrivez votre problème en détail. Plus vous fournirez d'informations, plus nous pourrons vous aider rapidement."
                                                      required>{{ old('description') }}</textarea>
                                            @error('description')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">
                                                <i class="ti ti-info-circle me-1"></i>
                                                Incluez les étapes pour reproduire le problème, les messages d'erreur, et toute information pertinente.
                                            </div>
                                        </div>

                                        <!-- Informations de contact -->
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <label for="contact_email" class="form-label">
                                                    Email de contact
                                                </label>
                                                <input type="email"
                                                       class="form-control @error('contact_email') is-invalid @enderror"
                                                       id="contact_email"
                                                       name="contact_email"
                                                       value="{{ old('contact_email', Auth::user()->email) }}"
                                                       placeholder="votre@email.com">
                                                @error('contact_email')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text">Laissez vide pour utiliser votre email de connexion</div>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="contact_phone" class="form-label">
                                                    Téléphone de contact
                                                </label>
                                                <input type="tel"
                                                       class="form-control @error('contact_phone') is-invalid @enderror"
                                                       id="contact_phone"
                                                       name="contact_phone"
                                                       value="{{ old('contact_phone') }}"
                                                       placeholder="+225 XX XX XX XX">
                                                @error('contact_phone')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text">Optionnel - pour un contact plus rapide</div>
                                            </div>
                                        </div>

                                        <!-- Boutons -->
                                        <div class="d-flex justify-content-between">
                                            <a href="{{ route('support.index') }}" class="btn btn-outline-secondary">
                                                <i class="ti ti-arrow-left me-2"></i>Retour
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="ti ti-send me-2"></i>Créer le Ticket
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Conseils -->
                    <div class="row mt-4">
                        <div class="col-lg-8 mx-auto">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="ti ti-lightbulb text-warning me-2"></i>Conseils pour un ticket efficace
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <ul class="list-unstyled small">
                                                <li class="mb-2">
                                                    <i class="ti ti-check text-success me-1"></i>
                                                    <strong>Soyez précis :</strong> Décrivez exactement ce qui ne fonctionne pas
                                                </li>
                                                <li class="mb-2">
                                                    <i class="ti ti-check text-success me-1"></i>
                                                    <strong>Incluez les étapes :</strong> Comment reproduire le problème
                                                </li>
                                                <li class="mb-2">
                                                    <i class="ti ti-check text-success me-1"></i>
                                                    <strong>Messages d'erreur :</strong> Copiez-collez les erreurs exactes
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <ul class="list-unstyled small">
                                                <li class="mb-2">
                                                    <i class="ti ti-check text-success me-1"></i>
                                                    <strong>Navigateur :</strong> Indiquez le navigateur utilisé
                                                </li>
                                                <li class="mb-2">
                                                    <i class="ti ti-check text-success me-1"></i>
                                                    <strong>Priorité :</strong> Choisissez la priorité appropriée
                                                </li>
                                                <li class="mb-2">
                                                    <i class="ti ti-check text-success me-1"></i>
                                                    <strong>Catégorie :</strong> Sélectionnez la bonne catégorie
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>



@include('layouts.footer')
