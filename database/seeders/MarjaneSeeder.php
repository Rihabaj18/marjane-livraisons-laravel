<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Fournisseur;
use App\Models\Produit;
use App\Models\Commande;
use App\Models\LigneCommande;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MarjaneSeeder extends Seeder
{
    public function run(): void
    {
        // ── Utilisateurs ──
        $admin = User::create([
            'nom' => 'Système', 'prenom' => 'Admin',
            'email' => 'admin@marjane.ma',
            'password' => Hash::make('marjane123'),
            'role' => 'admin',
        ]);

        $responsable = User::create([
            'nom' => 'Alaoui', 'prenom' => 'Hassan',
            'email' => 'h.alaoui@marjane.ma',
            'password' => Hash::make('marjane123'),
            'role' => 'responsable',
        ]);

        User::create([
            'nom' => 'Bennani', 'prenom' => 'Youssef',
            'email' => 'y.bennani@marjane.ma',
            'password' => Hash::make('marjane123'),
            'role' => 'magasinier',
        ]);

        User::create([
            'nom' => 'Chakir', 'prenom' => 'Fatima',
            'email' => 'f.chakir@marjane.ma',
            'password' => Hash::make('marjane123'),
            'role' => 'magasinier',
        ]);

        // ── Fournisseurs ──
        $fournisseurs = [
            ['nom' => 'Centrale Laitière', 'contact' => 'M. Tazi',      'telephone' => '0522-100200', 'email' => 'tazi@centralelaitiere.ma'],
            ['nom' => 'Cosumar',           'contact' => 'Mme. Idrissi', 'telephone' => '0522-300400', 'email' => 'idrissi@cosumar.ma'],
            ['nom' => 'Unilever Maroc',    'contact' => 'M. Berrada',  'telephone' => '0522-500600', 'email' => 'berrada@unilever.ma'],
            ['nom' => 'Bimo',              'contact' => 'Mme. Senhaji','telephone' => '0522-700800', 'email' => 'senhaji@bimo.ma'],
            ['nom' => 'Lesieur Cristal',   'contact' => 'M. El Fassi', 'telephone' => '0522-900100', 'email' => 'elfassi@lesieur.ma'],
        ];
        $f = [];
        foreach ($fournisseurs as $data) {
            $f[] = Fournisseur::create($data);
        }

        // ── Produits ──
        $produits = [
            ['reference' => 'CL-001', 'nom' => 'Lait Centrale 1L',        'unite' => 'carton'],
            ['reference' => 'CL-002', 'nom' => 'Yaourt Nature 125g',       'unite' => 'carton'],
            ['reference' => 'CS-001', 'nom' => 'Sucre Cosumar 1kg',        'unite' => 'sac'],
            ['reference' => 'UN-001', 'nom' => 'Savon Lux 90g',            'unite' => 'carton'],
            ['reference' => 'UN-002', 'nom' => 'Shampoing Sunsilk 400ml',  'unite' => 'carton'],
            ['reference' => 'BI-001', 'nom' => 'Biscuits Chamonix 100g',   'unite' => 'carton'],
            ['reference' => 'LE-001', 'nom' => 'Huile de table 1L',        'unite' => 'carton'],
        ];
        $p = [];
        foreach ($produits as $data) {
            $p[] = Produit::create($data);
        }

        // ── Commandes ──
        $c1 = Commande::create([
            'numero' => 'CMD-2024-001', 'fournisseur_id' => $f[0]->id, 'responsable_id' => $responsable->id,
            'date_prevue' => now(), 'creneau_debut' => '08:00', 'creneau_fin' => '10:00', 'statut' => 'planifiee',
        ]);
        LigneCommande::create(['commande_id' => $c1->id, 'produit_id' => $p[0]->id, 'quantite_prevue' => 50, 'prix_unitaire' => 120]);
        LigneCommande::create(['commande_id' => $c1->id, 'produit_id' => $p[1]->id, 'quantite_prevue' => 30, 'prix_unitaire' => 85]);

        $c2 = Commande::create([
            'numero' => 'CMD-2024-002', 'fournisseur_id' => $f[1]->id, 'responsable_id' => $responsable->id,
            'date_prevue' => now(), 'creneau_debut' => '10:00', 'creneau_fin' => '12:00', 'statut' => 'en_attente',
        ]);
        LigneCommande::create(['commande_id' => $c2->id, 'produit_id' => $p[2]->id, 'quantite_prevue' => 100, 'prix_unitaire' => 55]);

        $c3 = Commande::create([
            'numero' => 'CMD-2024-003', 'fournisseur_id' => $f[2]->id, 'responsable_id' => $responsable->id,
            'date_prevue' => now()->subDay(), 'creneau_debut' => '14:00', 'creneau_fin' => '16:00', 'statut' => 'validee',
        ]);
        LigneCommande::create(['commande_id' => $c3->id, 'produit_id' => $p[3]->id, 'quantite_prevue' => 40, 'prix_unitaire' => 95]);
        LigneCommande::create(['commande_id' => $c3->id, 'produit_id' => $p[4]->id, 'quantite_prevue' => 20, 'prix_unitaire' => 110]);

        $c4 = Commande::create([
            'numero' => 'CMD-2024-004', 'fournisseur_id' => $f[3]->id, 'responsable_id' => $responsable->id,
            'date_prevue' => now()->addDay(), 'creneau_debut' => '08:00', 'creneau_fin' => '09:00', 'statut' => 'en_attente',
        ]);
        LigneCommande::create(['commande_id' => $c4->id, 'produit_id' => $p[5]->id, 'quantite_prevue' => 60, 'prix_unitaire' => 75]);

        $c5 = Commande::create([
            'numero' => 'CMD-2024-005', 'fournisseur_id' => $f[4]->id, 'responsable_id' => $responsable->id,
            'date_prevue' => now()->addDays(2), 'creneau_debut' => '09:00', 'creneau_fin' => '11:00', 'statut' => 'en_attente',
        ]);
        LigneCommande::create(['commande_id' => $c5->id, 'produit_id' => $p[6]->id, 'quantite_prevue' => 80, 'prix_unitaire' => 65]);
    }
}