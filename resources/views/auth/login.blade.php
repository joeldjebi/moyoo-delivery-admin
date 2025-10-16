@include('auth.layouts.header')
    <div class="container-xxl">
      <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner py-6">
          <!-- Login -->
          <div class="card">
            <div class="card-body">
              <!-- Logo -->
              <div class="app-brand justify-content-center mb-6">
                <a href="index.html" class="app-brand-link">
                  <span class="app-brand-logo demo">
                    <svg width="32" height="22" viewBox="0 0 32 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path
                        fill-rule="evenodd"
                        clip-rule="evenodd"
                        d="M0.00172773 0V6.85398C0.00172773 6.85398 -0.133178 9.01207 1.98092 10.8388L13.6912 21.9964L19.7809 21.9181L18.8042 9.88248L16.4951 7.17289L9.23799 0H0.00172773Z"
                        fill="#7367F0" />
                      <path
                        opacity="0.06"
                        fill-rule="evenodd"
                        clip-rule="evenodd"
                        d="M7.69824 16.4364L12.5199 3.23696L16.5541 7.25596L7.69824 16.4364Z"
                        fill="#161616" />
                      <path
                        opacity="0.06"
                        fill-rule="evenodd"
                        clip-rule="evenodd"
                        d="M8.07751 15.9175L13.9419 4.63989L16.5849 7.28475L8.07751 15.9175Z"
                        fill="#161616" />
                      <path
                        fill-rule="evenodd"
                        clip-rule="evenodd"
                        d="M7.77295 16.3566L23.6563 0H32V6.88383C32 6.88383 31.8262 9.17836 30.6591 10.4057L19.7824 22H13.6938L7.77295 16.3566Z"
                        fill="#7367F0" />
                    </svg>
                  </span>
                  <span class="app-brand-text demo text-heading fw-bold">Mooyo fleet</span>
                </a>
              </div>
              <!-- /Logo -->
              <h4 class="mb-1">Bienvenue sur Mooyo fleet! 👋</h4>
              <p class="mb-6">Veuillez vous connecter à votre compte et commencer à gérer vos livraisons</p>

              @if(session('success'))
                  <div class="alert alert-success alert-dismissible fade show" role="alert">
                      {{ session('success') }}
                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>
              @endif

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

              <form id="formAuthentication" class="mb-4" action="{{ route('auth.login.post') }}" method="POST">
                  @csrf
                <div class="mb-6">
                  <label for="email" class="form-label">Adresse email</label>
                  <input
                    type="email"
                    class="form-control @error('email') is-invalid @enderror"
                    id="email"
                    name="email"
                    placeholder="Entrez votre adresse email"
                    value="{{ old('email') }}"
                    autofocus />
                  @error('email')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
                </div>
                <div class="mb-6 form-password-toggle">
                  <label class="form-label" for="password">Mot de passe</label>
                  <div class="input-group input-group-merge">
                    <input
                      type="password"
                      id="password"
                      class="form-control @error('password') is-invalid @enderror"
                      name="password"
                      placeholder="********"
                      aria-describedby="password" />
                    <span class="input-group-text cursor-pointer" onclick="togglePassword()">
                      <i class="ti ti-eye-off" id="toggleIcon"></i>
                    </span>
                  </div>
                  @error('password')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
                </div>
                <div class="my-8">
                  <div class="d-flex justify-content-between">
                    <div class="form-check mb-0 ms-2">
                      <input class="form-check-input" type="checkbox" id="remember-me" name="remember" value="1" />
                      <label class="form-check-label" for="remember-me"> Se souvenir de moi </label>
                    </div>
                    <a href="{{ route('auth.password-forget') }}">
                      <p class="mb-0">Mot de passe oublié?</p>
                    </a>
                  </div>
                </div>
                <div class="mb-6">
                  <button class="btn btn-primary d-grid w-100" type="submit">Connexion</button>
                </div>
              </form>

              <p class="text-center">
                <span>Nouveau sur notre plateforme?</span>
                <a href="{{ route('auth.register') }}">
                  <span>Créer un compte</span>
                </a>
              </p>

            </div>
          </div>
          <!-- /Register -->
        </div>
      </div>
    </div>

    <script>
    // Fonction pour basculer la visibilité du mot de passe
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('ti-eye-off');
            toggleIcon.classList.add('ti-eye');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('ti-eye');
            toggleIcon.classList.add('ti-eye-off');
        }
    }

    // Validation côté client
    document.getElementById('formAuthentication').addEventListener('submit', function(e) {
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;

        // Validation de l'email
        if (!email) {
            e.preventDefault();
            alert('Veuillez entrer votre adresse email.');
            document.getElementById('email').focus();
            return false;
        }

        // Validation du format email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            e.preventDefault();
            alert('Veuillez entrer une adresse email valide.');
            document.getElementById('email').focus();
            return false;
        }

        // Validation du mot de passe
        if (!password) {
            e.preventDefault();
            alert('Veuillez entrer votre mot de passe.');
            document.getElementById('password').focus();
            return false;
        }

        if (password.length < 8) {
            e.preventDefault();
            alert('Le mot de passe doit contenir au moins 8 caractères.');
            document.getElementById('password').focus();
            return false;
        }
    });

    // Auto-focus sur le champ email si vide
    document.addEventListener('DOMContentLoaded', function() {
        const emailField = document.getElementById('email');
        if (!emailField.value) {
            emailField.focus();
        }
    });

    // Masquer les alertes après 5 secondes
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
    </script>

@include('auth.layouts.footer')
