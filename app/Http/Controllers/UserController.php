<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Entreprise;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['title'] = 'Gestion des Utilisateurs';
        $data['menu'] = 'users';

        $user = Auth::user();

        // Vérifier les permissions
        if (!$user->hasPermission('users.read')) {
            abort(403, 'Vous n\'avez pas les permissions pour voir les utilisateurs.');
        }

        // Super admin voit tous les utilisateurs
        if ($user->isSuperAdmin()) {
            $data['users'] = User::withTrashed()
                ->with('entreprise')
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        } else {
            // Les autres utilisateurs ne voient que ceux de leur entreprise
            $data['users'] = User::withTrashed()
                ->forEntreprise($user->entreprise_id)
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        }

        return view('users.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data['title'] = 'Créer un Utilisateur';
        $data['menu'] = 'users';

        $user = Auth::user();

        // Vérifier les permissions
        if (!$user->hasPermission('users.create')) {
            abort(403, 'Vous n\'avez pas les permissions pour créer des utilisateurs.');
        }

        // Super admin peut créer pour n'importe quelle entreprise
        if ($user->isSuperAdmin()) {
            $data['entreprises'] = Entreprise::active()->get();
        } else {
            // Les autres ne peuvent créer que pour leur entreprise
            $data['entreprises'] = collect([$user->entreprise]);
        }

        return view('users.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();

            $rules = [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email',
                'mobile' => 'required|string|max:20',
                'password' => 'required|string|min:8|confirmed',
                'role' => 'required|in:admin,user,manager',
                'user_type' => 'required|in:entreprise_admin,entreprise_user',
                'status' => 'required|in:active,inactive',
                'custom_permissions' => 'array',
                'custom_permissions.*' => 'string'
            ];

            // Super admin peut choisir l'entreprise
            if ($user->isSuperAdmin()) {
                $rules['entreprise_id'] = 'required|exists:entreprises,id';
            }

            $validated = $request->validate($rules, [
                'first_name.required' => 'Le prénom est obligatoire.',
                'last_name.required' => 'Le nom est obligatoire.',
                'mobile.required' => 'Le numéro de téléphone est obligatoire.',
                'email.required' => 'L\'adresse email est obligatoire.',
                'email.email' => 'L\'adresse email doit être valide.',
                'email.unique' => 'Cette adresse email est déjà utilisée.',
                'password.required' => 'Le mot de passe est obligatoire.',
                'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
                'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
                'role.required' => 'Le rôle est obligatoire.',
                'role.in' => 'Le rôle sélectionné n\'est pas valide.',
                'user_type.required' => 'Le type d\'utilisateur est obligatoire.',
                'user_type.in' => 'Le type d\'utilisateur sélectionné n\'est pas valide.',
                'status.required' => 'Le statut est obligatoire.',
                'status.in' => 'Le statut sélectionné n\'est pas valide.',
                'entreprise_id.required' => 'L\'entreprise est obligatoire.',
                'entreprise_id.exists' => 'L\'entreprise sélectionnée n\'existe pas.'
            ]);

            // Définir l'entreprise_id selon le type d'utilisateur
            if (!$user->isSuperAdmin()) {
                $validated['entreprise_id'] = $user->entreprise_id;
            }

            $validated['password'] = Hash::make($validated['password']);
            $validated['created_by'] = $user->id;

            // Gérer les permissions personnalisées
            $customPermissions = $request->input('custom_permissions', []);
            $validated['permissions'] = $customPermissions;

            // Créer l'utilisateur
            $newUser = User::create($validated);

            Log::info('Utilisateur créé par un admin', [
                'admin_id' => Auth::id(),
                'admin_email' => Auth::user()->email,
                'new_user_id' => $newUser->id,
                'new_user_email' => $newUser->email,
                'ip' => $request->ip()
            ]);

            return redirect()->route('users.index')
                ->with('success', 'L\'utilisateur a été créé avec succès.');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la création d\'un utilisateur: ' . $e->getMessage(), [
                'admin_id' => Auth::id(),
                'error' => $e->getMessage(),
                'ip' => $request->ip()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Erreur lors de la création de l\'utilisateur. Veuillez réessayer.'])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $data['title'] = 'Détails de l\'Utilisateur';
        $data['menu'] = 'users';

        // Charger la relation entreprise
        $data['user'] = $user->load('entreprise');

        return view('users.show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $data['title'] = 'Modifier l\'Utilisateur';
        $data['menu'] = 'users';
        $data['user'] = $user;

        return view('users.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        try {
            // Validation des données
            $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'mobile' => 'required|string|max:20',
                'email' => 'required|email|max:255|unique:users,email,' . $user->id,
                'role' => 'required|in:admin,user,manager',
                'status' => 'required|in:active,inactive',
                'custom_permissions' => 'array',
                'custom_permissions.*' => 'string'
            ], [
                'first_name.required' => 'Le prénom est obligatoire.',
                'last_name.required' => 'Le nom est obligatoire.',
                'mobile.required' => 'Le numéro de téléphone est obligatoire.',
                'email.required' => 'L\'adresse email est obligatoire.',
                'email.email' => 'L\'adresse email doit être valide.',
                'email.unique' => 'Cette adresse email est déjà utilisée.',
                'role.required' => 'Le rôle est obligatoire.',
                'role.in' => 'Le rôle sélectionné n\'est pas valide.',
                'status.required' => 'Le statut est obligatoire.',
                'status.in' => 'Le statut sélectionné n\'est pas valide.'
            ]);

            // Gérer les permissions personnalisées
            $customPermissions = $request->input('custom_permissions', []);

            // Mettre à jour l'utilisateur
            $user->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'mobile' => $request->mobile,
                'email' => $request->email,
                'role' => $request->role,
                'status' => $request->status,
                'permissions' => $customPermissions
            ]);

            Log::info('Utilisateur modifié par un admin', [
                'admin_id' => Auth::id(),
                'admin_email' => Auth::user()->email,
                'user_id' => $user->id,
                'user_email' => $user->email,
                'ip' => $request->ip()
            ]);

            return redirect()->route('users.index')
                ->with('success', 'L\'utilisateur a été modifié avec succès.');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la modification d\'un utilisateur: ' . $e->getMessage(), [
                'admin_id' => Auth::id(),
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'ip' => $request->ip()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Erreur lors de la modification de l\'utilisateur. Veuillez réessayer.'])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        try {
            // Empêcher l'auto-suppression
            if ($user->id === Auth::id()) {
                return redirect()->back()
                    ->withErrors(['error' => 'Vous ne pouvez pas supprimer votre propre compte.']);
            }

            $userEmail = $user->email;
            $user->delete();

            Log::info('Utilisateur supprimé par un admin', [
                'admin_id' => Auth::id(),
                'admin_email' => Auth::user()->email,
                'deleted_user_email' => $userEmail,
                'ip' => request()->ip()
            ]);

            return redirect()->route('users.index')
                ->with('success', 'L\'utilisateur a été supprimé avec succès.');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression d\'un utilisateur: ' . $e->getMessage(), [
                'admin_id' => Auth::id(),
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'ip' => request()->ip()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Erreur lors de la suppression de l\'utilisateur. Veuillez réessayer.']);
        }
    }

    /**
     * Restaurer un utilisateur supprimé
     */
    public function restore($id)
    {
        try {
            $user = User::withTrashed()->findOrFail($id);
            $user->restore();

            Log::info('Utilisateur restauré par un admin', [
                'admin_id' => Auth::id(),
                'admin_email' => Auth::user()->email,
                'restored_user_id' => $user->id,
                'restored_user_email' => $user->email,
                'ip' => request()->ip()
            ]);

            return redirect()->route('users.index')
                ->with('success', 'L\'utilisateur a été restauré avec succès.');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la restauration d\'un utilisateur: ' . $e->getMessage(), [
                'admin_id' => Auth::id(),
                'user_id' => $id,
                'error' => $e->getMessage(),
                'ip' => request()->ip()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Erreur lors de la restauration de l\'utilisateur. Veuillez réessayer.']);
        }
    }

    /**
     * Changer le mot de passe d'un utilisateur
     */
    public function changePassword(Request $request, User $user)
    {
        try {
            $request->validate([
                'new_password' => 'required|string|min:8|confirmed',
            ], [
                'new_password.required' => 'Le nouveau mot de passe est obligatoire.',
                'new_password.min' => 'Le nouveau mot de passe doit contenir au moins 8 caractères.',
                'new_password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            ]);

            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            Log::info('Mot de passe utilisateur modifié par un admin', [
                'admin_id' => Auth::id(),
                'admin_email' => Auth::user()->email,
                'user_id' => $user->id,
                'user_email' => $user->email,
                'ip' => $request->ip()
            ]);

            return redirect()->back()
                ->with('success', 'Le mot de passe a été modifié avec succès.');

        } catch (\Exception $e) {
            Log::error('Erreur lors du changement de mot de passe: ' . $e->getMessage(), [
                'admin_id' => Auth::id(),
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'ip' => $request->ip()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Erreur lors du changement de mot de passe. Veuillez réessayer.']);
        }
    }
}
