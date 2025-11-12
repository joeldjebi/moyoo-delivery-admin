<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Str;

class StockDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $entrepriseId = 1;

        // Vérifier que l'entreprise existe
        $entreprise = \App\Models\Entreprise::find($entrepriseId);
        if (!$entreprise) {
            $this->command->error("L'entreprise avec l'ID {$entrepriseId} n'existe pas.");
            return;
        }

        // Récupérer le premier utilisateur de l'entreprise pour les mouvements
        $user = User::where('entreprise_id', $entrepriseId)->first();
        if (!$user) {
            $this->command->error("Aucun utilisateur trouvé pour l'entreprise ID {$entrepriseId}.");
            return;
        }

        $this->command->info("Création des données de stock pour l'entreprise ID {$entrepriseId}...");

        // 1. Créer des catégories
        $categories = $this->createCategories($entrepriseId);
        $this->command->info("✅ " . count($categories) . " catégories créées");

        // 2. Créer des produits
        $products = $this->createProducts($entrepriseId, $categories);
        $this->command->info("✅ " . count($products) . " produits créés");

        // 3. Créer des stocks
        $stocks = $this->createStocks($entrepriseId, $products);
        $this->command->info("✅ " . count($stocks) . " stocks créés");

        // 4. Créer des mouvements de stock
        $movements = $this->createStockMovements($entrepriseId, $products, $stocks, $user->id);
        $this->command->info("✅ " . count($movements) . " mouvements de stock créés");

        $this->command->info("✅ Données de stock créées avec succès pour l'entreprise ID {$entrepriseId}!");
    }

    /**
     * Créer des catégories
     */
    private function createCategories($entrepriseId)
    {
        $categoriesData = [
            [
                'name' => 'Électronique',
                'description' => 'Appareils électroniques et accessoires',
                'icon' => 'ti ti-device-mobile',
                'sort_order' => 1
            ],
            [
                'name' => 'Vêtements',
                'description' => 'Vêtements et accessoires de mode',
                'icon' => 'ti ti-shirt',
                'sort_order' => 2
            ],
            [
                'name' => 'Alimentaire',
                'description' => 'Produits alimentaires et boissons',
                'icon' => 'ti ti-shopping-bag',
                'sort_order' => 3
            ],
            [
                'name' => 'Mobilier',
                'description' => 'Meubles et décoration',
                'icon' => 'ti ti-armchair',
                'sort_order' => 4
            ],
            [
                'name' => 'Livres',
                'description' => 'Livres et documents',
                'icon' => 'ti ti-book',
                'sort_order' => 5
            ]
        ];

        $categories = [];
        foreach ($categoriesData as $data) {
            $category = Category::firstOrCreate(
                [
                    'slug' => Str::slug($data['name']),
                    'entreprise_id' => $entrepriseId
                ],
                array_merge($data, [
                    'entreprise_id' => $entrepriseId,
                    'is_active' => true
                ])
            );
            $categories[] = $category;
        }

        return $categories;
    }

    /**
     * Créer des produits
     */
    private function createProducts($entrepriseId, $categories)
    {
        $productsData = [
            // Électronique
            [
                'name' => 'Smartphone Samsung Galaxy A54',
                'sku' => 'ELEC-SM-A54-001',
                'barcode' => '1234567890123',
                'description' => 'Smartphone Android 128GB, 6.4 pouces',
                'category_id' => $categories[0]->id,
                'price' => 150000,
                'currency' => 'XOF',
                'unit' => 'unité'
            ],
            [
                'name' => 'Écouteurs Bluetooth AirPods',
                'sku' => 'ELEC-AIRPODS-001',
                'barcode' => '1234567890124',
                'description' => 'Écouteurs sans fil avec réduction de bruit',
                'category_id' => $categories[0]->id,
                'price' => 45000,
                'currency' => 'XOF',
                'unit' => 'unité'
            ],
            [
                'name' => 'Chargeur USB-C Rapide',
                'sku' => 'ELEC-CHARGER-001',
                'barcode' => '1234567890125',
                'description' => 'Chargeur rapide 20W USB-C',
                'category_id' => $categories[0]->id,
                'price' => 5000,
                'currency' => 'XOF',
                'unit' => 'unité'
            ],
            // Vêtements
            [
                'name' => 'T-shirt Coton Blanc',
                'sku' => 'VET-TSHIRT-001',
                'barcode' => '1234567890126',
                'description' => 'T-shirt 100% coton, taille M',
                'category_id' => $categories[1]->id,
                'price' => 8000,
                'currency' => 'XOF',
                'unit' => 'unité'
            ],
            [
                'name' => 'Jean Slim Noir',
                'sku' => 'VET-JEAN-001',
                'barcode' => '1234567890127',
                'description' => 'Jean slim noir, taille 32',
                'category_id' => $categories[1]->id,
                'price' => 25000,
                'currency' => 'XOF',
                'unit' => 'unité'
            ],
            [
                'name' => 'Chaussures Sport Nike',
                'sku' => 'VET-SHOES-001',
                'barcode' => '1234567890128',
                'description' => 'Chaussures de sport Nike taille 42',
                'category_id' => $categories[1]->id,
                'price' => 55000,
                'currency' => 'XOF',
                'unit' => 'unité'
            ],
            // Alimentaire
            [
                'name' => 'Riz Basmati 5kg',
                'sku' => 'ALIM-RIZ-001',
                'barcode' => '1234567890129',
                'description' => 'Riz basmati premium 5kg',
                'category_id' => $categories[2]->id,
                'price' => 3500,
                'currency' => 'XOF',
                'unit' => 'sac'
            ],
            [
                'name' => 'Huile de Palme 1L',
                'sku' => 'ALIM-HUILE-001',
                'barcode' => '1234567890130',
                'description' => 'Huile de palme raffinée 1 litre',
                'category_id' => $categories[2]->id,
                'price' => 1200,
                'currency' => 'XOF',
                'unit' => 'bouteille'
            ],
            [
                'name' => 'Eau Minérale 1.5L',
                'sku' => 'ALIM-EAU-001',
                'barcode' => '1234567890131',
                'description' => 'Eau minérale naturelle 1.5L',
                'category_id' => $categories[2]->id,
                'price' => 500,
                'currency' => 'XOF',
                'unit' => 'bouteille'
            ],
            // Mobilier
            [
                'name' => 'Chaise Bureau Ergonomique',
                'sku' => 'MOB-CHAISE-001',
                'barcode' => '1234567890132',
                'description' => 'Chaise de bureau ergonomique avec accoudoirs',
                'category_id' => $categories[3]->id,
                'price' => 45000,
                'currency' => 'XOF',
                'unit' => 'unité'
            ],
            [
                'name' => 'Table Basse Moderne',
                'sku' => 'MOB-TABLE-001',
                'barcode' => '1234567890133',
                'description' => 'Table basse moderne en bois',
                'category_id' => $categories[3]->id,
                'price' => 35000,
                'currency' => 'XOF',
                'unit' => 'unité'
            ],
            // Livres
            [
                'name' => 'Laravel : Le Framework PHP',
                'sku' => 'LIV-LARAVEL-001',
                'barcode' => '1234567890134',
                'description' => 'Guide complet du framework Laravel',
                'category_id' => $categories[4]->id,
                'price' => 15000,
                'currency' => 'XOF',
                'unit' => 'exemplaire'
            ],
            [
                'name' => 'JavaScript : Le Guide Complet',
                'sku' => 'LIV-JS-001',
                'barcode' => '1234567890135',
                'description' => 'Guide complet du langage JavaScript',
                'category_id' => $categories[4]->id,
                'price' => 18000,
                'currency' => 'XOF',
                'unit' => 'exemplaire'
            ]
        ];

        $products = [];
        foreach ($productsData as $data) {
            $product = Product::firstOrCreate(
                [
                    'sku' => $data['sku'],
                    'entreprise_id' => $entrepriseId
                ],
                array_merge($data, [
                    'entreprise_id' => $entrepriseId,
                    'is_active' => true,
                    'sort_order' => 0
                ])
            );
            $products[] = $product;
        }

        return $products;
    }

    /**
     * Créer des stocks
     */
    private function createStocks($entrepriseId, $products)
    {
        $stocksData = [
            ['product_id' => 0, 'quantity' => 25, 'min_quantity' => 10, 'max_quantity' => 100, 'unit_cost' => 140000, 'location' => 'Entrepôt Principal'],
            ['product_id' => 1, 'quantity' => 50, 'min_quantity' => 20, 'max_quantity' => 200, 'unit_cost' => 40000, 'location' => 'Entrepôt Principal'],
            ['product_id' => 2, 'quantity' => 100, 'min_quantity' => 50, 'max_quantity' => 500, 'unit_cost' => 4500, 'location' => 'Entrepôt Principal'],
            ['product_id' => 3, 'quantity' => 150, 'min_quantity' => 50, 'max_quantity' => 500, 'unit_cost' => 7000, 'location' => 'Entrepôt Principal'],
            ['product_id' => 4, 'quantity' => 75, 'min_quantity' => 30, 'max_quantity' => 200, 'unit_cost' => 22000, 'location' => 'Entrepôt Principal'],
            ['product_id' => 5, 'quantity' => 40, 'min_quantity' => 15, 'max_quantity' => 100, 'unit_cost' => 50000, 'location' => 'Entrepôt Principal'],
            ['product_id' => 6, 'quantity' => 200, 'min_quantity' => 100, 'max_quantity' => 1000, 'unit_cost' => 3000, 'location' => 'Entrepôt Principal'],
            ['product_id' => 7, 'quantity' => 300, 'min_quantity' => 100, 'max_quantity' => 1000, 'unit_cost' => 1000, 'location' => 'Entrepôt Principal'],
            ['product_id' => 8, 'quantity' => 500, 'min_quantity' => 200, 'max_quantity' => 2000, 'unit_cost' => 400, 'location' => 'Entrepôt Principal'],
            ['product_id' => 9, 'quantity' => 15, 'min_quantity' => 5, 'max_quantity' => 50, 'unit_cost' => 40000, 'location' => 'Entrepôt Principal'],
            ['product_id' => 10, 'quantity' => 20, 'min_quantity' => 5, 'max_quantity' => 50, 'unit_cost' => 30000, 'location' => 'Entrepôt Principal'],
            ['product_id' => 11, 'quantity' => 30, 'min_quantity' => 10, 'max_quantity' => 100, 'unit_cost' => 13000, 'location' => 'Entrepôt Principal'],
            ['product_id' => 12, 'quantity' => 25, 'min_quantity' => 10, 'max_quantity' => 100, 'unit_cost' => 16000, 'location' => 'Entrepôt Principal'],
        ];

        $stocks = [];
        foreach ($stocksData as $index => $data) {
            if (isset($products[$index])) {
                $stock = Stock::firstOrCreate(
                    [
                        'product_id' => $products[$index]->id,
                        'entreprise_id' => $entrepriseId,
                        'location' => $data['location']
                    ],
                    array_merge($data, [
                        'product_id' => $products[$index]->id,
                        'entreprise_id' => $entrepriseId
                    ])
                );
                $stocks[] = $stock;
            }
        }

        return $stocks;
    }

    /**
     * Créer des mouvements de stock
     */
    private function createStockMovements($entrepriseId, $products, $stocks, $userId)
    {
        $movements = [];

        // Créer quelques entrées de stock
        foreach ($stocks as $index => $stock) {
            if (isset($products[$index])) {
                // Entrée initiale
                $movement = StockMovement::create([
                    'product_id' => $products[$index]->id,
                    'entreprise_id' => $entrepriseId,
                    'stock_id' => $stock->id,
                    'type' => StockMovement::TYPE_ENTREE,
                    'quantity' => $stock->quantity,
                    'unit_cost' => $stock->unit_cost,
                    'reason' => 'Stock initial',
                    'reference' => 'INIT-' . strtoupper(Str::random(8)),
                    'user_id' => $userId,
                    'location' => $stock->location,
                    'quantity_before' => 0,
                    'quantity_after' => $stock->quantity
                ]);
                $movements[] = $movement;

                // Quelques sorties pour certains produits
                if ($index < 5) {
                    $quantityOut = rand(5, 15);
                    $movement = StockMovement::create([
                        'product_id' => $products[$index]->id,
                        'entreprise_id' => $entrepriseId,
                        'stock_id' => $stock->id,
                        'type' => StockMovement::TYPE_SORTIE,
                        'quantity' => $quantityOut,
                        'unit_cost' => $stock->unit_cost,
                        'reason' => 'Vente',
                        'reference' => 'VTE-' . strtoupper(Str::random(8)),
                        'user_id' => $userId,
                        'location' => $stock->location,
                        'quantity_before' => $stock->quantity,
                        'quantity_after' => $stock->quantity - $quantityOut
                    ]);
                    $movements[] = $movement;

                    // Mettre à jour le stock
                    $stock->quantity -= $quantityOut;
                    $stock->save();
                }
            }
        }

        return $movements;
    }
}
