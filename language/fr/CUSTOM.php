<?php

/**
 *  2Moons
 *  Copyright (C) 2011  Slaver
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package 2Moons
 * @author Slaver <slaver7@gmail.com>
 * @copyright 2009 Lucky <lucky@xgproyect.net> (XGProyecto)
 * @copyright 2011 Slaver <slaver7@gmail.com> (Fork/2Moons)
 * @license http://www.gnu.org/licenses/gpl.html GNU GPLv3 License
 * @version 1.8.x Koray Karakuş <koraykarakus@yahoo.com>
 * @info $Id: CUSTOM.php 2095 2011-12-21 19:39:40Z slaver7 $
 * @link http://code.google.com/p/2moons/
 */
  // login
$LNG['login1']                          = 'Pas de douleur, pas de gain.';
 
 $LNG['ex_a']                          = 'Bienvenue dans les Missions de l\'Univers';
$LNG['ex_a1']                         = 'Terminez quelques missions pour débloquer des récompenses :';
$LNG['ex_a2']                         = 'Sélectionner';
$LNG['ex_a3']                         = 'Anomalies / Missions';

$LNG['ex_ex']	                      = 'Anomalie';
$LNG['ex_em']                         = 'Mission supplémentaire dans U700';

//Bonus
$LNG['boni_01']                       = 'Bonus';
//$LNG['boni_02']                     = 'Toutes les 24 heures, vous pouvez obtenir un bonus. Utilisez la connexion quotidienne pour collecter des ressources et de la matière noire.';
$LNG['boni_02']                       = 'Des récompenses quotidiennes contenant de grandes quantités inconnues de ressources et de DM sont proposées ici.';
$LNG['boni_02.1']                     = 'Des récompenses quotidiennes en ressources et DM sont proposées ici.';
$LNG['boni_03']                       = 'Recevoir toutes les récompenses';
$LNG['boni_04']                       = 'En bonus, vous recevez :';
$LNG['boni_04.1']                     = 'Votre temps d\'attente n\'est pas encore terminé !!';
$LNG['boni_05']                       = '</br>';
$LNG['boni_06']                       = 'Le prochain bonus sera disponible à partir de :';
$LNG['boni_06.1']                     = 'Heure';
$LNG['boni_06.2']                     = 'de nouveau.';

// Mod Champ de Débris
$LNG['find_deb_title'] = "Rechercher Débris";
$LNG['find_deb_search'] = "Afficher les débris";
$LNG['find_deb_range'] = "Porté";
$LNG['find_deb_fleet'] = "Flotte...";
$LNG['find_deb_mv'] = "Impossible de récupérer le bonus en mode vacance !!";
$LNG['find_deb_coord'] = "Coordonnées";
$LNG['find_deb_metal'] = "Débris %s";
$LNG['find_deb_crystal'] = "Débris %s";
$LNG['find_deb_btn'] = "Collecté";
$LNG['find_deb_empty'] = "Il n'y a aucun débris dans votre portée";
$LNG['tf_tf']                            = 'Champs de débris';
$LNG['tf_txt']                           = 'Collectez les champs de débris autour de vous. Envoyez des recycleurs pour extraire les ressources. En développant vos recherches, vous pouvez explorer plus loin dans l\'univers.';

// Mod Astéroïde
$LNG['type_mission_18'] = 'Astéroïdes';
$LNG['fl_no_asteroid_slot']	= 'Vous ne pouvez pas lancer d\'autres missions d\'astéroïdes !';
$LNG['custom_asteroid']	= 'L\'événement Astéroïde a commencé<br>L\'astéroïde se trouve dans la galaxie et ressemble à cette image <img src="styles/theme/gow/planeten/small/s_asteroid.png" height="16" width="16"><br>CONSEIL : Un exemple d\'astéroïde se trouve aux coordonnées suivantes [%s:%s:%s]<br>Chaque astéroïde que vous extrayez vous rapporte des ressources.';
$LNG['total_recycle_asteroid'] = 'Vous avez extrait toutes les ressources de l\'astéroïde en une seule fois. Bonus : 500 %s.<br>';
$LNG['msg_asteroid_title'] = 'Astéroïdes';
$LNG['msg_asteroid_actif'] = 'Activer l\'événement Astéroïde';
$LNG['msg_asteroid_actif_desc'] = 'Lancez l\'événement. Il sera activé lors de l\'exécution du cronjob.';
$LNG['msg_asteroid_metal'] = 'Quantité de ';
$LNG['msg_asteroid_metal_desc'] = 'Quantité de ressources disponibles dans l\'astéroïde.';
$LNG['msg_asteroid_crystal'] = 'Quantité de ';
$LNG['msg_asteroid_crystal_desc'] = 'Quantité de ressources disponibles dans l\'astéroïde.';
$LNG['msg_asteroid_deuterium'] = 'Quantité de ';
$LNG['msg_asteroid_deuterium_desc'] = 'Quantité de ressources disponibles dans l\'astéroïde.';
$LNG['msg_asteroid_max'] = 'Nombre maximum d\'astéroïdes dans la galaxie.';
$LNG['msg_asteroid_max_desc'] = 'Nombre maximum d\'astéroïdes ajoutés aléatoirement dans la galaxie.';
$LNG['cronjob_asteroid_planet_name'] = 'Astéroïde';
$LNG['cronjob_asteroid_msg_from'] = 'Système d\'Événements';
$LNG['cronjob_asteroid_msg_to'] = 'Message de l\'Événement';
$LNG['cronjob_asteroid_msg_user_event_close'] = 'L\'événement Astéroïde est terminé, tous les astéroïdes ont été détruits.';



// Mission de sauvegarde
$LNG['type_mission_19']	= 'Mission de sauvegarde';
$LNG['modul_51'] = 'Mission - Sauvegarde';

//Quest
$LNG['quest_categorie_1'] = "Construction";
$LNG['quest_categorie_2'] = "Recherche";
$LNG['quest_categorie_3'] = "Flotte";
$LNG['quest_categorie_4'] = "Défense";
$LNG['quest_categorie_5'] = "Officiers";

$LNG['quest_01'] = "Créer une catégorie";
$LNG['quest_02'] = "Créer une quête";
$LNG['quest_03'] = "Quête active";
$LNG['quest_04'] = "Sélectionner une catégorie*";
$LNG['quest_05'] = "Nom de la quête*";
$LNG['quest_06'] = "Description de la quête";
$LNG['quest_07'] = "Objectif";
$LNG['quest_08'] = "Niveau cible";
$LNG['quest_09'] = "Points de récompense";
$LNG['quest_10'] = "Récompense";
$LNG['quest_11'] = "Ajouter";
$LNG['quest_12'] = "Créer une catégorie";
$LNG['quest_13'] = "Mission événement";
$LNG['quest_14'] = "Fin de l’événement";
$LNG['quest_15'] = "Créer une quête";
$LNG['quest_16'] = "Fin de l’événement";
$LNG['quest_17'] = "Catégories";
$LNG['quest_18'] = "Quêtes";
$LNG['quest_19'] = "ID de quête";

$LNG['quest_20'] = "Démarrer la quête";
$LNG['quest_21'] = "Missions du jeu";
$LNG['quest_22'] = "Récompenses";
$LNG['quest_23'] = "Catégorie";
$LNG['quest_24'] = "Description";
$LNG['quest_25'] = "Objectif";
$LNG['quest_26'] = "Quête active";
$LNG['quest_27'] = "Créer";
$LNG['quest_28'] = "Mission spéciale";
$LNG['quest_29'] = "Mission spéciale expirée";
$LNG['quest_30'] = "Action";
$LNG['quest_31'] = "Objectif";

$LNG['quest_32'] = "Aucune quête n’est disponible dans cette catégorie.";
$LNG['quest_33'] = "Non démarrée";
$LNG['quest_34'] = "Cet événement est déjà terminé.";
$LNG['quest_35'] = "La catégorie sélectionnée n’existe pas.";
$LNG['quest_36'] = "Sélectionner une catégorie";
$LNG['quest_37'] = "Ce numéro de catégorie existe déjà.";
$LNG['quest_38'] = "Terminée";
$LNG['quest_39'] = "Fin de l’événement :";
$LNG['quest_40'] = "En cours";
$LNG['quest_41'] = "Terminé";
$LNG['quest_42'] = "Aucun contenu de quête n’est actuellement disponible.";
$LNG['quest_loading'] = "Chargement des quêtes ...";


// =========================================================
// TEXTES DE QUÊTE SUPPLÉMENTAIRES
// =========================================================

$LNG['quest_event'] = "Événement";
$LNG['quest_event_finished'] = "Événement terminé";

$LNG['quest_error_not_exists'] =
    "Cette quête n’existe plus.";

$LNG['quest_error_not_active'] =
    "Cette quête n’est pas active.";

$LNG['quest_error_already_accepted'] =
    "Vous avez déjà accepté cette quête.";

$LNG['quest_error_invalid_target'] =
    "Objectif de quête invalide.";

$LNG['quest_error_not_found'] =
    "La quête n’a pas été trouvée.";

$LNG['quest_error_already_finished'] =
    "Vous avez déjà terminé cette quête.";

$LNG['quest_started'] =
    "La quête « %s » a été démarrée.";

$LNG['quest_finish_success'] =
    "Quête terminée. Les récompenses ont été créditées sur votre compte.";

$LNG['quest_finish_invalid'] =
    "Les conditions requises pour terminer cette quête ne sont pas encore remplies.";


// =========================================================
// MOD QUESTS - ADMIN
// =========================================================

$LNG['adm_quest_info_title'] = "Informations de fonctionnement";

$LNG['adm_quest_info_content'] = "
<p>Pour utiliser ce mod de manière optimale, nous recommandons ce qui suit :</p>

<ul style='margin-top: 20px;'>
    <li style='padding-bottom: 10px;'>1 - Créez toutes les catégories nécessaires</li>
    <li style='padding-bottom: 10px;'>2 - Actualisez la page afin que les catégories apparaissent dans la liste de sélection des quêtes</li>
    <li style='padding-bottom: 10px;'>3 - Créez vos différentes quêtes</li>
</ul>

<p style='color: red; font-weight: bold; font-size: 18px; margin-top: 10px;'>
Attention : les catégories qui ne sont pas prises en charge par le système de quêtes peuvent ne pas être réalisables par les joueurs.
</p>
";


// If you have custom lang vars, you can include them here. The file ll be not overwrite by updatemanager
// Also you can overwrite exists var, too. You use the same key.
// MOD FIND DEBRIS

?>
