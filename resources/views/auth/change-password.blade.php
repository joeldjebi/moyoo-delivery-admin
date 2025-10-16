@include('layouts.header')
@include('layouts.menu')
<div class="row">
    <div class="col-lg-12 mb-4 order-0">
        <div class="card">
            <div class="row">
                <div class="col-sm-7">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Changer le Mot de Passe</h5>
                        <p class="mb-4">Modifiez votre mot de passe de manière sécurisée</p>
                    </div>
                </div>
                <div class="col-sm-5 text-center text-sm-left">
                    <div class="card-body pb-0 px-0 px-md-4">
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('auth.profile') }}" class="btn btn-outline-info">
                                <i class="ti ti-user me-1"></i>
                                Mon Profil
                            </a>
                            <a href="{{ route('entreprise.index') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-arrow-left me-1"></i>
                                Retour
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Messages d'erreur -->
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Messages de succès -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="ti ti-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Formulaire de changement de mot de passe -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Modification du Mot de Passe</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('auth.password.update') }}">
                    @csrf
                    @method('POST')

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="current_password" class="form-label">Mot de Passe Actuel <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                                   id="current_password" name="current_password" required>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Entrez votre mot de passe actuel pour confirmer votre identité.</div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="new_password" class="form-label">Nouveau Mot de Passe <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('new_password') is-invalid @enderror"
                                   id="new_password" name="new_password" required>
                            @error('new_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Le mot de passe doit contenir au moins 8 caractères.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="new_password_confirmation" class="form-label">Confirmer le Nouveau Mot de Passe <span class="text-danger">*</span></label>
                            <input type="password" class="form-control"
                                   id="new_password_confirmation" name="new_password_confirmation" required>
                            <div class="form-text">Répétez le nouveau mot de passe pour confirmation.</div>
                        </div>
                    </div>

                    <!-- Indicateur de force du mot de passe -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="password-strength">
                                <div class="progress" style="height: 5px;">
                                    <div class="progress-bar" id="password-strength-bar" role="progressbar" style="width: 0%"></div>
                                </div>
                                <small class="text-muted" id="password-strength-text">Force du mot de passe</small>
                            </div>
                        </div>
                    </div>

                    <!-- Conseils de sécurité -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <h6 class="alert-heading">
                                    <i class="ti ti-shield-check me-2"></i>
                                    Conseils pour un mot de passe sécurisé :
                                </h6>
                                <ul class="mb-0">
                                    <li>Utilisez au moins 8 caractères</li>
                                    <li>Mélangez majuscules, minuscules, chiffres et symboles</li>
                                    <li>Évitez les mots courants ou personnels</li>
                                    <li>Ne réutilisez pas d'anciens mots de passe</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('auth.profile') }}" class="btn btn-outline-secondary">
                                    <i class="ti ti-x me-1"></i>
                                    Annuler
                                </a>
                                <button type="submit" class="btn btn-warning">
                                    <i class="ti ti-key me-1"></i>
                                    Changer le Mot de Passe
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const newPasswordInput = document.getElementById('new_password');
    const strengthBar = document.getElementById('password-strength-bar');
    const strengthText = document.getElementById('password-strength-text');

    newPasswordInput.addEventListener('input', function() {
        const password = this.value;
        const strength = calculatePasswordStrength(password);

        // Mettre à jour la barre de progression
        strengthBar.style.width = strength.score + '%';

        // Changer la couleur selon la force
        strengthBar.className = 'progress-bar';
        if (strength.score < 25) {
            strengthBar.classList.add('bg-danger');
            strengthText.textContent = 'Très faible';
            strengthText.className = 'text-danger';
        } else if (strength.score < 50) {
            strengthBar.classList.add('bg-warning');
            strengthText.textContent = 'Faible';
            strengthText.className = 'text-warning';
        } else if (strength.score < 75) {
            strengthBar.classList.add('bg-info');
            strengthText.textContent = 'Moyen';
            strengthText.className = 'text-info';
        } else {
            strengthBar.classList.add('bg-success');
            strengthText.textContent = 'Fort';
            strengthText.className = 'text-success';
        }
    });

    function calculatePasswordStrength(password) {
        let score = 0;

        // Longueur
        if (password.length >= 8) score += 20;
        if (password.length >= 12) score += 10;

        // Caractères variés
        if (/[a-z]/.test(password)) score += 10;
        if (/[A-Z]/.test(password)) score += 10;
        if (/[0-9]/.test(password)) score += 10;
        if (/[^A-Za-z0-9]/.test(password)) score += 20;

        // Motifs communs (pénalités)
        if (/(.)\1{2,}/.test(password)) score -= 10; // Répétitions
        if (/123|abc|qwe/i.test(password)) score -= 10; // Séquences

        return { score: Math.max(0, Math.min(100, score)) };
    }
});
</script>
@include('layouts.footer')
