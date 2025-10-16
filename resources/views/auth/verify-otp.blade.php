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
              src="../../assets/img/illustrations/auth-verify-illustration-light.png"
              alt="auth-verify-cover"
              class="my-5 auth-illustration"
              data-app-light-img="illustrations/auth-verify-illustration-light.png"
              data-app-dark-img="illustrations/auth-verify-illustration-dark.png" />

            <img
              src="../../assets/img/illustrations/bg-shape-image-light.png"
              alt="auth-verify-cover"
              class="platform-bg"
              data-app-light-img="illustrations/bg-shape-image-light.png"
              data-app-dark-img="illustrations/bg-shape-image-dark.png" />
          </div>
        </div>
        <!-- /Left Text -->

        <!-- Verify OTP -->
        <div class="d-flex col-12 col-lg-4 align-items-center authentication-bg p-sm-12 p-6">
          <div class="w-px-400 mx-auto mt-5 pt-5">
            <h4 class="mb-1">Vérification du code OTP 🔐</h4>
            <p class="mb-6">Entrez le code de vérification envoyé à votre email</p>

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

            <form id="formOTPVerification" class="mb-6" action="{{ route('auth.verify-otp.post') }}" method="POST">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">

                <div class="mb-6">
                    <label for="otp" class="form-label">Code de vérification</label>
                    <div class="input-group input-group-merge">
                        <input
                            type="text"
                            id="otp"
                            name="otp"
                            class="form-control @error('otp') is-invalid @enderror"
                            placeholder="000000"
                            maxlength="6"
                            autocomplete="one-time-code"
                            autofocus />
                        <span class="input-group-text">
                            <i class="ti ti-shield-check"></i>
                        </span>
                    </div>
                    @error('otp')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <div class="form-text">
                        Le code a été envoyé à : <strong>{{ $email }}</strong>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary d-grid w-100 mb-3">
                    Vérifier le code
                </button>
            </form>

            <div class="text-center">
                <p class="mb-2">Vous n'avez pas reçu le code ?</p>
                <form action="{{ route('auth.resend-otp') }}" method="POST" style="display: inline;">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email }}">
                    <button type="submit" class="btn btn-outline-secondary">
                        Renvoyer le code
                    </button>
                </form>
            </div>

            <div class="text-center mt-4">
                <a href="{{ route('auth.register') }}" class="text-decoration-none">
                    <i class="ti ti-arrow-left me-1"></i>
                    Retour à l'inscription
                </a>
            </div>
          </div>
        </div>
        <!-- /Verify OTP -->
      </div>
    </div>

    <script>
    // Auto-focus sur le champ OTP
    document.getElementById('otp').focus();

    // Validation côté client
    document.getElementById('formOTPVerification').addEventListener('submit', function(e) {
        const otp = document.getElementById('otp').value;

        // Vérifier que l'OTP contient 6 chiffres
        if (!/^\d{6}$/.test(otp)) {
            e.preventDefault();
            alert('Veuillez entrer un code de 6 chiffres.');
            return false;
        }
    });

    // Auto-formatage de l'OTP (seulement des chiffres)
    document.getElementById('otp').addEventListener('input', function(e) {
        // Supprimer tous les caractères non numériques
        this.value = this.value.replace(/\D/g, '');

        // Limiter à 6 caractères
        if (this.value.length > 6) {
            this.value = this.value.slice(0, 6);
        }
    });

    // Auto-submit quand 6 chiffres sont entrés
    document.getElementById('otp').addEventListener('input', function(e) {
        if (this.value.length === 6) {
            // Petit délai pour que l'utilisateur voie le code complet
            setTimeout(() => {
                document.getElementById('formOTPVerification').submit();
            }, 500);
        }
    });

    // Compteur de temps pour le renvoi
    let resendTimer = 60; // 60 secondes
    const resendButton = document.querySelector('button[type="submit"]');
    const originalText = resendButton.textContent;

    function updateResendTimer() {
        if (resendTimer > 0) {
            resendButton.textContent = `Renvoyer dans ${resendTimer}s`;
            resendButton.disabled = true;
            resendTimer--;
            setTimeout(updateResendTimer, 1000);
        } else {
            resendButton.textContent = originalText;
            resendButton.disabled = false;
        }
    }

    // Démarrer le timer au chargement de la page
    updateResendTimer();
    </script>

@include('auth.layouts.footer')
