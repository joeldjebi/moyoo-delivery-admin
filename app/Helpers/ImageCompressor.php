<?php

namespace App\Helpers;

class ImageCompressor
{
    /**
     * Compresser une image pour qu'elle fasse au maximum 1MB
     *
     * @param string $sourcePath Chemin vers l'image source
     * @param string $destinationPath Chemin de destination
     * @param int $maxSizeKB Taille maximale en KB (défaut: 1024KB = 1MB)
     * @param int $quality Qualité initiale (défaut: 85)
     * @return bool|string Retourne le chemin de l'image compressée ou false en cas d'erreur
     */
    public static function compress($sourcePath, $destinationPath = null, $maxSizeKB = 1024, $quality = 85)
    {
        try {
            // Si pas de destination, utiliser le même fichier
            if (!$destinationPath) {
                $destinationPath = $sourcePath;
            }

            // Vérifier que le fichier source existe
            if (!file_exists($sourcePath)) {
                return false;
            }

            // Obtenir les informations de l'image
            $imageInfo = getimagesize($sourcePath);
            if (!$imageInfo) {
                return false;
            }

            $mimeType = $imageInfo['mime'];
            $width = $imageInfo[0];
            $height = $imageInfo[1];

            // Créer l'image selon son type
            switch ($mimeType) {
                case 'image/jpeg':
                    $sourceImage = imagecreatefromjpeg($sourcePath);
                    break;
                case 'image/png':
                    $sourceImage = imagecreatefrompng($sourcePath);
                    break;
                case 'image/gif':
                    $sourceImage = imagecreatefromgif($sourcePath);
                    break;
                case 'image/webp':
                    $sourceImage = imagecreatefromwebp($sourcePath);
                    break;
                default:
                    return false;
            }

            if (!$sourceImage) {
                return false;
            }

            // Calculer les nouvelles dimensions si nécessaire
            $newWidth = $width;
            $newHeight = $height;
            $maxDimension = 1920; // Dimension maximale

            if ($width > $maxDimension || $height > $maxDimension) {
                $ratio = min($maxDimension / $width, $maxDimension / $height);
                $newWidth = intval($width * $ratio);
                $newHeight = intval($height * $ratio);
            }

            // Créer une nouvelle image avec les bonnes dimensions
            $newImage = imagecreatetruecolor($newWidth, $newHeight);

            // Préserver la transparence pour PNG
            if ($mimeType === 'image/png') {
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
                imagefill($newImage, 0, 0, $transparent);
            }

            // Redimensionner l'image
            imagecopyresampled(
                $newImage, $sourceImage,
                0, 0, 0, 0,
                $newWidth, $newHeight,
                $width, $height
            );

            // Compression itérative pour atteindre la taille cible
            $currentQuality = $quality;
            $attempts = 0;
            $maxAttempts = 10;

            do {
                // Sauvegarder l'image avec la qualité actuelle
                $success = false;
                switch ($mimeType) {
                    case 'image/jpeg':
                        $success = imagejpeg($newImage, $destinationPath, $currentQuality);
                        break;
                    case 'image/png':
                        // Pour PNG, on utilise la compression (0-9)
                        $pngQuality = intval(9 - ($currentQuality / 10));
                        $success = imagepng($newImage, $destinationPath, $pngQuality);
                        break;
                    case 'image/gif':
                        $success = imagegif($newImage, $destinationPath);
                        break;
                    case 'image/webp':
                        $success = imagewebp($newImage, $destinationPath, $currentQuality);
                        break;
                }

                if (!$success) {
                    break;
                }

                // Vérifier la taille du fichier
                $fileSizeKB = filesize($destinationPath) / 1024;

                if ($fileSizeKB <= $maxSizeKB) {
                    break; // Taille acceptable
                }

                // Réduire la qualité pour le prochain essai
                $currentQuality -= 10;
                $attempts++;

            } while ($currentQuality > 10 && $attempts < $maxAttempts);

            // Nettoyer la mémoire
            imagedestroy($sourceImage);
            imagedestroy($newImage);

            // Vérifier le résultat final
            if (file_exists($destinationPath)) {
                $finalSizeKB = filesize($destinationPath) / 1024;
                \Log::info("Image compressée: {$sourcePath} -> {$destinationPath} ({$finalSizeKB}KB)");
                return $destinationPath;
            }

            return false;

        } catch (\Exception $e) {
            \Log::error("Erreur lors de la compression d'image: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Compresser une image uploadée et la sauvegarder
     *
     * @param \Illuminate\Http\UploadedFile $file Fichier uploadé
     * @param string $directory Répertoire de destination
     * @param string $filename Nom du fichier (optionnel)
     * @param int $maxSizeKB Taille maximale en KB
     * @return bool|string Chemin de l'image compressée ou false
     */
    public static function compressUploadedFile($file, $directory, $filename = null, $maxSizeKB = 1024)
    {
        try {
            // Générer un nom de fichier si non fourni
            if (!$filename) {
                $extension = $file->getClientOriginalExtension();
                $filename = uniqid('compressed_') . '.' . $extension;
            }

            // Créer le répertoire s'il n'existe pas
            $fullDirectory = storage_path('app/public/' . $directory);
            if (!file_exists($fullDirectory)) {
                mkdir($fullDirectory, 0755, true);
            }

            // Créer le répertoire temp s'il n'existe pas
            $tempDirectory = storage_path('app/temp');
            if (!file_exists($tempDirectory)) {
                mkdir($tempDirectory, 0755, true);
            }

            // Chemin temporaire pour l'image originale
            $tempFilename = uniqid('temp_') . '.' . $file->getClientOriginalExtension();
            $tempFullPath = storage_path('app/temp/' . $tempFilename);

            // Copier le fichier uploadé vers le répertoire temp
            $file->move(storage_path('app/temp'), $tempFilename);

            // Chemin de destination
            $destinationPath = $fullDirectory . '/' . $filename;

            // Compresser l'image
            $result = self::compress($tempFullPath, $destinationPath, $maxSizeKB);

            // Supprimer le fichier temporaire
            if (file_exists($tempFullPath)) {
                unlink($tempFullPath);
            }

            if ($result) {
                return $directory . '/' . $filename;
            }

            return false;

        } catch (\Exception $e) {
            \Log::error("Erreur lors de la compression du fichier uploadé: " . $e->getMessage());
            \Log::error("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Obtenir les informations d'une image
     *
     * @param string $imagePath Chemin vers l'image
     * @return array|false Informations de l'image ou false
     */
    public static function getImageInfo($imagePath)
    {
        if (!file_exists($imagePath)) {
            return false;
        }

        $imageInfo = getimagesize($imagePath);
        if (!$imageInfo) {
            return false;
        }

        return [
            'width' => $imageInfo[0],
            'height' => $imageInfo[1],
            'mime_type' => $imageInfo['mime'],
            'size_bytes' => filesize($imagePath),
            'size_kb' => round(filesize($imagePath) / 1024, 2),
            'size_mb' => round(filesize($imagePath) / (1024 * 1024), 2)
        ];
    }
}
