<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès Refusé - 403</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .error-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            text-align: center;
            max-width: 600px;
            width: 100%;
        }
        .error-icon {
            font-size: 5rem;
            color: #dc3545;
            margin-bottom: 1rem;
        }
        .error-title {
            color: #dc3545;
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        .error-message {
            color: #6c757d;
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }
        .btn-custom {
            margin: 0.5rem;
            padding: 0.75rem 1.5rem;
        }
        .user-info {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            margin-top: 2rem;
            text-align: left;
        }
        .user-info h6 {
            color: #495057;
            margin-bottom: 1rem;
        }
        .badge-custom {
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-card">
            <div class="error-icon">
                <i class="bi bi-shield-exclamation"></i>
            </div>

            <h1 class="error-title">403</h1>
            <h2 class="mb-3">Accès Refusé</h2>
            <p class="error-message">
                Désolé, vous n'avez pas les permissions nécessaires pour accéder à cette ressource.
            </p>

            <div class="alert alert-warning">
                <h6 class="alert-heading">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Information
                </h6>
                <p class="mb-0">
                    Si vous pensez que vous devriez avoir accès à cette ressource,
                    contactez votre administrateur système.
                </p>
            </div>

            <div class="mt-4">
                <a href="javascript:history.back()" class="btn btn-primary btn-custom">
                    <i class="bi bi-arrow-left me-1"></i>
                    Retour
                </a>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-primary btn-custom">
                    <i class="bi bi-house me-1"></i>
                    Tableau de Bord
                </a>
            </div>

            @auth
            <div class="user-info">
                <h6>
                    <i class="bi bi-person me-2"></i>
                    Informations de votre compte
                </h6>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Nom :</strong> {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</p>
                        <p><strong>Email :</strong> {{ auth()->user()->email }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Rôle :</strong>
                            <span class="badge bg-{{ auth()->user()->role === 'admin' ? 'primary' : (auth()->user()->role === 'manager' ? 'warning' : 'secondary') }} badge-custom">
                                {{ ucfirst(auth()->user()->role) }}
                            </span>
                        </p>
                        <p><strong>Type :</strong>
                            <span class="badge bg-info badge-custom">
                                {{ auth()->user()->user_type === 'entreprise_admin' ? 'Admin Entreprise' : 'Utilisateur Entreprise' }}
                            </span>
                        </p>
                    </div>
                </div>

                @if(auth()->user()->permissions && count(auth()->user()->permissions) > 0)
                <div class="mt-3">
                    <h6>Permissions personnalisées :</h6>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach(auth()->user()->permissions as $permission)
                            <span class="badge bg-success badge-custom">{{ $permission }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @else
            <div class="alert alert-warning mt-3">
                <h6 class="alert-heading">
                    <i class="bi bi-box-arrow-in-right me-2"></i>
                    Authentification requise
                </h6>
                <p class="mb-0">
                    Vous devez être connecté pour accéder à cette ressource.
                </p>
                <div class="mt-2">
                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-box-arrow-in-right me-1"></i>
                        Se connecter
                    </a>
                </div>
            </div>
            @endauth
        </div>
    </div>
</body>
</html>
