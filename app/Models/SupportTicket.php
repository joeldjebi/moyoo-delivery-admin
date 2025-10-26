<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'user_id',
        'entreprise_id',
        'subject',
        'description',
        'priority',
        'status',
        'category',
        'contact_email',
        'contact_phone',
        'resolved_at',
        'assigned_to',
        'admin_notes'
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Accessors
    public function getPriorityLabelAttribute()
    {
        $labels = [
            'low' => 'Faible',
            'medium' => 'Moyen',
            'high' => 'Élevé',
            'urgent' => 'Urgent'
        ];

        return $labels[$this->priority] ?? $this->priority;
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'open' => 'Ouvert',
            'in_progress' => 'En cours',
            'resolved' => 'Résolu',
            'closed' => 'Fermé'
        ];

        return $labels[$this->status] ?? $this->status;
    }

    public function getCategoryLabelAttribute()
    {
        $labels = [
            'technical' => 'Technique',
            'billing' => 'Facturation',
            'feature_request' => 'Demande de fonctionnalité',
            'bug_report' => 'Rapport de bug',
            'general' => 'Général'
        ];

        return $labels[$this->category] ?? $this->category;
    }

    public function getPriorityColorAttribute()
    {
        $colors = [
            'low' => 'success',
            'medium' => 'warning',
            'high' => 'danger',
            'urgent' => 'dark'
        ];

        return $colors[$this->priority] ?? 'secondary';
    }

    public function getStatusColorAttribute()
    {
        $colors = [
            'open' => 'primary',
            'in_progress' => 'warning',
            'resolved' => 'success',
            'closed' => 'secondary'
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    // Methods
    public static function generateTicketNumber()
    {
        $prefix = 'TKT';
        $date = now()->format('Ymd');
        $lastTicket = self::where('ticket_number', 'like', $prefix . $date . '%')
            ->orderBy('ticket_number', 'desc')
            ->first();

        if ($lastTicket) {
            $lastNumber = (int) substr($lastTicket->ticket_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public function isOpen()
    {
        return $this->status === 'open';
    }

    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }

    public function isResolved()
    {
        return $this->status === 'resolved';
    }

    public function isClosed()
    {
        return $this->status === 'closed';
    }

    public function markAsResolved()
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now()
        ]);
    }

    public function markAsClosed()
    {
        $this->update([
            'status' => 'closed'
        ]);
    }
}
