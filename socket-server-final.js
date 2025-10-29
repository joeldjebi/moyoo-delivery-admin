import express from 'express';
import http from 'http';
import { Server as SocketIo } from 'socket.io';
import jwt from 'jsonwebtoken';

// Configuration
const PORT = process.env.SOCKET_PORT || 3000;
const JWT_SECRET = process.env.JWT_SECRET || 'll9f8PqzK3C7tVO0Ml9FdONKRnl15NnHtOrHsJpoVDQi6HbM2h8ZGIaXeXYvu0fU';

// Express app
const app = express();
const server = http.createServer(app);

// Socket.IO simple
const io = new SocketIo(server, {
    cors: {
        origin: "*",
        methods: ["GET", "POST"]
    },
    transports: ['websocket', 'polling'],
    pingTimeout: 60000,
    pingInterval: 25000
});

// Rate limiting storage simple
const rateLimits = new Map();

// Middleware d'authentification Socket.IO
io.use(async (socket, next) => {
    try {
        const token = socket.handshake.auth.token;
        const userType = socket.handshake.query.type || socket.handshake.auth.type;

        // Permettre les connexions admin sans token
        if (userType === 'admin') {
            socket.userType = 'admin';
            socket.userId = 'admin';
            socket.entrepriseId = 1;
            console.log('👨‍💼 Connexion admin autorisée sans token');
            return next();
        }

        if (!token) {
            return next(new Error('Token ou utilisateur manquant'));
        }

        // Vérifier le token JWT pour les livreurs
        const decoded = jwt.verify(token, JWT_SECRET);

        // Ajouter les informations utilisateur au socket
        socket.userId = decoded.sub;
        socket.userType = decoded.type;
        socket.entrepriseId = decoded.entreprise_id;

        next();
    } catch (error) {
        console.log('Erreur authentification:', error.message);
        next(new Error('Token invalide'));
    }
});

// Gestion des connexions
io.on('connection', (socket) => {
    console.log(`✅ Livreur connecté: ${socket.userId} (${socket.userType})`);

    // Rejoindre la room du livreur
    const livreurRoom = `livreur:${socket.userId}`;
    socket.join(livreurRoom);
    console.log(`📡 Livreur ${socket.userId} a rejoint la room: ${livreurRoom}`);

    // Rejoindre la room admin
    socket.join('admin:location');
    socket.join('admin:monitor');

    // Rejoindre la room dispatcher
    socket.join('dispatcher:tracking');

    // Rejoindre la room des livreurs actifs
    socket.join('livreur:active');

    // Événement: Livreur rejoint
    socket.on('livreur:join', (data) => {
        console.log('👤 Livreur rejoint:', data);

        // Rejoindre la room spécifiée
        if (data.room) {
            socket.join(data.room);
            console.log(`📡 Livreur ${data.livreur_id} a rejoint: ${data.room}`);
        }

        // Notifier les admins
        socket.to('admin:location').emit('livreur:tracking:start', {
            livreur_id: data.livreur_id,
            socket_id: socket.id,
            timestamp: new Date().toISOString()
        });
    });

    // Événement: Mise à jour de position
    socket.on('location:update', (data) => {
        console.log('📍 Position mise à jour:', data);

        // Valider les données
        if (!data.livreur_id || !data.latitude || !data.longitude) {
            socket.emit('error', { message: 'Données de position invalides' });
            return;
        }

        // Rate limiting simple
        const key = `location:${socket.userId}`;
        const now = Date.now();
        const lastUpdate = rateLimits.get(key) || 0;

        if (now - lastUpdate < 1000) { // 1 seconde minimum entre les mises à jour
            socket.emit('error', { message: 'Trop de mises à jour de position' });
            return;
        }

        rateLimits.set(key, now);

        // Notifier le livreur
        socket.emit('location:updated', {
            ...data,
            timestamp: new Date().toISOString(),
            socket_id: socket.id
        });

        // Notifier les admins
        socket.to('admin:location').emit('admin:livreur:location', {
            ...data,
            timestamp: new Date().toISOString(),
            socket_id: socket.id
        });

        // Notifier les admins monitor
        socket.to('admin:monitor').emit('admin:livreur:location', {
            ...data,
            timestamp: new Date().toISOString(),
            socket_id: socket.id
        });

        // Notifier tous les admins connectés
        socket.broadcast.emit('livreur:location:update', {
            ...data,
            timestamp: new Date().toISOString(),
            socket_id: socket.id
        });

        // Notifier les dispatchers
        socket.to('dispatcher:tracking').emit('dispatcher:livreur:status', {
            livreur_id: data.livreur_id,
            status: data.status || 'active',
            timestamp: new Date().toISOString(),
            socket_id: socket.id
        });
    });

    // Événement: Changement de statut
    socket.on('location:status:change', (data) => {
        console.log('🔄 Statut changé:', data);

        // Valider les données
        if (!data.livreur_id || !data.status) {
            socket.emit('error', { message: 'Données de statut invalides' });
            return;
        }

        // Notifier le livreur
        socket.emit('location:status:changed', {
            ...data,
            timestamp: new Date().toISOString(),
            socket_id: socket.id
        });

        // Notifier les admins avec les coordonnées si disponibles
        socket.to('admin:location').emit('admin:livreur:location', {
            livreur_id: data.livreur_id,
            status: data.status,
            latitude: data.latitude || null,
            longitude: data.longitude || null,
            timestamp: new Date().toISOString(),
            socket_id: socket.id
        });

        // Notifier les dispatchers
        socket.to('dispatcher:tracking').emit('dispatcher:livreur:status', {
            livreur_id: data.livreur_id,
            status: data.status,
            timestamp: new Date().toISOString(),
            socket_id: socket.id
        });
    });

    // Événement: Livreur quitte
    socket.on('livreur:leave', (data) => {
        console.log('👋 Livreur quitte:', data);

        // Notifier les admins
        socket.to('admin:location').emit('livreur:tracking:stop', {
            livreur_id: data.livreur_id,
            socket_id: socket.id,
            timestamp: new Date().toISOString()
        });
    });

    // Événement: Admin rejoint
    socket.on('admin:join', (data) => {
        console.log('👨‍💼 Admin rejoint:', data);

        // Rejoindre les rooms admin
        socket.join('admin:location');
        socket.join('admin:monitor');

        console.log('📡 Admin a rejoint les rooms: admin:location, admin:monitor');

        // Notifier que l'admin est connecté
        socket.emit('admin:connected', {
            type: 'admin',
            socket_id: socket.id,
            timestamp: new Date().toISOString()
        });
    });

    // Gestion de la déconnexion
    socket.on('disconnect', (reason) => {
        console.log(`❌ Livreur déconnecté: ${socket.userId} - ${reason}`);

        // Notifier les admins
        socket.to('admin:location').emit('livreur:tracking:stop', {
            livreur_id: socket.userId,
            socket_id: socket.id,
            timestamp: new Date().toISOString(),
            reason: reason
        });
    });

    // Gestion des erreurs
    socket.on('error', (error) => {
        console.log('❌ Erreur Socket.IO:', error);
    });
});

// Route de test
app.get('/', (req, res) => {
    res.json({
        message: 'Socket.IO Server MOYOO',
        status: 'active',
        port: PORT,
        timestamp: new Date().toISOString()
    });
});

// Route de santé
app.get('/health', (req, res) => {
    res.json({
        status: 'healthy',
        uptime: process.uptime(),
        timestamp: new Date().toISOString()
    });
});

// Démarrer le serveur
server.listen(PORT, '192.168.1.29', () => {
    console.log(`🚀 Serveur Socket.IO MOYOO démarré sur le port ${PORT}`);
    console.log(`📡 URL: http://192.168.1.29:${PORT}`);
    console.log(`🔧 Transports: websocket, polling`);
    console.log(`🔒 Authentification: JWT`);
    console.log(`⏰ Timestamp: ${new Date().toISOString()}`);
});

// Gestion des erreurs
process.on('uncaughtException', (error) => {
    console.error('❌ Erreur non gérée:', error);
});

process.on('unhandledRejection', (reason, promise) => {
    console.error('❌ Promesse rejetée:', reason);
});
