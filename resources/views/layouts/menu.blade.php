        <!-- Menu -->

        <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
            <div class="app-brand demo">
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
                <span class="app-brand-text demo menu-text fw-bold">MOYOO fleet</span>
              </a>

              <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
                <i class="ti menu-toggle-icon d-none d-xl-block align-middle"></i>
                <i class="ti ti-x d-block d-xl-none ti-md align-middle"></i>
              </a>
            </div>

            <div class="menu-inner-shadow"></div>

            <ul class="menu-inner py-1">
              <!-- Tableau de bord -->
              <li class="menu-item {{ $menu == 'dashboard' ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}" class="menu-link">
                  <i class="menu-icon tf-icons ti ti-smart-home"></i>
                  <div data-i18n="Tableau de bord">Tableau de bord</div>
                </a>
              </li>
              @if(auth()->user()->hasPermission('colis.read'))
              <li class="menu-item">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                  <i class="menu-icon tf-icons ti ti-shopping-cart"></i>
                  <div data-i18n="Colis">Colis</div>
                </a>
                <ul class="menu-sub">
                  <li class="menu-item">
                    <a href="{{ route('colis.index') }}" class="menu-link">
                      <div data-i18n="Liste des colis">Liste des colis</div>
                    </a>
                  </li>
                  @if(auth()->user()->hasPermission('colis.create'))
                  <li class="menu-item">
                    <a href="{{ route('colis.create') }}" class="menu-link">
                      <div data-i18n="Ajouter un colis">Ajouter un colis</div>
                    </a>
                  </li>
                  @endif
                  @if(auth()->user()->hasPermission('colis.update'))
                  <li class="menu-item">
                    <a href="{{ route('colis.packages') }}" class="menu-link">
                      <div data-i18n="Liste des packages de colis">Liste des packages de colis</div>
                    </a>
                  </li>
                  @endif
                  <li class="menu-item">
                    <a href="{{ route('ramassages.index') }}" class="menu-link">
                      <div data-i18n="Ramassages">Ramassages</div>
                    </a>
                  </li>
                </ul>
              </li>
              @endif

              <!-- Marchants & Boutiques -->
              @if(auth()->user()->hasPermission('marchands.read'))
              <li class="menu-item">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                  <i class="menu-icon tf-icons ti ti-layout-sidebar"></i>
                  <div data-i18n="Marchants & Boutiques">Marchants & Boutiques</div>
                </a>

                <ul class="menu-sub">
                  <li class="menu-item {{ $menu == 'marchands' ? 'active' : '' }}">
                    <a href="{{ route('marchands.index') }}" class="menu-link">
                      <div data-i18n="Marchands">Marchands</div>
                    </a>
                  </li>
                  <li class="menu-item {{ $menu == 'boutiques' ? 'active' : '' }}">
                    <a href="{{ route('boutiques.index') }}" class="menu-link">
                      <div data-i18n="Boutiques">Boutiques</div>
                    </a>
                  </li>

                </ul>
              </li>
              @endif
              <!-- Rapports menu start -->
              @if(auth()->user()->hasPermission('reports.read'))
              <li class="menu-item">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                  <i class="menu-icon tf-icons ti ti-chart-bar"></i>
                  <div data-i18n="Rapports">Rapports</div>
                </a>
                <ul class="menu-sub">
                  <li class="menu-item {{ $menu == 'rapports' ? 'active' : '' }}">
                    <a href="{{ route('rapports.index') }}" class="menu-link">
                      <div data-i18n="Tableau de bord">Tableau de bord</div>
                    </a>
                  </li>
                  <li class="menu-item">
                    <a href="{{ route('rapports.show', 'livraisons') }}" class="menu-link">
                      <div data-i18n="Rapport Livraisons">Rapport Livraisons</div>
                    </a>
                  </li>
                  <li class="menu-item">
                    <a href="{{ route('rapports.show', 'colis') }}" class="menu-link">
                      <div data-i18n="Rapport Colis">Rapport Colis</div>
                    </a>
                  </li>
                  <li class="menu-item">
                    <a href="{{ route('rapports.show', 'ramassages') }}" class="menu-link">
                      <div data-i18n="Rapport Ramassages">Rapport Ramassages</div>
                    </a>
                  </li>
                  <li class="menu-item">
                    <a href="{{ route('rapports.show', 'frais') }}" class="menu-link">
                      <div data-i18n="Rapport Frais">Rapport Frais</div>
                    </a>
                  </li>
                </ul>
              </li>
              @endif
              <!-- Rapports menu end -->

              <!-- Reversements menu start -->
              @if(auth()->user()->hasPermission('reversements.read'))
              <li class="menu-item">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                  <i class="menu-icon tf-icons ti ti-wallet"></i>
                  <div data-i18n="Reversements">Reversements</div>
                </a>
                <ul class="menu-sub">
                  <li class="menu-item {{ $menu == 'reversements' ? 'active' : '' }}">
                    <a href="{{ route('reversements.index') }}" class="menu-link">
                      <div data-i18n="Liste des Reversements">Liste des Reversements</div>
                    </a>
                  </li>
                  @if(auth()->user()->hasPermission('reversements.create'))
                  <li class="menu-item">
                    <a href="{{ route('reversements.create') }}" class="menu-link">
                      <div data-i18n="Nouveau Reversement">Nouveau Reversement</div>
                    </a>
                  </li>
                  @endif
                  <li class="menu-item {{ $menu == 'balances' ? 'active' : '' }}">
                    <a href="{{ route('balances.index') }}" class="menu-link">
                      <div data-i18n="Balances des Marchands">Balances des Marchands</div>
                    </a>
                  </li>
                  <li class="menu-item {{ $menu == 'historique_balances' ? 'active' : '' }}">
                    <a href="{{ route('historique.balances') }}" class="menu-link">
                      <div data-i18n="Historique des Balances">Historique des Balances</div>
                    </a>
                  </li>
                </ul>
              </li>
              @endif
              <!-- Reversements menu end -->

              <!-- Apps & Pages -->
              <li class="menu-header small">
                <span class="menu-header-text" data-i18n="Apps & Pages">Apps &amp; Pages</span>
              </li>
              <li class="menu-item {{ $menu == 'livreurs' ? 'active' : '' }}">
                <a href="{{ route('livreurs.index') }}" class="menu-link">
                  <i class="menu-icon tf-icons ti ti-mail"></i>
                  <div data-i18n="Livreurs">Livreurs</div>
                </a>
              </li>
              <li class="menu-item {{ $menu == 'tarifs' ? 'active' : '' }}">
                <a href="{{ route('tarifs.index') }}" class="menu-link">
                  <i class="menu-icon tf-icons ti ti-currency-dollar"></i>
                  <div data-i18n="Tarifs de Livraison">Tarifs de Livraison</div>
                </a>
              </li>
              <li class="menu-item {{ $menu == 'type_engins' ? 'active' : '' }}">
                <a href="{{ route('type-engins.index') }}" class="menu-link {{ $menu == 'type_engins' ? 'active' : '' }}">
                  <i class="menu-icon tf-icons ti ti-mail"></i>
                  <div data-i18n="Type engin">Type engin</div>
                </a>
              </li>
              <li class="menu-item {{ $menu == 'engins' ? 'active' : '' }}">
                <a href="{{ route('engins.index') }}" class="menu-link">
                  <i class="menu-icon tf-icons ti ti-messages"></i>
                  <div data-i18n="Engins">Engins</div>
                </a>
              </li>
              <li class="menu-item {{ $menu == 'delais' ? 'active' : '' }}">
                <a href="{{ route('delais.index') }}" class="menu-link">
                  <i class="menu-icon tf-icons ti ti-calendar"></i>
                  <div data-i18n="Delais">Delais</div>
                </a>
              </li>
              @if(auth()->user()->hasPermission('users.read'))
              <li class="menu-item {{ $menu == 'users' ? 'active' : '' }}">
                <a href="{{ route('users.index') }}" class="menu-link">
                  <i class="menu-icon tf-icons ti ti-users"></i>
                  <div data-i18n="Utilisateurs">Gestion des Utilisateurs</div>
                </a>
              </li>
              @endif
              @if(auth()->user()->hasPermission('settings.update'))
              <li class="menu-item {{ $menu == 'role-permissions' ? 'active' : '' }}">
                <a href="{{ route('role-permissions.index') }}" class="menu-link">
                  <i class="menu-icon tf-icons ti ti-shield"></i>
                  <div data-i18n="Permissions">Permissions des Rôles</div>
                </a>
              </li>
              @endif
              <li class="menu-item {{ $menu == 'mode-livraisons' ? 'active' : '' }}">
                <a href="{{ route('mode-livraisons.index') }}" class="menu-link">
                  <i class="menu-icon tf-icons ti ti-layout-kanban"></i>
                  <div data-i18n="Mode de livraison">Mode de livraison</div>
                </a>
              </li>
              <li class="menu-item {{ $menu == 'poids' ? 'active' : '' }}">
                <a href="{{ route('poids.index') }}" class="menu-link">
                  <i class="menu-icon tf-icons ti ti-layout-kanban"></i>
                  <div data-i18n="Poids du colis">Poids du colis</div>
                </a>
              </li>
              <li class="menu-item {{ $menu == 'type_colis' ? 'active' : '' }}">
                <a href="{{ route('type-colis.index') }}" class="menu-link">
                  <i class="menu-icon tf-icons ti ti-layout-kanban"></i>
                  <div data-i18n="Type de colis">Type de colis</div>
                </a>
              </li>
              <li class="menu-header small">
                <span class="menu-header-text" data-i18n="Parametres">Parametres</span>
              </li>
              <!-- e-commerce-app menu start -->
              <li class="menu-item">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                  <i class="menu-icon tf-icons ti ti-shopping-cart"></i>
                  <div data-i18n="Parametres">Parametres</div>
                </a>
                <ul class="menu-sub">
                  <li class="menu-item {{ $menu == 'profile' ? 'active' : '' }}">
                    <a href="{{ route('auth.profile') }}" class="menu-link">
                      <div data-i18n="Profil">Profil</div>
                    </a>
                  </li>
                  <li class="menu-item {{ $menu == 'entreprise' ? 'active' : '' }}">
                    <a href="{{ route('entreprise.index') }}" class="menu-link">
                      <div data-i18n="Entreprise">Entreprise</div>
                    </a>
                  </li>
                </ul>
              </li>


              <!-- Misc -->
              <li class="menu-header small">
                <span class="menu-header-text" data-i18n="Support & Documentation">Support & Documentation</span>
              </li>
              <li class="menu-item">
                <a href="https://pixinvent.ticksy.com/" target="_blank" class="menu-link">
                  <i class="menu-icon tf-icons ti ti-lifebuoy"></i>
                  <div data-i18n="Support">Support</div>
                </a>
              </li>
              <li class="menu-item">
                <a
                  href="https://demos.pixinvent.com/vuexy-html-admin-template/documentation/"
                  target="_blank"
                  class="menu-link">
                  <i class="menu-icon tf-icons ti ti-file-description"></i>
                  <div data-i18n="Documentation">Documentation</div>
                </a>
              </li>
            </ul>
          </aside>
          <!-- / Menu -->
                  <!-- Layout container -->
        <div class="layout-page">
            <!-- Navbar -->

            @include('layouts.nav-bar')

            <!-- / Navbar -->
                      <!-- Content wrapper -->
          <div class="content-wrapper">
            <!-- Content -->
            <div class="container-xxl flex-grow-1 container-p-y">
