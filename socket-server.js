import express from 'express';
import http from 'http';
import { Server as SocketIo } from 'socket.io';
import jwt from 'jsonwebtoken';
import mysql from 'mysql2/promise';
import Redis from 'redis';

// Configuration
const PORT = process.env.SOCKET_PORT || 3000;
const JWT_SECRET = process.env.JWT_SECRET || 'your-secret-key';
const REDIS_URL = process.env.REDIS_URL || 'redis://localhost:6379';

// Base de données MySQL
const dbConfig = {
    host: process.env.DB_HOST || '127.0.0.1',
    user: process.env.DB_USERNAME || 'root',
    password: process.env.DB_PASSWORD || '',
    database: process.env.DB_DATABASE || 'admin_delivery',
    charset: 'utf8mb4'
};

// Redis pour le rate limiting et l'adaptateur
const redisClient = Redis.createClient({ url: REDIS_URL });
const redisSubscriber = redisClient.duplicate();

// Express app
const app = express();
const server = http.createServer(app);

// Socket.IO avec Redis adapter
const io = new SocketIo(server, {
    cors: {
        origin: process.env.ALLOWED_ORIGINS?.split(',') || "*",
        methods: ["GET", "POST"]
    },
    transports: ['websocket', 'polling'],
    pingTimeout: 60000,
    pingInterval: 25000
});

// Rate limiting storage
const rateLimits = new Map();

// Middleware d'authentification Socket.IO
io.use(async (socket, next) => {
    try {
        const token = socket.handshake.auth.token;
        const userId = socket.handshake.auth.userId;
        const userName = socket.handshake.auth.userName;
        const userRole = socket.handshake.auth.userRole;
        const type = socket.handshake.auth.type;

        // Accepter les connexions admin sans authentification stricte
        if (type === 'admin') {
            socket.livreurId = userId || 'admin';
            socket.livreurName = userName || 'Admin';
            socket.userRole = userRole || 'admin';
            console.log('👨‍💼 Connexion admin acceptée');
            return next();
        }

        // Pour les livreurs, vérifier les données
        if (!token || !userId) {
            return next(new Error('Token ou utilisateur manquant'));
        }

        // Pour l'instant, on accepte le token CSRF et les données utilisateur
        // Dans un environnement de production, vous devriez valider le token CSRF
        socket.livreurId = userId;
        socket.livreurName = userName || 'Livreur';
        socket.userRole = userRole || 'livreur';

        next();
    } catch (err) {
        next(new Error('Authentification invalide: ' + err.message));
    }
});

// Connexion à la base de données
let db;
async function initDatabase() {
    try {
        db = await mysql.createConnection(dbConfig);
        console.log('✅ Connexion MySQL établie');
    } catch (error) {
        console.error('❌ Erreur connexion MySQL:', error);
        process.exit(1);
    }
}

// Rate limiting
async function isRateLimited(key, seconds) {
    const now = Date.now();
    const lastRequest = rateLimits.get(key);

    if (lastRequest && (now - lastRequest) < (seconds * 1000)) {
        return true;
    }

    rateLimits.set(key, now);
    return false;
}

// Validation des données de géolocalisation
function validateLocationData(data) {
    const { latitude, longitude, accuracy, speed, heading } = data;

    if (latitude < -90 || latitude > 90) {
        throw new Error('Latitude invalide');
    }
    if (longitude < -180 || longitude > 180) {
        throw new Error('Longitude invalide');
    }
    if (accuracy && (accuracy < 0 || accuracy > 1000)) {
        throw new Error('Précision invalide');
    }
    if (speed && speed < 0) {
        throw new Error('Vitesse invalide');
    }
    if (heading && (heading < 0 || heading > 360)) {
        throw new Error('Direction invalide');
    }
}

// Sauvegarder position en base
async function saveLocation(locationData) {
    try {
        const [result] = await db.execute(`
            INSERT INTO livreur_locations
            (livreur_id, latitude, longitude, accuracy, altitude, speed, heading, timestamp, status, ramassage_id, historique_livraison_id, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        `, [
            locationData.livreur_id,
            locationData.latitude,
            locationData.longitude,
            locationData.accuracy,
            locationData.altitude,
            locationData.speed,
            locationData.heading,
            locationData.timestamp,
            locationData.status || 'en_cours',
            locationData.ramassage_id,
            locationData.historique_livraison_id
        ]);

        return { id: result.insertId, ...locationData };
    } catch (error) {
        console.error('Erreur sauvegarde position:', error);
        throw error;
    }
}

// Vérifier si un livreur a une mission active
async function hasActiveMission(livreurId) {
    try {
        // Vérifier les livraisons en cours
        const [activeDeliveries] = await db.execute(`
            SELECT COUNT(*) as count FROM colis
            WHERE livreur_id = ? AND status = 1
        `, [livreurId]);

        // Vérifier les ramassages en cours
        const [activePickups] = await db.execute(`
            SELECT COUNT(*) as count FROM ramassages
            WHERE livreur_id = ? AND statut = 'en_cours'
        `, [livreurId]);

        const hasDelivery = activeDeliveries[0].count > 0;
        const hasPickup = activePickups[0].count > 0;

        console.log(`🔍 Vérification mission livreur ${livreurId}:`, {
            livraisons: activeDeliveries[0].count,
            ramassages: activePickups[0].count,
            hasActiveMission: hasDelivery || hasPickup
        });

        return hasDelivery || hasPickup;
    } catch (error) {
        console.error('Erreur vérification mission:', error);
        return false;
    }
}

// Mettre à jour statut livreur
async function updateLivreurStatus(livreurId, status, socketId = null) {
    try {
        await db.execute(`
            INSERT INTO livreur_location_status (livreur_id, status, socket_id, last_updated, created_at, updated_at)
            VALUES (?, ?, ?, NOW(), NOW(), NOW())
            ON DUPLICATE KEY UPDATE
            status = VALUES(status),
            socket_id = VALUES(socket_id),
            last_updated = VALUES(last_updated),
            updated_at = NOW()
        `, [livreurId, status, socketId]);
    } catch (error) {
        console.error('Erreur mise à jour statut:', error);
        throw error;
    }
}

// Calculer distance entre deux points
function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371000; // Rayon de la Terre en mètres
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;

    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
        Math.sin(dLon/2) * Math.sin(dLon/2);

    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}

// Gestion des connexions Socket.IO
io.on('connection', async (socket) => {
    console.log(`🔗 Livreur connecté: ${socket.livreurId} (${socket.livreurName})`);

    try {
        // Rejoindre les rooms appropriées
        socket.join(`livreur:${socket.livreurId}`);
        socket.join('livreur:active');

        // Mettre à jour le statut
        await updateLivreurStatus(socket.livreurId, 'active', socket.id);

        // Notifier les admins
        socket.to('admin:location').emit('livreur:online', {
            livreur_id: socket.livreurId,
            livreur_name: socket.livreurName,
            socket_id: socket.id,
            timestamp: new Date().toISOString()
        });

        // Notifier les dispatchers
        socket.to('dispatcher:tracking').emit('livreur:status:changed', {
            livreur_id: socket.livreurId,
            livreur_name: socket.livreurName,
            status: 'active',
            timestamp: new Date().toISOString()
        });

        // Événement: Mise à jour de position
        socket.on('location:update', async (data) => {
            try {
                console.log(`📍 Position reçue de ${socket.livreurId}:`, data);

                const { latitude, longitude, accuracy, speed, heading, timestamp, status, ramassage_id, historique_livraison_id } = data;

                // Validation des données
                if (!latitude || !longitude) {
                    socket.emit('location:error', { message: 'Coordonnées manquantes' });
                    return;
                }

                // VÉRIFICATION CRUCIALE : Le livreur doit avoir une mission active
                const hasMission = await hasActiveMission(socket.livreurId);
                if (!hasMission) {
                    console.log(`⚠️ Position rejetée - Livreur ${socket.livreurId} n'a pas de mission active`);
                    socket.emit('location:error', {
                        message: 'Suivi GPS désactivé. Démarrez une livraison ou un ramassage pour activer le suivi.'
                    });
                    return;
                }

                validateLocationData(data);

                // Rate limiting - max 1 position par 2 secondes
                const rateLimitKey = `location:${socket.livreurId}`;
                if (await isRateLimited(rateLimitKey, 2)) {
                    socket.emit('location:error', { message: 'Trop de requêtes. Attendez 2 secondes.' });
                    return;
                }

                // Sauvegarder en base
                const location = await saveLocation({
                    livreur_id: socket.livreurId,
                    latitude, longitude, accuracy, speed, heading,
                    timestamp: new Date(timestamp || Date.now()),
                    status: status || 'en_cours',
                    ramassage_id,
                    historique_livraison_id
                });

                // Confirmer au livreur
                socket.emit('location:updated', {
                    success: true,
                    location_id: location.id,
                    server_timestamp: new Date().toISOString()
                });

                // Diffuser aux admins
                socket.to('admin:location').emit('admin:livreur:location', {
                    livreur_id: socket.livreurId,
                    livreur_name: socket.livreurName,
                    latitude, longitude, accuracy, speed, heading,
                    timestamp: new Date(timestamp || Date.now()).toISOString(),
                    status: status || 'en_cours',
                    ramassage_id,
                    historique_livraison_id
                });

                // Diffuser aux dispatchers
                socket.to('dispatcher:tracking').emit('dispatcher:livreur:status', {
                    livreur_id: socket.livreurId,
                    livreur_name: socket.livreurName,
                    latitude, longitude, accuracy, speed, heading,
                    timestamp: new Date(timestamp || Date.now()).toISOString(),
                    status: status || 'en_cours'
                });

            } catch (error) {
                console.error('Erreur location:update:', error);
                socket.emit('location:error', { message: error.message || 'Erreur serveur' });
            }
        });

        // Événement: Changement de statut
        socket.on('location:status:change', async (data) => {
            try {
                const { status } = data;

                if (!['active', 'inactive', 'paused'].includes(status)) {
                    socket.emit('location:error', { message: 'Statut invalide' });
                    return;
                }

                await updateLivreurStatus(socket.livreurId, status, socket.id);

                socket.emit('location:status:changed', {
                    status,
                    timestamp: new Date().toISOString()
                });

                // Notifier les admins
                socket.to('admin:location').emit('livreur:status:changed', {
                    livreur_id: socket.livreurId,
                    livreur_name: socket.livreurName,
                    status,
                    timestamp: new Date().toISOString()
                });

                // Notifier les dispatchers
                socket.to('dispatcher:tracking').emit('dispatcher:livreur:status', {
                    livreur_id: socket.livreurId,
                    livreur_name: socket.livreurName,
                    status,
                    timestamp: new Date().toISOString()
                });

            } catch (error) {
                console.error('Erreur location:status:change:', error);
                socket.emit('location:error', { message: 'Erreur changement statut' });
            }
        });

        // Événement: Livreur se connecte explicitement
        socket.on('livreur:join', () => {
            console.log(`👋 Livreur ${socket.livreurId} rejoint le suivi`);
            socket.emit('livreur:tracking:start', {
                message: 'Suivi démarré',
                timestamp: new Date().toISOString()
            });
        });

        // Événement: Livreur se déconnecte explicitement
        socket.on('livreur:leave', async () => {
            console.log(`👋 Livreur ${socket.livreurId} quitte le suivi`);
            await updateLivreurStatus(socket.livreurId, 'inactive');
            socket.emit('livreur:tracking:stop', {
                message: 'Suivi arrêté',
                timestamp: new Date().toISOString()
            });
        });

        // Événement: Admin rejoint le suivi
        socket.on('admin:join', () => {
            socket.join('admin:location');
            console.log(`👨‍💼 Admin connecté pour le suivi`);
            socket.emit('admin:connected', {
                message: 'Connexion admin établie',
                timestamp: new Date().toISOString()
            });
        });

        // Événement: Dispatcher rejoint le suivi
        socket.on('dispatcher:join', () => {
            socket.join('dispatcher:tracking');
            console.log(`📡 Dispatcher connecté pour le suivi`);
            socket.emit('dispatcher:connected', {
                message: 'Connexion dispatcher établie',
                timestamp: new Date().toISOString()
            });
        });

        // Gestion de la déconnexion
        socket.on('disconnect', async () => {
            console.log(`🔌 Livreur déconnecté: ${socket.livreurId}`);

            try {
                // Mettre à jour le statut
                await updateLivreurStatus(socket.livreurId, 'inactive');

                // Notifier les admins
                socket.to('admin:location').emit('livreur:offline', {
                    livreur_id: socket.livreurId,
                    livreur_name: socket.livreurName,
                    socket_id: socket.id,
                    timestamp: new Date().toISOString()
                });

                // Notifier les dispatchers
                socket.to('dispatcher:tracking').emit('livreur:status:changed', {
                    livreur_id: socket.livreurId,
                    livreur_name: socket.livreurName,
                    status: 'inactive',
                    timestamp: new Date().toISOString()
                });

            } catch (error) {
                console.error('Erreur lors de la déconnexion:', error);
            }
        });

    } catch (error) {
        console.error('Erreur lors de la connexion:', error);
        socket.emit('error', { message: 'Erreur de connexion' });
    }
});

// Démarrage du serveur
async function startServer() {
    try {
        await initDatabase();

        // Redis optionnel - ne pas bloquer le démarrage si Redis n'est pas disponible
        try {
            await redisClient.connect();
            await redisSubscriber.connect();
            console.log(`📡 Redis connecté: ${REDIS_URL}`);
        } catch (redisError) {
            console.log(`⚠️  Redis non disponible (optionnel): ${redisError.message}`);
        }

        server.listen(PORT, '0.0.0.0', () => {
            console.log(`🚀 Serveur Socket.IO démarré sur le port ${PORT}`);
            console.log(`🌐 Accessible sur : http://192.168.1.6:${PORT}`);
            console.log(`🌐 Accessible sur : http://localhost:${PORT}`);
            console.log(`🗄️  MySQL connecté: ${dbConfig.host}:${dbConfig.database}`);
        });
    } catch (error) {
        console.error('❌ Erreur démarrage serveur:', error);
        process.exit(1);
    }
}

// Gestion des erreurs
process.on('uncaughtException', (error) => {
    console.error('❌ Exception non gérée:', error);
});

process.on('unhandledRejection', (reason, promise) => {
    console.error('❌ Promesse rejetée non gérée:', reason);
});

// Démarrage
startServer();
