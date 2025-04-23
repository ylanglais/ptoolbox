<?php

require_once("lib/date_util.php");
require_once("lib/dbg_tools.php");
require_once("lib/util.php");

function demid_gen() {
	return gen_uuid();
}


$o->pieces->identifiantDemande   = $data["__IDDEM__"];
$o->adhesion->identifiantDemande = $data["__IDDEM__"];
$o->adhesion->informationGenerale->idSectionDeVote = $data["__COMITEMUTIALISTE__"];
$o->adhesion->informationGenerale->dateReception = $data["__TODAY__"]:
$o->adhesion->informationGenerale->dateSignature = $data["__TODAY__"]:
$o->adhesion->versementInitial->montantBrutVersement = $data["__VI_MT__"]:
$o->adhesion->versementInitial->origineDesFonds->idOrigineFondsVersement = $data["__VI_ORIGINE__"]:
$o->adhesion->versementInitial->repartitionSupports= $data["__REPART_SUP_VI__"]:
$o->adhesion->prelevementProgramme->montant= $data["__VPR_MT__"]:
$o->adhesion->prelevementProgramme->datePremierPrelevement = $data["__DVPR__"];
$o->adhesion->prelevementProgramme->periodicite = $data["__PERIODE__"];
$o->adhesion->prelevementProgramme->repartitionSupports = $data["__REPOART_SUP_VRP__"];
$o->adhesion->clauseBeneficiaireDeces->idClauseBeneficiaireDeces = $data["__IDCLAUSE__"];
$o->adhesion->clauseBeneficiaireDeces->clauseBeneficiaireDecesTexte = $data["__TXTCLAUSE__"];
$o->recueilInformation->identifiantDemande->idDemandeClient = $data["__IDDEM__"];
$o->recueilInformation->recueilInformationPersonne->regimeMatrimonial = $data["__REGMAT__"];
$o->recueilInformation->recueilInformationPersonne->nombreEnfantsPersonnesACharge = $data["__NBKID__"];
$o->recueilInformation->recueilInformationRevenusProspect->estimationRevenus = $data["__ESTREV__"];
$o->recueilInformation->recueilInformationRevenusProspect->montantRevenusProfessionnels = $data["__REVPRO__"];
$o->recueilInformation->recueilInformationRevenusProspect->montantRevenusPatrimoine = $data["__REVPAT__"];
$o->recueilInformation->recueilInformationRevenusProspect->montantRevenusDeRemplacement = $data["__REVRET__"];
$o->recueilInformation->recueilInformationRevenusProspect->montantTotalDesRevenus = $data["__REVTOT__"];
$o->recueilInformation->patrimoine->capaciteMensuelleEpargne = $data["__CME__"];
$o->recueilInformation->patrimoine->estimationPatrimoineImmobilier = $data["__ESTPAT__"];
$o->recueilInformation->patrimoine->estimationEpargneDisponible = $data["__ESTEPA__"];
$o->recueilInformation->patrimoine->estimationEpargneFinanciereAutre = $data["__ESTFIN__"];
$o->recueilInformation->objectif->objectifs->completerVosRevenus = $data["__OBJ1__"];
$o->recueilInformation->objectif->objectifs->preparerVotreRetraite = $data["__OBJ2__"];
$o->recueilInformation->objectif->objectifs->epargnerEnVueProjet = $data["__OBJ3__"];
$o->recueilInformation->objectif->objectifs->financerVosObseques = $data["__OBJ4__"];
$o->recueilInformation->objectif->objectifs->valoriserEtTransmettreCapital = $data["__OBJ5__"];
$o->recueilInformation->profilInvestisseur->codeReponses = $data["__CODEREP__"];
$o->acteurs->identifiantDemande->idDemandeClient = $data["__IDDEM__"];
$o->acteurs->souscripteur->idPersonne = $data["__IDBPU__"];
$o->acteurs->souscripteur->idPersonneModule = $data["__IDPM__"];
$o->acteurs->souscripteur->nom    = $data["__NOM__"];
$o->acteurs->souscripteur->prenom = $data["__PRENOM__"];
$o->acteurs->souscripteur->dateNaissance = $data["__DOB__"];
$o->acteurs->souscripteur->titre = $data["__TITLE__"];
$o->acteurs->souscripteur->nationalite = $data["__NAT__"];
$o->acteurs->souscripteur->situationFamiliale = $data["__SITFAM__"];
$o->acteurs->souscripteur->categorieSocioProfessionnelle = $data["__CSP__"];
$o->acteurs->souscripteur->contact->adresseMail = $data["__EMAIL__"];
$o->acteurs->souscripteur->fatca->indicateur = $data["__FATCA__"];
$o->acteurs->souscripteur->fatca->nif = $data["__NIFFATCA__"];
$o->acteurs->souscripteur->ocde->nif = $data["__NIFOCDE__"];
$o->acteurs->souscripteur->ocde->pays = $data["__OCDE_PAYS__"];
$o->acteurs->souscripteur->personnePolitiquementExposee->personnePolitiquementExposeeFonction = $data["__PPE__"];
$o->acteurs->souscripteur->personnePolitiquementExposee->personnePolitiquementExposeePays = $data["__PPEPAYS__"];
$o->acteurs->souscripteur->personnePolitiquementExposee->personnePolitiquementExposeeLienParente = $data["__PPELIEN__"];
$o->acteurs->souscripteur->nifFrancais = $data["__NIFFRA__"];
$o->acteurs->souscripteur->coordonneesBancaires->nomTitulaire-> = $data["__NOM__"];
$o->acteurs->souscripteur->coordonneesBancaires->prenomTitulaire-> = $data["__PRENOM__"];





