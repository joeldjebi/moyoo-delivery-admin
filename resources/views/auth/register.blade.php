@include('auth.layouts.header')
    <div class="authentication-wrapper authentication-cover">
      <!-- Logo -->
      <a href="index.html" class="app-brand auth-cover-brand">
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
      <!-- /Logo -->
      <div class="authentication-inner row m-0">
        <!-- /Left Text -->
        <div class="d-none d-lg-flex col-lg-8 p-0">
          <div class="auth-cover-bg auth-cover-bg-color d-flex justify-content-center align-items-center">
            <img
              src="../../assets/img/illustrations/auth-register-illustration-light.png"
              alt="auth-register-cover"
              class="my-5 auth-illustration"
              data-app-light-img="illustrations/auth-register-illustration-light.png"
              data-app-dark-img="illustrations/auth-register-illustration-dark.png" />

            <img
              src="../../assets/img/illustrations/bg-shape-image-light.png"
              alt="auth-register-cover"
              class="platform-bg"
              data-app-light-img="illustrations/bg-shape-image-light.png"
              data-app-dark-img="illustrations/bg-shape-image-dark.png" />
          </div>
        </div>
        <!-- /Left Text -->

        <!-- Register -->
        <div class="d-flex col-12 col-lg-4 align-items-center authentication-bg p-sm-12 p-6">
          <div class="w-px-400 mx-auto mt-5 pt-5">
            <h4 class="mb-1">L'aventure commence ici 🚀</h4>
            <p class="mb-6">Gérez facilement et amusement vos livraisons!</p>

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

            <form id="formAuthentication" class="mb-6" action="{{ route('auth.register.post') }}" method="POST">
                @csrf
              <div class="mb-6">
                <label for="first_name" class="form-label">Nom</label>
                <input
                  type="text"
                  class="form-control @error('first_name') is-invalid @enderror"
                  id="first_name"
                  name="first_name"
                  placeholder="Entrez votre nom"
                  value="{{ old('first_name') }}"
                  autofocus />
                @error('first_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="mb-6">
                <label for="last_name" class="form-label">Prénom</label>
                <input
                  type="text"
                  class="form-control @error('last_name') is-invalid @enderror"
                  id="last_name"
                  name="last_name"
                  placeholder="Entrez votre prénom"
                  value="{{ old('last_name') }}"
                />
                @error('last_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="mb-6">
                <label for="mobile" class="form-label">Numéro de téléphone</label>
                <input
                  type="text"
                  class="form-control @error('mobile') is-invalid @enderror"
                  id="mobile"
                  name="mobile"
                  placeholder="Entrez votre numéro de téléphone"
                  value="{{ old('mobile') }}"
                />
                @error('mobile')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="mb-6">
                <label for="email" class="form-label">Email</label>
                <input
                  type="email"
                  class="form-control @error('email') is-invalid @enderror"
                  id="email"
                  name="email"
                  placeholder="Entrez votre adresse email"
                  value="{{ old('email') }}" />
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
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
                  <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                </div>
                @error('password')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
              <div class="mb-6">
                <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                <div class="input-group input-group-merge">
                  <input
                    type="password"
                    id="password_confirmation"
                    class="form-control @error('password_confirmation') is-invalid @enderror"
                    name="password_confirmation"
                    placeholder="********"
                    aria-describedby="password" />
                  <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                </div>
                @error('password_confirmation')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
              <div class="mb-6 mt-8">
                <div class="form-check mb-8 ms-2">
                  <input class="form-check-input" type="checkbox" id="terms-conditions" name="terms" />
                  <label class="form-check-label" for="terms-conditions">
                    J'accepte les
                    <a href="javascript:void(0);">politique de confidentialité & conditions d'utilisation</a>
                  </label>
                </div>
              </div>
              <button class="btn btn-primary d-grid w-100">S'inscrire</button>
            </form>

            <p class="text-center">
              <span>Vous avez déjà un compte?</span>
              <a href="{{ route('auth.login') }}">
                <span>Se connecter</span>
              </a>
            </p>
          </div>
        </div>
        <!-- /Register -->
      </div>
    </div>

    <script>
    // Validation côté client
    document.getElementById('formAuthentication').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('password_confirmation').value;
        const terms = document.getElementById('terms-conditions').checked;

        // Vérifier la correspondance des mots de passe
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Les mots de passe ne correspondent pas.');
            return false;
        }

        // Vérifier les conditions d'utilisation
        if (!terms) {
            e.preventDefault();
            alert('Vous devez accepter les conditions d\'utilisation.');
            return false;
        }

        // Vérifier la force du mot de passe
        // const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/;
        // if (!passwordRegex.test(password)) {
        //     e.preventDefault();
        //     alert('Le mot de passe doit contenir au moins une minuscule, une majuscule, un chiffre et un caractère spécial.');
        //     return false;
        // }
    });

    // Fonction pour afficher/masquer le mot de passe
    document.querySelectorAll('.input-group-text.cursor-pointer').forEach(function(element) {
        element.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            const icon = this.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('ti-eye-off');
                icon.classList.add('ti-eye');
            } else {
                input.type = 'password';
                icon.classList.remove('ti-eye');
                icon.classList.add('ti-eye-off');
            }
        });
    });
    </script>

@include('auth.layouts.footer')
