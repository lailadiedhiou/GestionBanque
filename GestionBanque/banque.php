<?php
class Person {
    protected $nom;
    protected $prenom;
    protected $adresse;

    public function __construct($nom, $prenom, $adresse) {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->adresse = $adresse;
    }

    public function getNom() {
        return $this->nom;
    }

    public function setNom($nom) {
        $this->nom = $nom;
    }

    public function getPrenom() {
        return $this->prenom;
    }

    public function setPrenom($prenom) {
        $this->prenom = $prenom;
    }

    public function getAdresse() {
        return $this->adresse;
    }

    public function setAdresse($adresse) {
        $this->adresse = $adresse;
    }
}

interface Transaction {
    public function deposer(float $montant);
    public function retirer(float $montant);
    public function transfert(float $montant, Compte $destinataire);
}

abstract class Compte implements Transaction {
    protected $numeroCompte;
    protected $solde;
    protected $titulaire;

    public function __construct($numeroCompte, $titulaire, $solde = 0) {
        $this->numeroCompte = $numeroCompte;
        $this->titulaire = $titulaire;
        $this->solde = $solde;
    }

    public function deposer(float $montant) {
        $this->solde += $montant;
    }

    public function retirer(float $montant) {
        if ($this->solde >= $montant) {
            $this->solde -= $montant;
        } else {
            echo "Solde insuffisant.";
        }
    }

    public function transfert(float $montant, Compte $destinataire) {
        $this->retirer($montant);
        $destinataire->deposer($montant);
    }

    abstract public function calculerInteret();

    public function getSolde() {
        return $this->solde;
    }

    public function getNumeroCompte() {
        return $this->numeroCompte;
    }
}

class CompteCourant extends Compte {
    private $decouvertAutorise;

    public function __construct($numeroCompte, $titulaire, $solde = 0, $decouvertAutorise = 0) {
        parent::__construct($numeroCompte, $titulaire, $solde);
        $this->decouvertAutorise = $decouvertAutorise;
    }

    public function retirer(float $montant) {
        if ($this->solde + $this->decouvertAutorise >= $montant) {
            $this->solde -= $montant;
        } else {
            throw new Exception("Solde insuffisant, même avec le découvert autorisé.");
        }
    }

    public function calculerInteret() {
        
    }
}

class CompteEpargne extends Compte {
    private $tauxInteret;

    public function __construct($numeroCompte, $titulaire, $solde = 0, $tauxInteret = 0.41) {
        parent::__construct($numeroCompte, $titulaire, $solde);
        $this->tauxInteret = $tauxInteret;
    }

    public function calculerInteret() {
        $this->solde += $this->solde * $this->tauxInteret;
    }
}
class Client extends Person {
    private $comptes;

    public function __construct($nom, $prenom, $adresse) {
        parent::__construct($nom, $prenom, $adresse);
        $this->comptes = [];
    }

    public function ajouterCompte(Compte $compte) {
        $this->comptes[] = $compte;
    }

    public function getSoldeTotal() {
        $soldeTotal = 0;
        foreach ($this->comptes as $compte) {
            $soldeTotal += $compte->getSolde();
        }
        return $soldeTotal;
    }

    public function afficherComptes() {
        foreach ($this->comptes as $compte) {
            echo "Compte : " . $compte->getNumeroCompte() . " <br> Votre solde est de - Solde : " . $compte->getSolde() . "<br>";
        }
    }
}
trait Identifiable {
    protected $id;

    public function __construct() {
        $this->id = idunique();
    }

    public function getId() {
        return $this->id;
    }
}

$client = new Client("Laila", "Diedhiou", "Rue Rurisque");

$compteCourant = new CompteCourant("CC(compt courant)", $client, 10000, 100);
$compteEpargne = new CompteEpargne("CE(compte epargne)", $client, 7000, 0.03);

$client->ajouterCompte($compteCourant);
$client->ajouterCompte($compteEpargne);


$compteCourant->deposer(500);
$compteCourant->retirer(150);


$compteCourant->transfert(50, $compteEpargne);

echo "Bienvenue dans le compte de " . $client->getNom() . " " . $client->getPrenom()." de ".$client->getAdresse(). ":<br>";
$client->afficherComptes();

?>
