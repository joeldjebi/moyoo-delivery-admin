// FLUTTER TIMEOUT FIX - Code complet pour résoudre les timeouts
// Copiez ce code dans votre application Flutter

import 'dart:async';
import 'dart:io';
import 'package:geolocator/geolocator.dart';
import 'package:socket_io_client/socket_io_client.dart' as IO;
import 'package:http/http.dart' as http;

class AppConfig {
  // Timeouts
  static const int POSITION_TIMEOUT = 30; // secondes
  static const int SOCKET_TIMEOUT = 30; // secondes
  static const int API_TIMEOUT = 15; // secondes

  // Retry
  static const int MAX_RETRY_ATTEMPTS = 3;
  static const int RETRY_DELAY = 5; // secondes

  // Health check
  static const int HEALTH_CHECK_INTERVAL = 60; // secondes
}

class LocationService {
  static const String BASE_URL = 'http://192.168.1.6:8000/api';

  // Obtenir la position avec timeout augmenté
  static Future<Position> getCurrentPosition() async {
    try {
      // Vérifier les permissions
      LocationPermission permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
        if (permission == LocationPermission.denied) {
          throw Exception('Permission de géolocalisation refusée');
        }
      }

      if (permission == LocationPermission.deniedForever) {
        throw Exception('Permission de géolocalisation définitivement refusée');
      }

      // Obtenir la position avec timeout augmenté
      Position position = await Geolocator.getCurrentPosition(
        desiredAccuracy: LocationAccuracy.high,
        timeLimit: Duration(seconds: AppConfig.POSITION_TIMEOUT),
      );

      return position;
    } catch (e) {
      print('Erreur géolocalisation: $e');
      rethrow;
    }
  }

  // Stream de position avec timeout
  static Stream<Position> getPositionStream() {
    return Geolocator.getPositionStream(
      locationSettings: LocationSettings(
        accuracy: LocationAccuracy.high,
        distanceFilter: 10, // Mettre à jour toutes les 10 mètres
        timeLimit: Duration(seconds: AppConfig.POSITION_TIMEOUT),
      ),
    );
  }

  // Mise à jour de position via API
  static Future<void> updateLocation({
    required int livreurId,
    required double latitude,
    required double longitude,
    required String token,
    double? accuracy,
    double? altitude,
    double? speed,
    double? heading,
    String? status,
    String? contextType,
    int? contextId,
  }) async {
    try {
      final response = await http
          .post(
            Uri.parse('$BASE_URL/livreur/location/update'),
            headers: {
              'Content-Type': 'application/json',
              'Authorization': 'Bearer $token',
            },
            body: jsonEncode({
              'livreur_id': livreurId,
              'latitude': latitude,
              'longitude': longitude,
              'accuracy': accuracy,
              'altitude': altitude,
              'speed': speed,
              'heading': heading,
              'timestamp': DateTime.now().toIso8601String(),
              'status': status ?? 'en_cours',
              'context_type': contextType,
              'context_id': contextId,
            }),
          )
          .timeout(Duration(seconds: AppConfig.API_TIMEOUT));

      if (response.statusCode == 200) {
        print('✅ Position mise à jour avec succès');
      } else {
        print('❌ Erreur API: ${response.statusCode}');
      }
    } catch (e) {
      print('❌ Erreur mise à jour position: $e');
      rethrow;
    }
  }
}

class SocketService {
  static const String SOCKET_URL = 'http://192.168.1.6:3000';
  IO.Socket? _socket;
  String? _lastToken;
  Timer? _reconnectTimer;
  int _reconnectAttempts = 0;
  static const int MAX_RECONNECT_ATTEMPTS = 5;

  bool isConnected() {
    return _socket?.connected == true;
  }

  void connect(String token) {
    _lastToken = token;
    _reconnectAttempts = 0;
    _connectWithRetry();
  }

  void _connectWithRetry() {
    if (_reconnectAttempts >= MAX_RECONNECT_ATTEMPTS) {
      print('❌ Nombre maximum de tentatives de reconnexion atteint');
      return;
    }

    _reconnectAttempts++;
    print(
      '🔄 Tentative de connexion ${_reconnectAttempts}/$MAX_RECONNECT_ATTEMPTS',
    );

    try {
      _socket = IO.io(SOCKET_URL, <String, dynamic>{
        'transports': ['websocket', 'polling'],
        'autoConnect': false,
        'timeout':
            AppConfig.SOCKET_TIMEOUT * 1000, // 30 secondes en millisecondes
        'auth': {'token': _lastToken},
      });

      _setupListeners();
      _socket!.connect();
    } catch (e) {
      print('❌ Erreur création socket: $e');
      _scheduleReconnect();
    }
  }

  void _setupListeners() {
    _socket!.on('connect', (data) {
      print('✅ Socket.IO connecté');
      _reconnectAttempts = 0; // Reset du compteur
    });

    _socket!.on('connect_error', (error) {
      print('❌ Erreur connexion: ${error.message}');
      _scheduleReconnect();
    });

    _socket!.on('disconnect', (reason) {
      print('⚠️ Déconnexion: $reason');
      if (reason != 'io client disconnect') {
        _scheduleReconnect();
      }
    });

    _socket!.on('location:updated', (data) {
      print('📍 Position mise à jour: $data');
    });

    _socket!.on('location:status:changed', (data) {
      print('🔄 Statut changé: $data');
    });

    _socket!.on('error', (error) {
      print('❌ Erreur Socket.IO: $error');
    });
  }

  void _scheduleReconnect() {
    _reconnectTimer?.cancel();
    int delay = _reconnectAttempts * 2; // Délai progressif
    print('⏰ Reconnexion dans $delay secondes...');

    _reconnectTimer = Timer(Duration(seconds: delay), () {
      _connectWithRetry();
    });
  }

  void emitLocationUpdate(Map<String, dynamic> data) {
    if (_socket?.connected == true) {
      _socket?.emit('location:update', data);
      print('📤 Position envoyée via Socket.IO');
    } else {
      print('⚠️ Socket.IO non connecté - position non envoyée');
    }
  }

  void emitStatusChange(Map<String, dynamic> data) {
    if (_socket?.connected == true) {
      _socket?.emit('location:status:change', data);
      print('📤 Statut envoyé via Socket.IO');
    } else {
      print('⚠️ Socket.IO non connecté - statut non envoyé');
    }
  }

  void disconnect() {
    _reconnectTimer?.cancel();
    _socket?.disconnect();
  }

  void reconnect() {
    if (_lastToken != null) {
      _reconnectAttempts = 0;
      _connectWithRetry();
    }
  }
}

class LocationTracker {
  final LocationService _locationService = LocationService();
  final SocketService _socketService = SocketService();
  StreamSubscription<Position>? _positionStream;
  Timer? _healthCheckTimer;
  Timer? _timeoutTimer;
  String? _token;
  int? _livreurId;

  void initializeLocationTracking(String token, int livreurId) async {
    try {
      _token = token;
      _livreurId = livreurId;

      // 1. Vérifier les permissions
      bool hasPermission = await _checkLocationPermission();
      if (!hasPermission) {
        throw Exception('Permission de géolocalisation refusée');
      }

      // 2. Connecter Socket.IO avec retry
      await _connectSocketWithRetry(token);

      // 3. Démarrer le tracking de position
      _startPositionTracking();

      // 4. Démarrer le health check
      _startHealthCheck();

      print('✅ Location tracking initialisé avec succès');
    } catch (e) {
      print('❌ Erreur initialisation: $e');
      _handleInitializationError(e);
    }
  }

  Future<bool> _checkLocationPermission() async {
    LocationPermission permission = await Geolocator.checkPermission();

    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
    }

    return permission == LocationPermission.whileInUse ||
        permission == LocationPermission.always;
  }

  Future<void> _connectSocketWithRetry(String token) async {
    int attempts = 0;
    const maxAttempts = 3;

    while (attempts < maxAttempts) {
      try {
        _socketService.connect(token);
        await Future.delayed(Duration(seconds: 5));

        if (_socketService.isConnected()) {
          print('✅ Socket.IO connecté');
          return;
        }

        attempts++;
        print('🔄 Tentative de connexion ${attempts}/$maxAttempts');
      } catch (e) {
        print('❌ Erreur connexion Socket.IO: $e');
        attempts++;
        await Future.delayed(Duration(seconds: 5));
      }
    }

    throw Exception('Impossible de se connecter à Socket.IO');
  }

  void _startPositionTracking() {
    _positionStream = _locationService.getPositionStream().listen(
      (Position position) {
        _onPositionUpdate(position);
        _resetTimeout();
      },
      onError: (error) {
        print('❌ Erreur stream position: $error');
        _handlePositionError(error);
      },
    );

    // Timeout de sécurité
    _timeoutTimer = Timer(Duration(seconds: AppConfig.POSITION_TIMEOUT), () {
      print('⚠️ Timeout position - tentative de reconnexion');
      _reconnectLocationStream();
    });
  }

  void _onPositionUpdate(Position position) {
    print('📍 Position: ${position.latitude}, ${position.longitude}');

    // Mise à jour via API
    _updateLocationToAPI(position);

    // Mise à jour via Socket.IO
    _updateLocationToSocket(position);

    _resetTimeout();
  }

  void _updateLocationToAPI(Position position) {
    if (_token != null && _livreurId != null) {
      _locationService
          .updateLocation(
            livreurId: _livreurId!,
            latitude: position.latitude,
            longitude: position.longitude,
            token: _token!,
            accuracy: position.accuracy,
            altitude: position.altitude,
            speed: position.speed,
            heading: position.heading,
            status: 'en_cours',
            contextType: 'ramassage', // ou 'livraison'
            contextId: 1,
          )
          .catchError((error) {
            print('❌ Erreur API: $error');
          });
    }
  }

  void _updateLocationToSocket(Position position) {
    if (_livreurId != null) {
      _socketService.emitLocationUpdate({
        'livreur_id': _livreurId,
        'latitude': position.latitude,
        'longitude': position.longitude,
        'accuracy': position.accuracy,
        'speed': position.speed,
        'heading': position.heading,
        'timestamp': DateTime.now().toIso8601String(),
        'status': 'en_cours',
        'context_type': 'ramassage',
        'context_id': 1,
      });
    }
  }

  void _resetTimeout() {
    _timeoutTimer?.cancel();
    _timeoutTimer = Timer(Duration(seconds: AppConfig.POSITION_TIMEOUT), () {
      print('⚠️ Timeout position - tentative de reconnexion');
      _reconnectLocationStream();
    });
  }

  void _reconnectLocationStream() {
    _positionStream?.cancel();
    _timeoutTimer?.cancel();

    // Attendre avant de reconnecter
    Timer(Duration(seconds: 5), () {
      _startPositionTracking();
    });
  }

  void _handlePositionError(dynamic error) {
    print('Erreur position: $error');

    if (error is TimeoutException) {
      print('⏰ Timeout position - tentative de reconnexion');
      _reconnectLocationStream();
    } else {
      print('❌ Erreur position: $error');
      // Gérer d'autres types d'erreurs
    }
  }

  void _startHealthCheck() {
    _healthCheckTimer = Timer.periodic(
      Duration(seconds: AppConfig.HEALTH_CHECK_INTERVAL),
      (timer) {
        _performHealthCheck();
      },
    );
  }

  void _performHealthCheck() {
    // Vérifier la connexion Socket.IO
    if (!_socketService.isConnected()) {
      print('⚠️ Socket.IO déconnecté - tentative de reconnexion');
      _socketService.reconnect();
    }

    // Vérifier le stream de position
    if (_positionStream == null) {
      print('⚠️ Stream position arrêté - redémarrage');
      _startPositionTracking();
    }
  }

  void _handleInitializationError(dynamic error) {
    print('❌ Erreur initialisation: $error');

    // Tentative de reconnexion après 10 secondes
    Timer(Duration(seconds: 10), () {
      if (_token != null && _livreurId != null) {
        initializeLocationTracking(_token!, _livreurId!);
      }
    });
  }

  void stopLocationTracking() {
    _positionStream?.cancel();
    _healthCheckTimer?.cancel();
    _timeoutTimer?.cancel();
    _socketService.disconnect();
    print('🛑 Location tracking arrêté');
  }
}

// Exemple d'utilisation
class MyApp extends StatefulWidget {
  @override
  _MyAppState createState() => _MyAppState();
}

class _MyAppState extends State<MyApp> {
  final LocationTracker _locationTracker = LocationTracker();
  final String _token = 'your_jwt_token_here';
  final int _livreurId = 1;

  @override
  void initState() {
    super.initState();
    _initializeLocation();
  }

  void _initializeLocation() async {
    try {
      await _locationTracker.initializeLocationTracking(_token, _livreurId);
    } catch (e) {
      print('Erreur initialisation: $e');
    }
  }

  @override
  void dispose() {
    _locationTracker.stopLocationTracking();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'MOYOO Location Tracker',
      home: Scaffold(
        appBar: AppBar(title: Text('Géolocalisation MOYOO')),
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Text('Système de géolocalisation actif'),
              SizedBox(height: 20),
              ElevatedButton(
                onPressed: _initializeLocation,
                child: Text('Redémarrer le tracking'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
