<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Faq;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqs = [
            // Catégorie Général
            [
                'category' => 'Général',
                'question' => 'Qu\'est-ce que MOYOO Delivery ?',
                'answer' => 'MOYOO Delivery est une plateforme de gestion de livraisons qui permet aux entreprises de gérer efficacement leurs colis et livraisons avec un système de suivi en temps réel.',
                'sort_order' => 1,
                'is_active' => true
            ],
            [
                'category' => 'Général',
                'question' => 'Comment puis-je commencer à utiliser la plateforme ?',
                'answer' => 'Vous pouvez commencer en créant un compte, en configurant votre entreprise, puis en ajoutant vos premiers colis. Notre équipe de support est là pour vous accompagner.',
                'sort_order' => 2,
                'is_active' => true
            ],
            [
                'category' => 'Général',
                'question' => 'La plateforme est-elle disponible en français ?',
                'answer' => 'Oui, MOYOO Delivery est entièrement disponible en français avec une interface utilisateur intuitive et des fonctionnalités adaptées au marché français.',
                'sort_order' => 3,
                'is_active' => true
            ],

            // Catégorie Tarification
            [
                'category' => 'Tarification',
                'question' => 'Quels sont les différents plans disponibles ?',
                'answer' => 'Nous proposons trois plans : Starter (29.99€/mois), Premium (99.99€/mois) et Enterprise (299.99€/mois). Chaque plan offre des fonctionnalités adaptées à vos besoins.',
                'sort_order' => 1,
                'is_active' => true
            ],
            [
                'category' => 'Tarification',
                'question' => 'Puis-je changer de plan à tout moment ?',
                'answer' => 'Oui, vous pouvez upgrader ou downgrader votre plan à tout moment. Les changements prennent effet immédiatement et les ajustements de facturation sont calculés au prorata.',
                'sort_order' => 2,
                'is_active' => true
            ],
            [
                'category' => 'Tarification',
                'question' => 'Y a-t-il des frais de configuration ?',
                'answer' => 'Non, il n\'y a aucun frais de configuration. Vous payez uniquement votre abonnement mensuel selon le plan choisi.',
                'sort_order' => 3,
                'is_active' => true
            ],

            // Catégorie Support
            [
                'category' => 'Support',
                'question' => 'Comment puis-je contacter le support ?',
                'answer' => 'Vous pouvez nous contacter par email à support@moyoo.com, par téléphone au +33 1 23 45 67 89, ou via le chat en direct dans votre dashboard.',
                'sort_order' => 1,
                'is_active' => true
            ],
            [
                'category' => 'Support',
                'question' => 'Quels sont les horaires de support ?',
                'answer' => 'Notre support est disponible 24/7 pour les plans Premium et Enterprise. Pour le plan Starter, le support est disponible du lundi au vendredi de 9h à 18h.',
                'sort_order' => 2,
                'is_active' => true
            ],

            // Catégorie Technique
            [
                'category' => 'Technique',
                'question' => 'L\'API est-elle disponible ?',
                'answer' => 'Oui, notre API REST est disponible pour les plans Premium et Enterprise. Elle permet d\'intégrer MOYOO Delivery avec vos systèmes existants.',
                'sort_order' => 1,
                'is_active' => true
            ],
            [
                'category' => 'Technique',
                'question' => 'Mes données sont-elles sécurisées ?',
                'answer' => 'Absolument. Nous utilisons un chiffrement SSL/TLS pour toutes les communications et stockons vos données dans des serveurs sécurisés conformes aux standards de sécurité.',
                'sort_order' => 2,
                'is_active' => true
            ],
            [
                'category' => 'Technique',
                'question' => 'Puis-je exporter mes données ?',
                'answer' => 'Oui, vous pouvez exporter toutes vos données (colis, livraisons, rapports) au format CSV ou Excel depuis votre dashboard.',
                'sort_order' => 3,
                'is_active' => true
            ]
        ];

        foreach ($faqs as $faq) {
            Faq::create($faq);
        }
    }
}
