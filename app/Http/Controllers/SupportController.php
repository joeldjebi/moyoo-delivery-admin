<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class SupportController extends Controller
{
    public function index()
    {
        try {
            $data['menu'] = 'support';
            $data['title'] = 'Support & Tickets';

            $user = Auth::user();
            if (empty($user)) {
                return redirect()->route('auth.login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            // Récupérer l'ID de l'entreprise de l'utilisateur connecté
            $entrepriseId = $this->getEntrepriseId();

            // Récupérer les tickets de l'utilisateur
            $data['tickets'] = SupportTicket::where('entreprise_id', $entrepriseId)
                ->with(['user', 'assignedTo'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            // Statistiques
            $data['stats'] = [
                'total' => SupportTicket::where('entreprise_id', $entrepriseId)->count(),
                'open' => SupportTicket::where('entreprise_id', $entrepriseId)->open()->count(),
                'in_progress' => SupportTicket::where('entreprise_id', $entrepriseId)->inProgress()->count(),
                'resolved' => SupportTicket::where('entreprise_id', $entrepriseId)->resolved()->count(),
                'closed' => SupportTicket::where('entreprise_id', $entrepriseId)->closed()->count(),
            ];

            return view('support.index', $data);
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement de la page support: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du chargement de la page support.');
        }
    }

    public function create()
    {
        try {
            $data['menu'] = 'support';
            $data['title'] = 'Nouveau Ticket de Support';

            $user = Auth::user();
            if (empty($user)) {
                return redirect()->route('auth.login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            return view('support.create', $data);
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement du formulaire de création de ticket: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du chargement du formulaire.');
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'subject' => 'required|string|max:255',
                'description' => 'required|string|min:10',
                'priority' => 'required|in:low,medium,high,urgent',
                'category' => 'required|in:technical,billing,feature_request,bug_report,general',
                'contact_email' => 'nullable|email',
                'contact_phone' => 'nullable|string|max:20',
            ], [
                'subject.required' => 'Le sujet est obligatoire.',
                'subject.max' => 'Le sujet ne peut pas dépasser 255 caractères.',
                'description.required' => 'La description est obligatoire.',
                'description.min' => 'La description doit contenir au moins 10 caractères.',
                'priority.required' => 'La priorité est obligatoire.',
                'priority.in' => 'La priorité sélectionnée n\'est pas valide.',
                'category.required' => 'La catégorie est obligatoire.',
                'category.in' => 'La catégorie sélectionnée n\'est pas valide.',
                'contact_email.email' => 'L\'adresse email de contact n\'est pas valide.',
                'contact_phone.max' => 'Le numéro de téléphone ne peut pas dépasser 20 caractères.',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $user = Auth::user();
            $entrepriseId = $this->getEntrepriseId();

            // Créer le ticket
            $ticket = SupportTicket::create([
                'ticket_number' => SupportTicket::generateTicketNumber(),
                'user_id' => $user->id,
                'entreprise_id' => $entrepriseId,
                'subject' => $request->subject,
                'description' => $request->description,
                'priority' => $request->priority,
                'category' => $request->category,
                'contact_email' => $request->contact_email ?: $user->email,
                'contact_phone' => $request->contact_phone,
                'status' => 'open'
            ]);

            // Log de création
            Log::info('Nouveau ticket de support créé', [
                'ticket_id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'user_id' => $user->id,
                'entreprise_id' => $entrepriseId,
                'subject' => $request->subject,
                'priority' => $request->priority,
                'category' => $request->category
            ]);

            return redirect()->route('support.index')
                ->with('success', 'Votre ticket de support a été créé avec succès. Numéro de ticket: ' . $ticket->ticket_number);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la création du ticket de support: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Erreur lors de la création du ticket. Veuillez réessayer.')
                ->withInput();
        }
    }

    public function show($id)
    {
        try {
            $data['menu'] = 'support';
            $data['title'] = 'Détails du Ticket';

            $user = Auth::user();
            if (empty($user)) {
                return redirect()->route('auth.login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            $entrepriseId = $this->getEntrepriseId();

            $data['ticket'] = SupportTicket::where('id', $id)
                ->where('entreprise_id', $entrepriseId)
                ->with(['user', 'assignedTo'])
                ->firstOrFail();

            return view('support.show', $data);
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement des détails du ticket: ' . $e->getMessage());
            return redirect()->route('support.index')
                ->with('error', 'Ticket non trouvé ou accès non autorisé.');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'sometimes|in:open,in_progress,resolved,closed',
                'admin_notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator);
            }

            $user = Auth::user();
            $entrepriseId = $this->getEntrepriseId();

            $ticket = SupportTicket::where('id', $id)
                ->where('entreprise_id', $entrepriseId)
                ->firstOrFail();

            $updateData = [];
            
            if ($request->has('status')) {
                $updateData['status'] = $request->status;
                if ($request->status === 'resolved') {
                    $updateData['resolved_at'] = now();
                }
            }

            if ($request->has('admin_notes')) {
                $updateData['admin_notes'] = $request->admin_notes;
            }

            $ticket->update($updateData);

            Log::info('Ticket de support mis à jour', [
                'ticket_id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'updates' => $updateData
            ]);

            return redirect()->back()
                ->with('success', 'Ticket mis à jour avec succès.');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du ticket: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Erreur lors de la mise à jour du ticket.');
        }
    }

    private function getEntrepriseId()
    {
        $user = Auth::user();
        return $user->entreprise_id ?? 1; // Fallback à 1 si pas d'entreprise
    }
}