<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Afficher les paramètres de notifications
     */
    public function settings()
    {
        $data['title'] = 'Paramètres des Notifications';
        $data['menu'] = 'notifications';

        return view('notifications.settings', $data);
    }
}
