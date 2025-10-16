@include('layouts.header')

@include('layouts.menu', ['menu' => 'error'])

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="misc-wrapper">
        <h2 class="mb-2 mx-2">Page Non Trouvée</h2>
        <p class="mb-4 mx-2">Désolé, la page que vous recherchez n'existe pas ou a été déplacée.</p>

        <div class="mt-3">
            <div class="error-icon">
                <i class="ti ti-file-x" style="font-size: 8rem; color: #6c757d;"></i>
            </div>
        </div>

        <div class="mt-4">
            <a href="{{ url()->previous() }}" class="btn btn-primary">
                <i class="ti ti-arrow-left me-1"></i>
                Retour
            </a>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-primary ms-2">
                <i class="ti ti-home me-1"></i>
                Tableau de Bord
            </a>
        </div>

        <div class="mt-4">
            <div class="alert alert-info">
                <h6 class="alert-heading">
                    <i class="ti ti-info-circle me-2"></i>
                    Suggestions
                </h6>
                <ul class="mb-0">
                    <li>Vérifiez l'URL dans la barre d'adresse</li>
                    <li>Utilisez le menu de navigation pour accéder aux sections disponibles</li>
                    <li>Contactez l'administrateur si le problème persiste</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
.misc-wrapper {
    text-align: center;
    padding: 2rem 0;
}

.misc-wrapper h2 {
    color: #697a8d;
    font-size: 2.5rem;
    font-weight: 600;
}

.misc-wrapper p {
    color: #697a8d;
    font-size: 1.1rem;
}

@media (max-width: 768px) {
    .misc-wrapper h2 {
        font-size: 2rem;
    }

    .error-icon i {
        font-size: 6rem !important;
    }
}
</style>

@include('layouts.footer')
