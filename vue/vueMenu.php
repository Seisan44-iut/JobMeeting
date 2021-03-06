<?php
require_once 'util/utilitairePageHtml.php';
require_once __DIR__."/../modele/dao/dao.php";
require_once __DIR__."/../modele/bean/Etudiant.php";
require_once __DIR__."/../modele/bean/Entreprise.php";
require_once __DIR__."/../modele/formationV2.php";
require_once __DIR__."/../controleur/ICSGeneration.php";
/**
 * Classe permettant de générer la majorité des pages du site.
 */
class VueMenu{
	/**
	 * Méthode qui permet l'affichage du formulaire d'envoi de mail Personalisé
	 * @param  array(array(String,String))  $listeMails  un tableau composé des couples (objet,contenu) de chaque mail.
	 */
	public function afficherMails($listeMails)
	{
		$util = new UtilitairePageHtml();
		echo $util->genereBandeauApresConnexion();
		?>
		<div id="main">
			<br />
			<li><a href="index.php?modele=0">Mail confirmation inscription entreprise</a></li>
			<li><a href="index.php?modele=1">Mail informations pratiques entreprise</a></li>
			<li><a href="index.php?modele=2">Mail invitation entreprise</a></li>
			<li><a href="index.php?modele=3">Mail invitation étudiant</a></li>
			<li><a href="index.php?modele=4">Mail pré-validation inscriptions</a></li>
			<li><a href="index.php?modele=-1">Nouveau mail</a></li>
			<div align="center" class="mails"><?php
			foreach($listeMails as $ind=>$mail){
					echo"\n";?>
			<a href = "index.php?modele=<?=$ind?>">
			<div class = "mailAffichage">
			<h1><?=$mail[0]?></h1>
			<?=
			// Pour ne pas interpréter les liens
			$mail[1]=str_replace(array("<a href=","</a>"),array("",""),$mail[1]);
			$mail[1]?>
			</div>
			</a>
			<?php
			}
			?>
			</div>
		</div>
		<?php
		echo $util->generePied();
	}
/**
 * Fonction permettant l'affichage de la page planning de l'étudiant.
 */
 public function afficherPlanningEtu(){
 		$dao = new Dao();
 		$tableauConfig = $dao->getConfiguration();
 		$dateEvenementTmp = explode("-",$tableauConfig['dateEvenement']);
 		$dateDebutVuePlanningTmp = explode("-",$tableauConfig['dateDebutVuePlanning']);
 		$dateEvenement = implode("/",array_reverse($dateEvenementTmp));
 		$dateDebutVuePlanning = implode("/",array_reverse($dateDebutVuePlanningTmp));
 		$idEtudiant = $_SESSION['idUser'];
 		$date = new DateTime();
 		$util = new UtilitairePageHtml();
 		echo $util->genereBandeauApresConnexion();
 	?>
 		<?php
 		$dateDebutVuePlanning2 = DateTime::createFromFormat ('d/m/Y', $dateDebutVuePlanning);
 		 if ($dateDebutVuePlanning2< $date) { ?>

 		<div id="main">
 		<p id="bonjouretudiant">

 			<br/>Bienvenue sur votre espace utilisateur créé à l'occasion des rencontres alternances du <?=$dateEvenement?>.
 <?php
 		//////////////////////////////////////ATTTENTION METTRE EN PLACE SYSTEME DATE POUR AFFICHER/////////////////////////////////////
 		//On génére l'emploi du temps
 		$dao = new Dao();
 		$tabConfig = $dao -> getConfiguration();
 		$tabEnt = $dao -> getAllEntreprises();

 		$nbCreneaux = $tabConfig["nbCreneauxAprem"] + $tabConfig["nbCreneauxMatin"];

 		if ($tabConfig["nbCreneauxMatin"] == 0) {
 			$nbCreneaux --;
 		}

 		if($tabConfig["nbCreneauxMatin"] == 0) {
 			$pauseMidi = $tabConfig["nbCreneauxMatin"];
 		} elseif ($tabConfig["nbCreneauxMatin"] != 0) {
 			$pauseMidi = $tabConfig["nbCreneauxMatin"];
 			$pauseMidi += 1;
 		}

 		$heureCreneauPauseMatin = new DateTime($tabConfig['heureCreneauPauseMatin']);
 		$heureCreneauPause = new DateTime($tabConfig['heureCreneauPause']);
 		$numCreneauPauseAprem = -1;
 		$numCreneauPauseMatin = -1;
 		$dureeCreneau = $tabConfig["dureeCreneau"];

 		//Planning du point de vue des entreprises
 		?>
 		<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
 		<script src="https://cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js"></script>
 		<script src="vue/js/selectionTab.js"></script>
 		<script type="text/javascript">
 		$(document).ready(function() {
 			var table = $('#tabPlanningEnt').dataTable({
 				"paging":         false,
 				"bSort": false,
 				"info": false
 			});
 		});
 		</script>

 		<div id="main">
 		<br/>
 		<h1>Planning Etudiant</h1>
		<?php
		// On rempli un tableau jusqu'à ce que tous les créneaux soient fais.
			$i = 0;
			$tabPropsGeneral = array();
			$nbCreneau = $dao->getNombreCreneau();
			$tabNumCreneau = array();
			$tabIdFormation = array();

			$dateDebut = $dao->getDateEvent();
			$dateDebut = explode("-",$dateDebut[0]);
			$dateDebut=$dateDebut[0].$dateDebut[1].$dateDebut[2];

			$dateFin = $dateDebut;

			$heureDebut = $dao->getNumCreneauEtu($_SESSION['idUser']);
			$titre = $dao->getIdFormationEtu($_SESSION['idUser']);


			foreach ($heureDebut as $value) {
					array_push($tabNumCreneau,$value[0]);
			}

			foreach ($titre as $value) {
				array_push($tabIdFormation,$value[0]);
			}


			foreach ($tabNumCreneau as $numeroCreno) { // Oui il y a une faute d'AURTOGRAPHE é alor ?
					$heureDebut = $dao->getHeureNumCreneau($numeroCreno);
					$heureFin = $heureDebut;
					$heureDebut = explode(":",$heureDebut[0]);

					$heureDebut = $heureDebut[0].$heureDebut[1]."00";
					$heureFin = explode(":",$heureFin[0]);


					if($heureFin[1] + $dureeCreneau >= 60){
						$heureFin[0]++;
						$heureFin[1] += $dureeCreneau - 60;
						$heureFin = $heureFin[0].$heureFin[1]."00";
					}
					else {
						$heureFin[1] += $dureeCreneau;
						$heureFin = $heureFin[0].$heureFin[1]."00";
					}

					$titre = $dao->getIDEntIDform($tabIdFormation[$i]);
					$titre = $dao->getNomEntreprise($titre);

					// echo "<br>"."dateDebut : ".$dateDebut."<br>";
					// echo "dateFin :".$dateFin."<br>";
					// echo "heureDebut :".$heureDebut."<br>";
					// echo "heureFin :".$heureFin."<br>";
					// echo "titre :".$titre."<br>";

					$tabProps1=array(
						$dateDebut,
						$heureDebut,
						$dateFin,
						$heureFin,
						$titre
					);

					array_push($tabPropsGeneral, $tabProps1);
					$i++;
			}
		 ?>
		 <a href="../ICS.php?tabGeneral=<?php echo urlencode(serialize($tabPropsGeneral))?>">  Télécharger mon planning au format agenda </a>

		<br/><br/>
 		<div class="resptab" >
 		<table id="tabPlanningEnt">


 		<thead>
 		<tr>
 			<td colspan= 1> Entreprise </td>
 			<td colspan= 1> Formation </td>
 			<?php

 			$taillePlanningmatin = $tabConfig["nbCreneauxMatin"] + 1;
 			echo'<td colspan= '.$taillePlanningmatin.'> Matin </td>';
 			echo'<td colspan= 1> Pause midi </td>';
 			$taillePlanningaprem = $tabConfig["nbCreneauxAprem"] + 1;
 			echo'<td colspan= '.$taillePlanningaprem.'> Après-midi </td>';
 			?>
 		</tr>

 		<?php
 		echo'<tr>';
 		echo'<td> </td>';
 		echo'<td> </td>';
 		$listeCreneaux = $dao->getListeCreneaux();

 		$heureCreneauApresPause = $heureCreneauPause;
 		$heureCreneauApresPause->add(new DateInterval('PT'.$dureeCreneau.'M'));
 		$heureCreneauApresPauseMatin = $heureCreneauPauseMatin;
 		$heureCreneauApresPauseMatin->add(new DateInterval('PT'.$dureeCreneau.'M'));

 		// Affichage des heures du matin
 		if($tabConfig["nbCreneauxMatin"] == 0){

 			echo '<td> </td>';
 		}else{

 			for ($i = 0; $i < $tabConfig["nbCreneauxMatin"]; $i++){
 				//On récupère le numéro de la pause du matin
 				if($listeCreneaux[$i] == $heureCreneauApresPauseMatin->format('H:i')){
 					$numCreneauPauseMatin = $i;
 				}
 			}


 			for ($i = 0; $i < $tabConfig["nbCreneauxMatin"]; $i++){
 				if ($numCreneauPauseMatin == $i) {
 					echo '<td>'."Pause Matin".'</td>';
 				}
 				echo '<td>'.$listeCreneaux[$i].'</td>';

 			}
 		}
 		// Pause du midi
 		echo '<td> </td>';

 		if($tabConfig["nbCreneauxAprem"] == 0){
 			echo '<td> </td>';
 		}else{
                        // Affichage des créneaux de l'après midi

                if ($tabConfig["nbCreneauxMatin"] == 0) {
                        for ($i = $tabConfig["nbCreneauxMatin"]; $i <= $nbCreneaux; $i++){
                        // On récupère le numéro de la pause de l'après-midi
                                 if($listeCreneaux[$i] == $heureCreneauApresPause->format('H:i')){
                                        $numCreneauPauseAprem = $i;
                                 }
                        }
                        for ($i = $tabConfig["nbCreneauxMatin"]; $i <= $nbCreneaux; $i++){
                                if ($numCreneauPauseAprem==$i) {
                                        echo '<td>'."Pause".'</td>';
                                }
                                echo '<td>'.$listeCreneaux[$i].'</td>';
                        }
                }
                else {
                        for ($i = $tabConfig["nbCreneauxMatin"]; $i < $nbCreneaux; $i++){
                        // On récupère le numéro de la pause de l'après-midi
                                if($listeCreneaux[$i] == $heureCreneauApresPause->format('H:i')){
                                        $numCreneauPauseAprem = $i;
                                }
                }
                        for ($i = $tabConfig["nbCreneauxMatin"]; $i < $nbCreneaux; $i++){
                                if ($numCreneauPauseAprem==$i) {
                                        echo '<td>'."Pause".'</td>';
                                }
                                echo '<td>'.$listeCreneaux[$i].'</td>';
                        }
                }
 		}
 		if ($tabConfig["nbCreneauxMatin"] != 0) {
 			$numCreneauPauseAprem ++;
 		}
 		echo'</tr>
 		</thead>
 		<tbody id="planning">';
 		foreach ($tabEnt as $ent) {
 			$tabForm = $dao -> getFormationsEntreprise($ent -> getID());
 		foreach ($tabForm as $form) {
 			$res = false;
 			for($i = 0; $i <= $nbCreneaux+1; $i++) {
 				if (($idEtudiant == ($dao -> getCreneau($i, $form['IDformation']))) && ($i != $numCreneauPauseMatin) && ( $i != $numCreneauPauseAprem)) {
 					$res = true;
 				}
 			}

 			if ($res) {

 				echo '<tr id="entreprise">
 				<td><a href="index.php?profil='.$ent->getID().'&type=Ent">'.$ent->getNomEnt().'</a>
 				</td>
 				<td>'
 				.$form['typeFormation'].
 				'</td>';
 				;
 				if ($tabConfig["nbCreneauxMatin"]==0) {
 					echo'<td id="pause_midi"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp</td>';
 				}
 				for($i = 0; $i <= $nbCreneaux+1; $i++) {
 					if ($i == $pauseMidi) {
 						echo'<td id="pause_midi"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp</td>';
 					}
 					echo '<td class=colorMe>';
 					// Si c'est la pause on affiche un indicateur de pause
 					if (($i == $numCreneauPauseAprem) ||($i == $numCreneauPauseMatin)) {
 						echo'-';
 					}
 					// Si ce n'est pas la pause, on affiche l'étudiant affecté à ce créneau
 					else {
 						echo $dao -> getNomEtudiant($dao -> getCreneau($i, $form['IDformation']));
 					}
 				}
 				if ($tabConfig["nbCreneauxAprem"]==0){
 					echo'<td id="pause_midi"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp</td>';
 					echo'<td id="pause_midi"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp</td>';
 				}
 				echo '</td> ';

 			echo '</tr>';
 		}
 			}
 	}
 		?>
 	</tbody>
 	</table>
 	</diV>
 			<?php
 	    	}
 			  else {
 			 	echo "  Les emplois du temps relatifs à cet événement, le vôtre y compris, n'ont toujours pas été générés. L'administrateur vous en informera lorsque ceux-ci seront disponibles.";
 			  }
 		?>
 		</tbody>
 		</table>
 		</diV>
 		<p> <br/> </p>
 	    <?php
 	    //Planning du point de vue des Etudiants
 			echo $util->generePied();
 			?>
 		</body>
 		</html>

 	<br/><br/>
 	</div>
 		<?php
 	}

	public function afficherPlanning() {
                ?>

                <?php
                //////////////////////////////////////ATTTENTION METTRE EN PLACE SYSTEME DATE POUR sAFFICHER/////////////////////////////////////
                //On g??n??re l'emploi du temps
                $dao = new Dao();
                $tabConfig = $dao -> getConfiguration();
                $tabEnt = $dao -> getAllEntreprises();

                $nbCreneaux = $tabConfig["nbCreneauxAprem"] + $tabConfig["nbCreneauxMatin"];
                $pauseMidi = $tabConfig["nbCreneauxMatin"]+1;

                $heureCreneauPauseMatin = new DateTime($tabConfig['heureCreneauPauseMatin']);
                $heureCreneauPause = new DateTime($tabConfig['heureCreneauPause']);
                $numCreneauPauseAprem = -1;
                $numCreneauPauseMatin = -1;
                $dureeCreneau = $tabConfig["dureeCreneau"];


                //Planning du point de vue des entreprises
                ?>
                <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
                <script



src="https://cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js"></script>
                <script src="vue/js/selectionTab.js"></script>
                <script type="text/javascript">
                $(document).ready(function() {
                        var table = $('#tabPlanningEnt').dataTable({
                                "paging":         false,
                                "bSort": false,
                                "info": false
                        });
                });
                </script>

                <div id="main">
                <br/>
                <h1>Planning Entreprises</h1>
                <div class="resptab" >
                <table id="tabPlanningEnt">


                <thead>
                <tr>
                        <td colspan= 1> Entreprise </td>
                        <td colspan= 1> Formation </td>
                        <?php

                        $taillePlanningmatin = $tabConfig["nbCreneauxMatin"] + 1;
                        echo'<td colspan= '.$taillePlanningmatin.'> Matin </td>';
                        echo'<td colspan= 1> Pause midi </td>';
                        $taillePlanningaprem = $tabConfig["nbCreneauxAprem"] + 1;
                        echo'<td colspan= '.$taillePlanningaprem.'> Après-midi </td>';
                        ?>
                </tr>

                <?php
                echo'<tr>';
                echo'<td> </td>';
                echo'<td> </td>';
                $listeCreneaux = $dao->getListeCreneaux();

                $heureCreneauApresPause = $heureCreneauPause;
                $heureCreneauApresPause->add(new DateInterval('PT'.$dureeCreneau.'M'));
                $heureCreneauApresPauseMatin = $heureCreneauPauseMatin;
                $heureCreneauApresPauseMatin->add(new DateInterval('PT'.$dureeCreneau.'M'));

                // Affichage des heures du matin
                if($tabConfig["nbCreneauxMatin"] == 0){
                        echo '<td> </td>';
                }else{

                        for ($i = 0; $i < $tabConfig["nbCreneauxMatin"]; $i++){
                                //On r??cup??re le num??ro de la pause du matin
                                if($listeCreneaux[$i] == $heureCreneauApresPauseMatin->format('H:i')){
                                        $numCreneauPauseMatin = $i;
                                }
                        }


                        for ($i = 0; $i < $tabConfig["nbCreneauxMatin"]; $i++){
                                if ($numCreneauPauseMatin == $i) {
                                        echo '<td>'."Pause Matin".'</td>';
                                }
                                echo '<td>'.$listeCreneaux[$i].'</td>';

                        }
                }
                // Pause du midi
                echo '<td> </td>';

                if($tabConfig["nbCreneauxAprem"] == 0){
                        echo '<td> </td>';
                }else{
                        // Affichage des cr??neaux de l'apr??s midi

                for ($i = $tabConfig["nbCreneauxMatin"]; $i <= $nbCreneaux; $i++){
                        // On r??cup??re le num??ro de la pause de l'apr??s-midi
                        if($listeCreneaux[$i] == $heureCreneauApresPause->format('H:i')){
                                $numCreneauPauseAprem = $i;
                        }
                }
                for ($i = 1+$tabConfig["nbCreneauxMatin"]; $i <= $nbCreneaux; $i++){
                        if ($numCreneauPauseAprem==$i) {
                                echo '<td>'."Pause".'</td>';
                        }
                        echo '<td>'.$listeCreneaux[$i].'</td>';
                        }
                }

                echo'</tr>
                </thead>
                <tbody id="planning">';
                foreach ($tabEnt as $ent) {
                        $tabForm = $dao -> getFormationsEntreprise($ent -> getID());
                foreach ($tabForm as $form) {
                        echo '<tr id="entreprise">
                        <td><a href="index.php?profil='.$ent->getID().'&type=Ent">'.$ent->getNomEnt().'</a>
                        </td>
                        <td>'
                        .$form['typeFormation'].
                        '</td>';
                        ;
                        if ($tabConfig["nbCreneauxMatin"]==0) {
                                echo'<td id="pause_midi"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp</td>';
                        }
                        for($i = 0; $i <= $nbCreneaux+1; $i++) {
                                if ($i == $pauseMidi) {
                                        echo'<td id="pause_midi"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp</td>';
                                }
                                echo '<td class=colorMe>';
                                // Si c'est la pause on affiche un indicateur de pause
                                if (($i == $numCreneauPauseAprem) ||($i == $numCreneauPauseMatin)) {
                                        echo'-';
                                }
                                // Si ce n'est pas la pause, on affiche l'??tudiant affect?? ?? ce cr??neau
                                else {
                                        echo $dao -> getNomEtudiant($dao -> getCreneau($i, $form['IDformation']));
                                }
                        }
                        if ($tabConfig["nbCreneauxAprem"]==0){
                                echo'<td id="pause_midi"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp</td>';
                                echo'<td id="pause_midi"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp</td>';
                        }
                        echo '</td> ';
                }
                        echo '</tr>';
        }
                ?>
        </tbody>
        </table>
        </diV>
        <?php
        }
	/**
	 * Fonction permettant l'affichage de la page planning de l'entreprise.
	 */
	 public function afficherPlanningEnt(){
	 $dao = new Dao();
	 $etu = new Etudiant();
	 		$tableauConfig = $dao->getConfiguration();
	 		$dateEvenementTmp = explode("-",$tableauConfig['dateEvenement']);
	 		$dateDebutVuePlanningTmp = explode("-",$tableauConfig['dateDebutVuePlanning']);
	 		$dateEvenement = implode("/",array_reverse($dateEvenementTmp));
	 		$dateDebutVuePlanning = implode("/",array_reverse($dateDebutVuePlanningTmp));
	 		$date = new DateTime();
	 		$util = new UtilitairePageHtml();
	 		echo $util->genereBandeauApresConnexion();
	 		$idEntreprise = $_SESSION['idUser'];

	 	?>
	 		<?php
	 		$dateDebutVuePlanning2 = DateTime::createFromFormat ('d/m/Y', $dateDebutVuePlanning);

	 		 if ($dateDebutVuePlanning2< $date) { ?>
	 		<div id="main">
	 		<p id="bonjourEnt">
	 			<br/>Bienvenue sur votre espace utilisateur créé à l'occasion des rencontres alternances du <?=$dateEvenement?>.

	 		<?php
	 		//////////////////////////////////////ATTTENTION METTRE EN PLACE SYSTEME DATE POUR AFFICHER/////////////////////////////////////
	 		//On génére l'emploi du temps
	 		$dao = new Dao();
	 		$tabConfig = $dao -> getConfiguration();
	 		$tabEnt = $dao -> getAllEntreprises();

	 		$nbCreneaux = $tabConfig["nbCreneauxAprem"] + $tabConfig["nbCreneauxMatin"];

	 		if ($tabConfig["nbCreneauxMatin"] == 0) {
	 			$nbCreneaux --;
	 		}

	 		if($tabConfig["nbCreneauxMatin"] == 0) {
	 			$pauseMidi = $tabConfig["nbCreneauxMatin"];
	 		} elseif ($tabConfig["nbCreneauxMatin"] != 0) {
	 			$pauseMidi = $tabConfig["nbCreneauxMatin"];
	 			$pauseMidi += 1;
	 		}

	 		$heureCreneauPauseMatin = new DateTime($tabConfig['heureCreneauPauseMatin']);
	 		$heureCreneauPause = new DateTime($tabConfig['heureCreneauPause']);
	 		$numCreneauPauseAprem = -1;
	 		$numCreneauPauseMatin = -1;
	 		$dureeCreneau = $tabConfig["dureeCreneau"];

	 		//Planning du point de vue des entreprises
	 		?>
	 		<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
	 		<script src="https://cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js"></script>
	 		<script src="vue/js/selectionTab.js"></script>
	 		<script type="text/javascript">
	 		$(document).ready(function() {
	 			var table = $('#tabPlanningEnt').dataTable({
	 				"paging":         false,
	 				"bSort": false,
	 				"info": false
	 			});
	 		});
	 		</script>

	 		<div id="main">
	 		<br/>
	 		<h1>Planning Entreprises</h1>
	 		<div class="resptab" >
	 		<table id="tabPlanningEnt">


	 		<thead>
	 		<tr>
	 			<td colspan= 1> Entreprise </td>
	 			<td colspan= 1> Formation </td>
	 			<?php

	 			$taillePlanningmatin = $tabConfig["nbCreneauxMatin"] + 1;
	 			echo'<td colspan= '.$taillePlanningmatin.'> Matin </td>';
	 			echo'<td colspan= 1> Pause midi </td>';
	 			$taillePlanningaprem = $tabConfig["nbCreneauxAprem"] + 1;
	 			echo'<td colspan= '.$taillePlanningaprem.'> Après-midi </td>';
	 			?>
	 		</tr>

	 		<?php
	 		echo'<tr>';
	 		echo'<td> </td>';
	 		echo'<td> </td>';
	 		$listeCreneaux = $dao->getListeCreneaux();

	 		$heureCreneauApresPause = $heureCreneauPause;
	 		$heureCreneauApresPause->add(new DateInterval('PT'.$dureeCreneau.'M'));
	 		$heureCreneauApresPauseMatin = $heureCreneauPauseMatin;
	 		$heureCreneauApresPauseMatin->add(new DateInterval('PT'.$dureeCreneau.'M'));

	 		// Affichage des heures du matin
	 		if($tabConfig["nbCreneauxMatin"] == 0){

	 			echo '<td> </td>';
	 		}else{

	 			for ($i = 0; $i < $tabConfig["nbCreneauxMatin"]; $i++){
	 				//On récupère le numéro de la pause du matin
	 				if($listeCreneaux[$i] == $heureCreneauApresPauseMatin->format('H:i')){
	 					$numCreneauPauseMatin = $i;
	 				}
	 			}


	 			for ($i = 0; $i < $tabConfig["nbCreneauxMatin"]; $i++){
	 				if ($numCreneauPauseMatin == $i) {
	 					echo '<td>'."Pause Matin".'</td>';
	 				}
	 				echo '<td>'.$listeCreneaux[$i].'</td>';

	 			}
	 		}
	 		// Pause du midi
	 		echo '<td> </td>';

	 		if($tabConfig["nbCreneauxAprem"] == 0){
	 			echo '<td> </td>';
	 		}else{
                        // Affichage des créneaux de l'après midi

                                if ($tabConfig["nbCreneauxMatin"] == 0) {
                                        for ($i = $tabConfig["nbCreneauxMatin"]; $i <= $nbCreneaux; $i++){
                                        // On récupère le numéro de la pause de l'après-midi
                                                 if($listeCreneaux[$i] == $heureCreneauApresPause->format('H:i')){
                                                        $numCreneauPauseAprem = $i;
                                                 }
                                        }
                                        for ($i = $tabConfig["nbCreneauxMatin"]; $i <= $nbCreneaux; $i++){
                                                if ($numCreneauPauseAprem==$i) {
                                                        echo '<td>'."Pause".'</td>';
                                                }
                                                echo '<td>'.$listeCreneaux[$i].'</td>';
                                        }
                                }
                                else {
                                        for ($i = $tabConfig["nbCreneauxMatin"]; $i < $nbCreneaux; $i++){
                                        // On récupère le numéro de la pause de l'après-midi
                                                if($listeCreneaux[$i] == $heureCreneauApresPause->format('H:i')){
                                                        $numCreneauPauseAprem = $i;
                                                }
                                }
                                        for ($i = $tabConfig["nbCreneauxMatin"]; $i < $nbCreneaux; $i++){
                                                if ($numCreneauPauseAprem==$i) {
                                                        echo '<td>'."Pause".'</td>';
                                                }
                                                echo '<td>'.$listeCreneaux[$i].'</td>';
                                        }
                                }
	 		}
	 		if ($tabConfig["nbCreneauxMatin"] != 0) {
	 			$numCreneauPauseAprem ++;
	 		}
	 		echo'</tr>
	 		</thead>
	 		<tbody id="planning">';
	 		foreach ($tabEnt as $ent) {
	 			$tabForm = $dao -> getFormationsEntreprise($ent -> getID());
	 		foreach ($tabForm as $form) {
	 			if ($ent->getID() == $idEntreprise) {
	 				echo '<tr id="entreprise">
	 				<td><a href="index.php?profil='.$ent->getID().'&type=Ent">'.$ent->getNomEnt().'</a>
	 				</td>
	 				<td>'
	 				.$form['typeFormation'].
	 				'</td>';
	 				;
	 				if ($tabConfig["nbCreneauxMatin"]==0) {
	 					echo'<td id="pause_midi"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp</td>';
	 				}
	 				for($i = 0; $i <= $nbCreneaux+1; $i++) {
	 					if ($i == $pauseMidi) {
	 						echo'<td id="pause_midi"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp</td>';
	 					}
	 					echo '<td class=colorMe>';
	 					// Si c'est la pause on affiche un indicateur de pause
	 					if (($i == $numCreneauPauseAprem) ||($i == $numCreneauPauseMatin)) {
	 						echo'-';
	 					}
	 					// Si ce n'est pas la pause, on affiche l'étudiant affecté à ce créneau
	 					else {
							$etuNom= $dao -> getNomEtudiant($dao -> getCreneau($i, $form['IDformation']));
							$etuId =  $dao->getIdEtudiant($etuNom);
							echo "<a href=\"index.php?profil=$etuId[0]&type=Etu\"> $etuNom </a>";
	 					}
	 				}
	 				if ($tabConfig["nbCreneauxAprem"]==0){
	 					echo'<td id="pause_midi"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp</td>';
	 					echo'<td id="pause_midi"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp</td>';
	 				}
	 				echo '</td> ';
	 		}
	 		}
	 			echo '</tr>';
	 	}
	 		?>
	 	</tbody>
	 	</table>
	 	</diV>
	 	<?php
	 	    	}
	 			  else {
	 			 	echo "  Les emplois du temps relatifs à cet événement, le vôtre y compris, n'ont toujours pas été générés. L'administrateur vous en informera lorsque ceux-ci seront disponibles.";
	 			  }
	 		?>
	 		</tbody>
	 		</table>
	 		</diV>
	 			<p>
	 			<br/>
	 			</p>

	 	<br/><br/>
	 	</div>
	 		<?php
	 		echo $util->generePied();
	 	}
		
		//PROJET/////////////////////////////////////////////////////////////////////////////////
		 public function afficherPlanningResponsable(){

				 $dao = new Dao();
				 $tableauConfig = $dao->getConfiguration();
				 $dateEvenementTmp = explode("-",$tableauConfig['dateEvenement']);
				 $dateEvenement = implode("/",array_reverse($dateEvenementTmp));

				 $util = new UtilitairePageHtml();
				 echo $util->genereBandeauApresConnexion();
			 ?>

			 <div id="main">
				 <p id="bonjourResponsable">
					 <br/>Bienvenue sur votre espace responsable créé à l'occasion des rencontres alternances du <?=$dateEvenement?>.
					 <br> <br/ > <a title="Ajouter un étudiant" href="#" onclick="afficherAjoutEtu();return false;"></a>
				 <br/ ></p>
<!-- PROJET , inserer form menu suppr et ajout
				  -->
					<script src="vue/js/AjoutEtudiantPlanning.js"></script>
				 <script>
				 function checkDelete() {
					 if (confirm('Êtes-vous sûr(e) de vouloir supprimer l\'étudiant ?')) {
							 return true;
							 <?php $dao->supprimerEtuCreneau($_POST['numero_creneau'],$_POST['idEtudiantCre']); ?>
					 } else {
							 return false;
					 }
				 }
				 </script>

			 <script>
			 function afficherSupprEtu(){
				 document.getElementById("formSupprEtu").style = "display";
				 document.getElementById("formAjoutEtu").style = "display:none";
			 }
			 function afficherAjoutEtu(){
				 document.getElementById("formAjoutEtu").style = "display";
				 document.getElementById("formSupprEtu").style = "display:none";

			 }
			 </script>

			 <script type="text/javascript">
			 function maj_etudiant(oSelect){
				 var formation_selectionne = oSelect.options[oSelect.selectedIndex].value;
				 console.log(formation_selectionne);
				 $.ajax({
					 dataType : 'json',
					 url: 'index.php',
					 type: 'GET',
					 data: {
						 formSelectionee : formation_selectionne
					 },

					 success : function(reponse){
							 readData(reponse);
						 },
					 error: function(reponse){
						 console.log(reponse,"error");
					 }
				 });
			 }

						 function readData(oData){
				 var oSelect = document.getElementById("nom_etu");
				 var oOpts = oSelect.getElementsByTagName("option");

				 var oOption, oInner;

				 oSelect.innerHTML = "";
				 while(oOpts[1]){
					 oSelect.removeChild(oOpts[1]);
				 }
				 $.each(oData, function(i, item){

					 oOption = document.createElement("option");
					 oInner = document.createTextNode(item[2]);
					 oOption.value = item[0];
					 oOption.appendChild(oInner);
					 oSelect.appendChild(oOption);
					 console.log(item[0]);
				 })
			 }

			 </script>


			 <?php
			 if(isset($_POST['ajouterEtudiantCr'])) {
				 /*
				 1. Vérifier que l'entreprise recherche bien la formation de l'étudiant
				 2. Vérifier que l'étudiant ne soit pas déjà présent sur le créneau
				 3. Vérifier qu'il n'y a pas déjà un étudiant sur le créneau de la formation
				 */
				 $_POST['idFormationEntrepriseCre'] = $dao->getIDFormation($_POST['idFormationEntrepriseCre'], $_POST['idEntrepriseCre']);
				 $verifEtuSurCreneau = $dao->verifEtuSurCreneau($_POST['idEtudiantCre'], $_POST['numero_creneau']);
				 $verifEtuSurCreneauEnt = $dao->verifEtuSurCreneauEntreprise($_POST['idFormationEntrepriseCre'], $_POST['numero_creneau']);

				 //1.
					 if($_POST['idFormationEntrepriseCre']==null){
						 echo "L'entreprise ne recherche pas d'étudiant dans cette formation";
					 }
				 //2.
					 elseif ($_POST['idEtudiantCre'] == $verifEtuSurCreneau[0]) {
						 echo "Cet étudiant est déjà sur le créneau horaire";
					 }
				 //3.
				 elseif ($verifEtuSurCreneauEnt[0]!=null) {
					 echo "Un étudiant est déjà présent pour cette formation à cette heure";
				 }
				 else{
					 $dao->ajouterEtuCreneau($_POST['numero_creneau'],$_POST['idFormationEntrepriseCre'],$_POST['idEtudiantCre']);
				 }
			 }
	 ?>
		 </div>
		 <?php
		 //////////////////////////////////////ATTTENTION METTRE EN PLACE SYSTEME DATE POUR AFFICHER///////////////////////////////////////On génére l'emploi du temps
		 $dao = new Dao();
		 //$dao->session();
		 $tabConfig = $dao -> getConfiguration();
		 //////PROJET
		 $tabfor = $dao -> getFormationsResponsable($_SESSION['idUser']);
		 $tabEnt = $dao -> getEntreprisesParFormation($tabfor[0][1]);
		 //BUG !!
		 /*
 		for($i=0;$i<count($tabEnt);$i++){
			for($j=0;$j<count($tabEnt[$i]);$j++){
				echo "*".$tabEnt[$i][$j]."*"."<br>";
			}
		}*/




		 //DEVRAIT FONCTIONNER SI PAS DE BUG AU DESSUS
		 /*for($i=0;$i<count($tabEnt);$i++){
			echo bla1;
			echo $tabEnt[$i]->getID()."<br>";
			echo bla2;
			echo $tabEnt[$i][0]->getNomEnt()."<br>";
			echo bla3;
		}*/
		 ///////


		 $nbCreneaux = $tabConfig["nbCreneauxAprem"] + $tabConfig["nbCreneauxMatin"];

		 if ($tabConfig["nbCreneauxMatin"] == 0) {
			 $nbCreneaux --;
		 }

		 if($tabConfig["nbCreneauxMatin"] == 0) {
			 $pauseMidi = $tabConfig["nbCreneauxMatin"];
		 } elseif ($tabConfig["nbCreneauxMatin"] != 0) {
			 $pauseMidi = $tabConfig["nbCreneauxMatin"];
			 $pauseMidi += 1;
		 }

		 $heureCreneauPauseMatin = new DateTime($tabConfig['heureCreneauPauseMatin']);
		 $heureCreneauPause = new DateTime($tabConfig['heureCreneauPause']);
		 $numCreneauPauseAprem = -1;
		 $numCreneauPauseMatin = -1;
		 $dureeCreneau = $tabConfig["dureeCreneau"];

		 //Planning du point de vue des entreprises
		 ?>
		 <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
		 <script src="https://cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js"></script>
		 <script src="vue/js/selectionTab.js"></script>
		 
		 <script type="text/javascript">
		 
		 $(document).ready(function() {
			 var table = $('#tabPlanningEnt').dataTable({
				 "paging": false,
				 "bSort": false,
				 "info": false,
				 "autoWidth": true
			 });

			 var search = $("#tabPlanningEnt_filter label input");

			 var nom_formation = $("#formation_entreprise option:selected").text();

			 $("#formation_entreprise").change(function functionName() {

				 console.log("ok");
				 console.log($("#formation_entreprise option:selected").val());
				 search.val($("#formation_entreprise option:selected").val());
				 search.focus();
			 })

		 });
		 </script>

		 <div id="main">
		 <h1>Planning responsable</h1>
		 <div class="resptab" >
		 <table id="tabPlanningEnt">

		 <thead>
		 <tr>
			 <td colspan= 1> Entreprise </td>
			 
			 <td colspan= 1> Formation </td>
			 
			 <?php

			 $taillePlanningmatin = $tabConfig["nbCreneauxMatin"] + 1;
			 //echo'<td colspan= '.$taillePlanningmatin.'> Matin </td>';
			 //echo'<td colspan= 1> Pause midi </td>';
			 $taillePlanningaprem = $tabConfig["nbCreneauxAprem"] + 2;
			 echo'<td colspan= '.$taillePlanningaprem.'> Après-midi </td>';
			 ?>
		 </tr>

		 <?php
		 echo'<tr>';
		 echo'<td> </td>';
		 echo'<td> </td>';
		 $listeCreneaux = $dao->getListeCreneaux();

		 $heureCreneauApresPause = $heureCreneauPause;
		 $heureCreneauApresPause->add(new DateInterval('PT'.$dureeCreneau.'M'));
		 $heureCreneauApresPauseMatin = $heureCreneauPauseMatin;
		 $heureCreneauApresPauseMatin->add(new DateInterval('PT'.$dureeCreneau.'M'));

		 // Affichage des heures du matin
		 if($tabConfig["nbCreneauxMatin"] == 0){

			echo '<td id="cacheMatin"> </td>';
		 }else{

			 for ($i = 0; $i < $tabConfig["nbCreneauxMatin"]; $i++){
				 //On récupère le numéro de la pause du matin
				 if($listeCreneaux[$i] == $heureCreneauApresPauseMatin->format('H:i')){
					 $numCreneauPauseMatin = $i;
				 }
			 }


			 for ($i = 0; $i < $tabConfig["nbCreneauxMatin"]; $i++){
				 if ($numCreneauPauseMatin == $i) {
					 echo '<td>'."Pause Matin".'</td>';
				 }
				 echo '<td>'.$listeCreneaux[$i].'</td>';

			 }
		 }
		 // Pause du midi
		 echo '<td id="cacheMatin"> </td>';

		 if($tabConfig["nbCreneauxAprem"] == 0){
			 echo '<td> </td>';
		 }else{

			// Affichage des créneaux de l'après midi
				if ($tabConfig["nbCreneauxMatin"] == 0) {

					for ($i = $tabConfig["nbCreneauxMatin"]; $i <= $nbCreneaux; $i++){
			// On récupère le numéro de la pause de l'après-midi
						if($listeCreneaux[$i] == $heureCreneauApresPause->format('H:i')){
							$numCreneauPauseAprem = $i;
						}
					}

					for ($i = $tabConfig["nbCreneauxMatin"]; $i <= $nbCreneaux; $i++){
						$j=$numCreneauPauseAprem;
						if ($j==$i) {
							echo '<td>'."Pause".'</td>';
						}
							echo '<td>'.$listeCreneaux[$i].'</td>';

					}

				}
				else {

					for ($i = $tabConfig["nbCreneauxMatin"]; $i < $nbCreneaux; $i++){
					// On récupère le numéro de la pause de l'après-midi
						if($listeCreneaux[$i] == $heureCreneauApresPause->format('H:i')){
							$numCreneauPauseAprem = $i;
						}
					}
					for ($i = $tabConfig["nbCreneauxMatin"]; $i < $nbCreneaux; $i++){
						if ($numCreneauPauseAprem==$i) {
							echo '<td>'."PauseP".'</td>';
						}
						echo '<td>'.$listeCreneaux[$i].'</td>';
					}
				}
			}

		 if ($tabConfig["nbCreneauxMatin"] != 0) {
			 $numCreneauPauseAprem ++;
		 }
		 echo'</tr>
		 </thead>
		 <tbody id="planning">';

		 //PROJET
		 /*for($i=0;$i<count($tabEnt);$i++){
			 $tabEntreprise[$i]=$tabEnt[$i][0];
			 //echo $tabEntreprise[$i]."<br>";
		 }*/
		 //$tabEnt=$tabEntreprise;


			
			//$tabForm = $dao -> getFormationResponsa;
			/*for($i=0;$i<count($tabForm);$i++){
				echo $tabForm[$i][0]."<br>";
			}*/

		 ////
				
			 foreach ($tabfor as $form) {
				

			 echo '<tr id="entreprise">
			 <td><a href="index.php?profil='.$form['entPropose'].'&type=Ent">'.$dao->getNomEntreprise($form['entPropose']).'</a>
			 </td>
			 <td>'
			 .$form['typeFormation'].
			 '</td>';
			 ;


			 if ($tabConfig["nbCreneauxMatin"]==0) { 
				echo'<td id="pause_midi"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp</td>';
			 }

			 for($i = 0; $i < $nbCreneaux+1; $i++) {
				 if ($i == $pauseMidi) {
					 echo'<td id="pause_midi"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp</td>';
				 }
				 echo '<td class=colorMe>';
				// Si c'est la pause on affiche un indicateur de pause

				if (($i == $numCreneauPauseAprem) ||($i == $numCreneauPauseMatin)) {
					echo '-'.'<td class=colorMe>'.strtoupper($dao -> getNomEtudiant($dao -> getCreneau($i, $form['IDformation']))).'</td>';

				}

				// Si ce n'est pas la pause, on affiche l'étudiant affecté à ce créneau
				else {
						echo strtoupper ($dao -> getNomEtudiant($dao -> getCreneau($i, $form['IDformation'])));
				}
			 }
			 if ($tabConfig["nbCreneauxAprem"]==0){
				 echo'<td id="pause_midi"> &nbsp;&nbsp</td>';
				 echo'<td id="pause_midi"> &nbsp;&nbsp</td>';
			 }
			 echo '</td> ';
		 }
			 echo '</tr>';
		
		 ?>
		 </tbody>
		 </table>
		 </diV>

			 </p>
		 </div>
		 <p> <br/> </p>

			 <?php

			 //Planning du point de vue des Etudiants
			 echo $util->generePied();


		}

	/**
	 * Fonction permettant l'affichage de la page planning de l'administrateur.
	 */
	public function afficherPlanningAdmin(){

			$dao = new Dao();
			$tableauConfig = $dao->getConfiguration();
			$dateEvenementTmp = explode("-",$tableauConfig['dateEvenement']);
			$dateEvenement = implode("/",array_reverse($dateEvenementTmp));

			$util = new UtilitairePageHtml();
			echo $util->genereBandeauApresConnexion();
		?>


		<div id="main">
			<p id="bonjourAdmin">
				<br/>Bienvenue sur votre espace administrateur créé à l'occasion des rencontres alternances du <?=$dateEvenement?>.
				<br> <br/ > <a title="Ajouter un étudiant" href="#" onclick="afficherAjoutEtu();return false;"> Ajouter un étudiant au planning</a>
			<br/ > <a title="Supprimer un étudiant" href="#" onclick="afficherSupprEtu();return false;">Supprimer un étudiant du planning</a> </p>

			<form id="formSupprEtu" action="index.php" method="post" style="display:none">
				<label>Heure :</label>
					<select name='numero_creneau'>
						<option value="null"> ----- </option>
						<?php
						$dao = new Dao();
						for ($numCreneau=0; $numCreneau < 25 ; $numCreneau++) {
							$heure = $dao->getHeure($numCreneau);
								if ($heure != null) {
								echo "<option value=".$numCreneau.">".$heure."</option>";
							}
						}

						?>
					</select>

					<label>Etudiant :</label>
					<select name="idEtudiantCre">
						<option value="null"> ----- </option>
						<?php
							$nomEtu = $dao->getTousEtudiants();
							foreach ($nomEtu as $res) {
									echo "<option value=".$res['IDEtu'].">".$res['nomEtu']."</option>";
							}
						 ?>
					</select>
					<br>
					<input type="submit" name="supprimerEtudiantCr" value="Supprimer l'étudiant" onclick="return checkDelete()">
				</form>

			<form id="formAjoutEtu" action="index.php" method="post" style="display:none">
				<label>Heure :</label>
					<select name='numero_creneau'>
						<option value="null"> ----- </option>
						<?php
						$dao = new Dao();
						for ($numCreneau=0; $numCreneau < 25 ; $numCreneau++) {
							$heure = $dao->getHeure($numCreneau);
								if ($heure != null) {
								echo "<option value=".$numCreneau.">".$heure."</option>";
							}
						}
						?>
					</select>

					<label>Etudiant :</label>
					<select name="idEtudiantCre" id="nom_etu">
						<option value="null"> ----- </option>
						<?php
							$nomEtu = $dao->getTousEtudiants();
							foreach ($nomEtu as $res) {
									echo "<option value=".$res['IDEtu'].">".$res['nomEtu']."</option>";
							}
						 ?>
					</select>

						 <label>Entreprise :</label>
	 					<select name="idEntrepriseCre">
	 						<option value="null"> ----- </option>
	 						<?php
	 							$nomEnt = $dao->getTousEntreprises();
	 							foreach ($nomEnt as $res) {
	 									echo "<option value=".$res['IDEnt'].">".$res['nomEnt']."</option>";
	 							}
	 						 ?>
	 					</select>

	 				<label>Formation :</label>
	 				<select id="formation_entreprise" name="idFormationEntrepriseCre" onchange="maj_etudiant(this)">
	 						<?php
								$i = 0;
	 							$nomForm = $dao->getTousFormations();
	 							foreach ($nomForm as $res) {
									if($i == 0){
										echo "<option id=".$i." value=\"\"> ----- </option>";
									}
	 									echo "<option id=".$i." value=".$res['typeFormation'].">".$res['typeFormation']."</option>";
										$i++;
	 							}
	 						 ?>
	 				</select>
					<input type="submit" name="ajouterEtudiantCr" value="Ajouter l'étudiant">
			</form>

				<script src="vue/js/AjoutEtudiantPlanning.js"></script>
				<script>
				function checkDelete() {
					if (confirm('Êtes-vous sûr(e) de vouloir supprimer l\'étudiant ?')) {
							return true;
							<?php $dao->supprimerEtuCreneau($_POST['numero_creneau'],$_POST['idEtudiantCre']); ?>
					} else {
							return false;
					}
				}
				</script>

			<script>
			function afficherSupprEtu(){
				document.getElementById("formSupprEtu").style = "display";
				document.getElementById("formAjoutEtu").style = "display:none";
			}
			function afficherAjoutEtu(){
				document.getElementById("formAjoutEtu").style = "display";
				document.getElementById("formSupprEtu").style = "display:none";

			}
			</script>

			<script type="text/javascript">
			function maj_etudiant(oSelect){
				var formation_selectionne = oSelect.options[oSelect.selectedIndex].value;
				console.log(formation_selectionne);
				$.ajax({
					dataType : 'json',
					url: 'index.php',
					type: 'GET',
					data: {
						formSelectionee : formation_selectionne
					},

					success : function(reponse){
							readData(reponse);
						},
					error: function(reponse){
						console.log(reponse,"error");
					}
				});
			}

						function readData(oData){
				var oSelect = document.getElementById("nom_etu");
				var oOpts = oSelect.getElementsByTagName("option");

				var oOption, oInner;

				oSelect.innerHTML = "";
				while(oOpts[1]){
					oSelect.removeChild(oOpts[1]);
				}
				$.each(oData, function(i, item){

					oOption = document.createElement("option");
					oInner = document.createTextNode(item[2]);
					oOption.value = item[0];
					oOption.appendChild(oInner);
					oSelect.appendChild(oOption);
					console.log(item[0]);
				})
			}

			</script>


			<?php
			if(isset($_POST['ajouterEtudiantCr'])) {
				/*
				1. Vérifier que l'entreprise recherche bien la formation de l'étudiant
				2. Vérifier que l'étudiant ne soit pas déjà présent sur le créneau
				3. Vérifier qu'il n'y a pas déjà un étudiant sur le créneau de la formation
				*/
				$_POST['idFormationEntrepriseCre'] = $dao->getIDFormation($_POST['idFormationEntrepriseCre'], $_POST['idEntrepriseCre']);
				$verifEtuSurCreneau = $dao->verifEtuSurCreneau($_POST['idEtudiantCre'], $_POST['numero_creneau']);
				$verifEtuSurCreneauEnt = $dao->verifEtuSurCreneauEntreprise($_POST['idFormationEntrepriseCre'], $_POST['numero_creneau']);

				//1.
					if($_POST['idFormationEntrepriseCre']==null){
						echo "L'entreprise ne recherche pas d'étudiant dans cette formation";
					}
				//2.
					elseif ($_POST['idEtudiantCre'] == $verifEtuSurCreneau[0]) {
						echo "Cet étudiant est déjà sur le créneau horaire";
					}
				//3.
				elseif ($verifEtuSurCreneauEnt[0]!=null) {
					echo "Un étudiant est déjà présent pour cette formation à cette heure";
				}
				else{
					$dao->ajouterEtuCreneau($_POST['numero_creneau'],$_POST['idFormationEntrepriseCre'],$_POST['idEtudiantCre']);
				}
			}
 ?>
		</div>
		<?php
		//////////////////////////////////////ATTTENTION METTRE EN PLACE SYSTEME DATE POUR AFFICHER///////////////////////////////////////On génére l'emploi du temps
		$dao = new Dao();
		$tabConfig = $dao -> getConfiguration();
		$tabEnt = $dao -> getAllEntreprises();

		$nbCreneaux = $tabConfig["nbCreneauxAprem"] + $tabConfig["nbCreneauxMatin"];

		if ($tabConfig["nbCreneauxMatin"] == 0) {
			$nbCreneaux --;
		}

		if($tabConfig["nbCreneauxMatin"] == 0) {
			$pauseMidi = $tabConfig["nbCreneauxMatin"];
		} elseif ($tabConfig["nbCreneauxMatin"] != 0) {
			$pauseMidi = $tabConfig["nbCreneauxMatin"];
			$pauseMidi += 1;
		}

		$heureCreneauPauseMatin = new DateTime($tabConfig['heureCreneauPauseMatin']);
		$heureCreneauPause = new DateTime($tabConfig['heureCreneauPause']);
		$numCreneauPauseAprem = -1;
		$numCreneauPauseMatin = -1;
		$dureeCreneau = $tabConfig["dureeCreneau"];

		//Planning du point de vue des entreprises
		?>
		<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
		<script src="https://cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js"></script>
		<script src="vue/js/selectionTab.js"></script>
		<script type="text/javascript">
		$(document).ready(function() {
			var table = $('#tabPlanningEnt').dataTable({
				"paging": false,
				"bSort": false,
				"info": false
			});

			var search = $("#tabPlanningEnt_filter label input");

			var nom_formation = $("#formation_entreprise option:selected").text();

			$("#formation_entreprise").change(function functionName() {

				console.log("ok");
				console.log($("#formation_entreprise option:selected").val());
				search.val($("#formation_entreprise option:selected").val());
				search.focus();
			})

		});
		</script>

		<div id="main">
		<h1>Planning administrateur</h1>
		<div class="resptab" >
		<table id="tabPlanningEnt">

		<thead>
		<tr>
			<td colspan= 1> Entreprise </td>
			<td colspan= 1> Formation </td>
			<?php

			$taillePlanningmatin = $tabConfig["nbCreneauxMatin"] + 1;
			//echo'<td colspan= '.$taillePlanningmatin.'> Matin </td>';
			//echo'<td colspan= 1> Pause midi </td>';
			$taillePlanningaprem = $tabConfig["nbCreneauxAprem"] + 1;
			echo'<td colspan= '.$taillePlanningaprem.'> Après-midi </td>';
			?>
		</tr>

		<?php
		echo'<tr>';
		echo'<td> </td>';
		echo'<td> </td>';
		echo'<td> </td>';
		$listeCreneaux = $dao->getListeCreneaux();

		$heureCreneauApresPause = $heureCreneauPause;
		$heureCreneauApresPause->add(new DateInterval('PT'.$dureeCreneau.'M'));
		$heureCreneauApresPauseMatin = $heureCreneauPauseMatin;
		$heureCreneauApresPauseMatin->add(new DateInterval('PT'.$dureeCreneau.'M'));

		// Affichage des heures du matin
		if($tabConfig["nbCreneauxMatin"] == 0){

			echo '<td></td>';
		}else{

			for ($i = 0; $i < $tabConfig["nbCreneauxMatin"]; $i++){
				//On récupère le numéro de la pause du matin
				if($listeCreneaux[$i] == $heureCreneauApresPauseMatin->format('H:i')){
					$numCreneauPauseMatin = $i;
				}
			}


			for ($i = 0; $i < $tabConfig["nbCreneauxMatin"]; $i++){
				if ($numCreneauPauseMatin == $i) {
					echo '<td>'."Pause Matin".'</td>';
				}
				echo '<td>'.$listeCreneaux[$i].'</td>';

			}
		}
		// Pause du midi
		echo '<td></td>';

		if($tabConfig["nbCreneauxAprem"] == 0){
			echo '<td></td>';
		}else{
			// Affichage des créneaux de l'après midi

                if ($tabConfig["nbCreneauxMatin"] == 0) {
                        for ($i = $tabConfig["nbCreneauxMatin"]; $i <= $nbCreneaux; $i++){
                        // On récupère le numéro de la pause de l'après-midi
                                 if($listeCreneaux[$i] == $heureCreneauApresPause->format('H:i')){
                                        $numCreneauPauseAprem = $i;
                                 }
                        }
						
                        for ($i = $tabConfig["nbCreneauxMatin"]; $i <= $nbCreneaux; $i++){
							$j=$numCreneauPauseAprem;
                              if ($j==$i) {
                                      echo '<td>'."Pause".'</td>';
                                }
								echo '<td>'.$listeCreneaux[$i].'</td>';
                     
                            
                        }
						
                }
                else {
                        for ($i = $tabConfig["nbCreneauxMatin"]; $i < $nbCreneaux; $i++){
                        // On récupère le numéro de la pause de l'après-midi
                                if($listeCreneaux[$i] == $heureCreneauApresPause->format('H:i')){
                                        $numCreneauPauseAprem = $i;
                                }
                }
                        for ($i = $tabConfig["nbCreneauxMatin"]; $i < $nbCreneaux; $i++){
                                if ($numCreneauPauseAprem==$i) {
                                        echo '<td>'."PauseP".'</td>';
                                }
                                echo '<td>'.$listeCreneaux[$i].'</td>';
                        }
                }
                }

		if ($tabConfig["nbCreneauxMatin"] != 0) {
			$numCreneauPauseAprem ++;
		}
		echo'</tr>
		</thead>
		<tbody id="planning">';
		foreach ($tabEnt as $ent) {
			$tabForm = $dao -> getFormationsEntreprise($ent -> getID());
		foreach ($tabForm as $form) {
			echo '<tr id="entreprise">
			<td><a href="index.php?profil='.$ent->getID().'&type=Ent">'.$ent->getNomEnt().'</a>
			</td>
			<td>'
			.$form['typeFormation'].
			'</td>';
			;
			if ($tabConfig["nbCreneauxMatin"]==0) {
				echo'<td id="pause_midi"> &nbsp;&nbsp;<td>';
			}
			for($i = 0; $i < $nbCreneaux+1; $i++) {
				if ($i == $pauseMidi) {
					echo'<td id="pause_midi"> &nbsp;&nbsp;</td>';
				}
				echo '<td class=colorMe>';
				// Si c'est la pause on affiche un indicateur de pause
				//echo $dao -> getNomEtudiant($dao -> getCreneau($i, $form['IDformation']));
				if (($i == $numCreneauPauseAprem) ||($i == $numCreneauPauseMatin)) {
					echo '-'.'<td class=colorMe>'.$dao -> getNomEtudiant($dao -> getCreneau($i, $form['IDformation'])).'</td>';
				}
				// Si ce n'est pas la pause, on affiche l'étudiant affecté à ce créneau
				else {
					echo $dao -> getNomEtudiant($dao -> getCreneau($i, $form['IDformation']));
				}
			}
			if ($tabConfig["nbCreneauxAprem"]==0){
				echo'<td id="pause_midi"> &nbsp;&nbsp;</td>';
				echo'<td id="pause_midi"> &nbsp;&nbsp;</td>';
			}
			echo '</td> ';
		}
			echo '</tr>';
		}
		?>
		</tbody>
		</table>
		</diV>

			</p>
		</div>
		<p> <br/> </p>

			<?php

	    //Planning du point de vue des Etudiants
			echo $util->generePied();

	   }

/**
 * Fonction permettant l'affichage de la page comptes de l'administrateur.
 */
public function afficherComptes() {
	$util = new UtilitairePageHtml();
	 	$dao = new Dao();
		echo $util->genereBandeauApresConnexion();
		$tabEtuTemp = $dao->getAllEtudiantsTemp();
		$tabEtu = $dao->getAllEtudiants();
		$tabEntTemp = $dao->getAllEntreprisesTemp();
		$tabEnt = $dao->getAllEntreprises();
	?>

		<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
		<script src="https://cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js"></script>
		<script type="text/javascript">
		$(document).ready(function() {
			$('#tabEtudiants').dataTable({
				"scrollY":        "500px",
				"scrollCollapse": true,
				"paging":         false,
				"info": false
			});
			$('#tabEntreprises').dataTable({
				"scrollY":"500px",
				"scrollCollapse": true,
				"paging": false,
				"info": false
			});
			// Récupérer les checkbox cachés pour les envoyer au serveur
			$('form').submit(function() {
				var dataTableEtu = $('#tabEtudiants').dataTable();
				var dataTableEnt = $('#tabEntreprises').dataTable();
				var rowsEtu = dataTableEtu.fnGetNodes(), inputsEtu = [];
				var rowsEnt = dataTableEnt.fnGetNodes(), inputsEnt = [];
				for (var i = 0, len = rowsEtu.length; i < len; i++) {
					var $fields = $(rowsEtu[i]).find('input[name]:hidden:checked');
					$fields.each(function(idx, el) {
						inputsEtu.push('<input type="hidden" name="'
						+ $(el).attr('name') + '" value="'
						+ $(el).val() + '">');
					});
				}
				$(this).append(inputsEtu.join(''));
				for (var i = 0, len = rowsEnt.length; i < len; i++) {
					var $fields = $(rowsEnt[i]).find('input[name]:hidden:checked');
					$fields.each(function(idx, el) {
						inputsEnt.push('<input type="hidden" name="'
						+ $(el).attr('name') + '" value="'
						+ $(el).val() + '">');
					});
				}
				$(this).append(inputsEnt.join(''));
			});
		});
	</script>
	<div id="main">
	<script type="text/javascript">
		function CocheTout(ref, name)
		{
			var form = ref;
			while (form.parentNode && form.nodeName.toLowerCase() != 'form')
			{
				form = form.parentNode;
			}
			var elements = form.getElementsByTagName('input');
			for (var i = 0; i < elements.length; i++)
			{
				if (elements[i].type == 'checkbox' && elements[i].name == name)
				{
					elements[i].checked = ref.checked;
				}
			}
		}
	</script>
	<br/>
	<h2>Etudiants</h2>
		<div class="resptab2">
			<form action="index.php?modele=-1" method="post">
		<table id="tabEtudiants">

		<thead>
			<tr>
				<td>
					<form>
						<input onclick="CocheTout(this, 'mails[]');" type="checkbox" name="oneCheckboxToSelectThemAll"></input>
					</form>
				</td>
				<td>
					<b>Nom</b>
				</td>
				<td>
					<b>Prénom</b>
				</td>
				<td>
					<b>Email</b>
				</td>
				<td>
					<b>Téléphone</b>
				</td>
				<td>
					<b>Formation</b>
				</td>
				<td>
					<b>Etat</b>
				</td>

				<td>
					<b>Choix</<b>
			</td>
			</tr>
		</thead>
		<tbody>

		<?php
			foreach ($tabEtuTemp as $etuTemp) {
				echo '<tr>
					<td>
					<input type="checkbox" name="mails[]" value='.$etuTemp->getMailEtu()."+".$etuTemp->getId()."+tmpEtu".'>
					</td>
					<td>
					<a href="index.php?profil='.$etuTemp->getID().'&type=tmpEtu">'.$etuTemp->getNomEtu().'</a>
					</td>
					<td>'
					.$etuTemp->getPrenomEtu().
					'</td>
					</td>
					<td>
					<a href="mailto:'.$etuTemp->getMailEtu().'">'.$etuTemp->getMailEtu().'</a>
					</td>
					<td>'
					.$etuTemp->getNumTelEtu().
					'</td>
					<td>'
					.$etuTemp->getFormationEtu().
					'</td>
					<td>
						<a href="index.php?validation=ok&id='.$etuTemp->getId().'&type=tmpEtu" onclick="return checkValidate()">Valider</a>
						</td>'.
						'<td>
						';
						if($etuTemp->getListeChoixEtu() == ""){
							echo 'Pas_faits';
						}else{
							echo 'Bien_faits';
						}
						echo '
						</td>
					</tr>';
			}
			foreach ($tabEtu as $etuTemp) {
				echo '<tr>
					<td>
					<input type="checkbox" name="mails[]" value='.$etuTemp->getMailEtu()."+".$etuTemp->getId()."+Etu".'>
					</td>
					<td>
					<a href="index.php?profil='.$etuTemp->getID().'&type=Etu">'.$etuTemp->getNomEtu().'</a>
					</td>
					<td>'
					.$etuTemp->getPrenomEtu().
					'</td>
					<td> <a href="mailto:'.$etuTemp->getMailEtu().'">'.$etuTemp->getMailEtu().'</a>
					</td>
					<td>'
					.$etuTemp->getNumtelEtu().
					'</td>
					<td>'
					.$etuTemp->getFormationEtu().
					'</td>
					<td>
						<a href="index.php?geler=ok&id='.$etuTemp->getId().'&type=Etu" onclick="return checkFreeze()">Geler</a>
					</td>'
					.'<td>';
					if($etuTemp->getListeChoixEtu() == ""){
						echo 'Pas_faits';
					}else{
						echo 'Bien_faits';
					}
					echo '
					</td>
				</tr>';
			}
		?>
	</tbody>
		</table>
		<input type ="submit" name="mail" value="Envoyer mail">
		<input type="submit" name="ajouterEtudiant" value="Ajouter">
		<input type="submit" name="supprimer" value="Supprimer" onclick="return checkDelete()">
	
	</form>
	</div>

		<br/><br/>
		<h2>Entreprises</h2>
		<div class="resptab2">
			<form action="index.php?modele=0" method="post">
		<table id="tabEntreprises">

		<thead>
			<tr>
				<td>
					<form>
						<input onclick="CocheTout(this, 'mails[]');" type="checkbox" name="oneCheckboxToSelectThemAll"></input>
					</form>
				</td>
				<td><b>
					Entreprise</b>
				</td>
				<td>
					<b>Nom</b>
				</td>
				<td>
					<b>Pr&eacute;nom</b>
				</td>
				<td>
					<b>Téléphone</b>
				</td>
				<td>
					<b>Email</b>
				</td>
				<td>
					<b>Etat</b>
				</td>

			</tr>

		</thead>
		<tbody>

		<?php
			foreach ($tabEntTemp as $entTemp) {
				echo '<tr>
					<td>
					<input type="checkbox" name="mails[]" value='.$entTemp->getMailEnt()."+".$entTemp->getId()."+tmpEnt".'>
					</td>
					<td>
					<a href="index.php?profil='.$entTemp->getID().'&type=tmpEnt">'.$entTemp->getNomEnt().'</a>
					</td>
					<td>'
					.$entTemp->getNomContact().
					'</td>
					<td>'
					.$entTemp->getPrenomContact().
					'</td>
					<td>'
					.$entTemp->getNumTelContact().
					'</td>
					<td>
					<a href="mailto:'.$entTemp->getMailEnt().'">'.$entTemp->getMailEnt().'</a>
					</td>
					<td>
						<a href="index.php?validation=ok&id='.$entTemp->getId().'&type=tmpEnt" onclick="return checkValidate()">Valider</a>
					</td>
					</tr>';
			}
			foreach ($tabEnt as $entTemp) {
				echo '<tr>
					<td>
					<input type="checkbox" name="mails[]" value='.$entTemp->getMailEnt()."+".$entTemp->getId()."+Ent".'>
					</td>
					<td>
					<a href="index.php?profil='.$entTemp->getID().'&type=Ent">'.$entTemp->getNomEnt().'</a>
					</td>
					<td>'
					.$entTemp->getNomContact().
					'</td>
					<td>'
					.$entTemp->getPrenomContact().
					'</td>
					<td>'
					.$entTemp->getNumTelContact().
					'</td>
					<td>
					<a href="mailto:'.$entTemp->getMailEnt().'">'.$entTemp->getMailEnt().'</a>
					</td>
					<td>
						<a href="index.php?geler=ok&id='.$entTemp->getId().'&type=Ent" onclick="return checkFreeze()">Geler</a>
					</td>
				</tr>';
			}
		?>
	</tbody>
		</table>
		<input type="submit" name="mail" value="Envoyer mail">
		<input type="submit" name="ajouter" value="Ajouter">
		<input type="submit" name="supprimer" value="Supprimer" onclick="return checkDelete()">
	</form>
	</div>
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
		 <script src="https://cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js"></script>
		 <script src="vue/js/selectionTab.js"></script>
		 <script type="text/javascript">
		 $(document).ready(function() {
			 var table = $('#tabPlanningEnt').dataTable({
				 "paging": false,
				 "bSort": false,
				 "info": false
			 });

			 var search = $("#tabPlanningEnt_filter label input");

			 var nom_formation = $("#formation_entreprise option:selected").text();

			 $("#formation_entreprise").change(function functionName() {

				 console.log("ok");
				 console.log($("#formation_entreprise option:selected").val());
				 search.val($("#formation_entreprise option:selected").val());
				 search.focus();
			 })

		 });
		 </script>
		 
		 		<br/><br/>
		<h2>Responsables</h2>
		<div class="resptab3">
			<form action="index.php?modele=0" method="post">
		<table id="tabResponsables">


		<thead>
			<tr class ="even" role="row">
				<td>
					<form>
						<input onclick="CocheTout(this, 'mails[]');" type="checkbox" name="oneCheckboxToSelectThemAll"></input>
					</form>
				</td>
				<td>
					<b>Nom</b>
				</td>
				<td>
					<b>Prénom</b>
				</td>
				<td>
					<b>Email</b>
				</td>
				<td>
					<b>Téléphone</b>
				</td>
				<td>
					<b>Formation(s)</b>
				</td>
				<td>
					<b>Etat</b>
				</td>
			</tr>
		</thead>
		<tbody>

		<?php
			
			$respsTemp = $dao->getAllRespTemp();
			$resps = $dao->getAllResp();
			foreach ($respsTemp as $tabRespTemp) {
				$idCrypt = $dao->my_simple_crypt( $tabRespTemp->getIdResp(), 'e' );
				$str_form = "";
				foreach ($dao->getFormationsResponsableBis($tabRespTemp->getIDresp()) as $form)
				{
					$str_form = str_replace("_"," ",$str_form.$form[0]).";";
				}
				echo '<tr>
					<td>
					<input type="checkbox" name="mails[]" value='.$tabRespTemp->getmailResp()."+".$tabRespTemp->getIDresp()."+tmpEtu".'>
					</td>
					<td>
					<a href="index.php?profil='.$idCrypt.'&type=responsable">'.strtoupper($tabRespTemp->getnomResp()).'</a>
					</td>
					<td>'
					.ucfirst(strtolower($tabRespTemp->getprenomResp())).
					'</td>
					</td>
					<td>
					<a href="mailto:'.$tabRespTemp->getmailResp().'">'.$tabRespTemp->getmailResp().'</a>
					</td>
					<td>'
					.$tabRespTemp->getnumtelResp().
					'</td>
					<td>'
					.$str_form.
					'</td>
					<td>
						<a href="index.php?validation=ok&id='.$idCrypt.'&type=tmpResp" onclick="return checkValidate()">Valider</a>
						</td>'.
						'<td>
						';
						echo '
						</td>
					</tr>';
			}
			
			foreach ($resps as $tabResp) {
				
				$str_form = "";
				foreach ($dao->getFormationsResponsableBis($tabResp->getIDresp()) as $form)
				{
					$str_form = str_replace("_"," ",$str_form.$form[0]).";";
				}
				$idCrypt = $dao->my_simple_crypt( $tabResp->getIdResp(), 'e' );
				echo '<tr>
					<td>
					<input type="checkbox" name="mailsResp[]" value='.$tabResp->getmailResp().'>
					</td>
					<td>
					<a href="index.php?profil='.$idCrypt.'&type=responsable">'.strtoupper($tabResp->getnomResp()).'</a>
					</td>
					<td>'
					.ucfirst(strtolower($tabResp->getprenomResp())).
					'</td>
					<td> <a href="mailto:'.$tabResp->getmailResp().'">'.$tabResp->getmailResp().'</a>
					</td>
					<td>'
					.$tabResp->getnumtelResp().
					'</td>
					<td>'
					.$str_form.
					'</td>
					<td>
						<a href="index.php?geler=ok&id='.$idCrypt.'&type=Resp" onclick="return checkFreeze()">Geler</a>
					</td>'
					.'<td>';
					echo '
					</td>
				</tr>';
			}
		?>
	</tbody>
		</table>
		<input type ="submit" name="mail" value="Envoyer mail">
		<input type="submit" name="ajouterResponsable" value="Ajouter">
		<input type="submit" name="supprimerResp" value="Supprimer" onclick="return checkDelete()">
	</form>
	</div>
	
	
		<script>
		function checkDelete() {
			if (confirm('Êtes-vous sûr(e) de vouloir supprimer les comptes sélectionnés ? Cette action ne peut pas être annulée !')) {
   				return confirm('Veuillez confirmer une seconde fois la suppression irréversible de ces comptes.');
			} else {
			    return false;
			}
		}
		function checkFreeze() {
			return confirm('Êtes-vous sûr(e) de vouloir geler ce compte ?');
		}
		function checkValidate() {
			return confirm('Êtes-vous sûr(e) de vouloir valider ce compte ?');
		}
		</script>

	</div>
		<?php
		echo $util->generePied();
		?>
	</div>
	</html>

	<?php
	}
	
//PROJET



	public function afficherComptesResponsable() {
		$util = new UtilitairePageHtml();
			 $dao = new Dao();
	
			echo $util->genereBandeauApresConnexion();
			$tabEtuTemp = $dao->getAllEtudiantsTempResponsable($_SESSION['idUser']);
			$tabEtu = $dao->getEtudiantsResponsable($_SESSION['idUser']);
			$tabEnt = $dao->getEntreprisesParResp($_SESSION['idUser']);
		?>
	
			<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
			<script src="https://cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js"></script>
			<script type="text/javascript">
			$(document).ready(function() {
				$('#tabEtudiants').dataTable({
					"scrollY":        "500px",
					"scrollCollapse": true,
					"paging":         false,
					"info": false
				});
				$('#tabEntreprises').dataTable({
					"scrollY":"500px",
					"scrollCollapse": true,
					"paging": false,
					"info": false
				});
				// Récupérer les checkbox cachés pour les envoyer au serveur
				$('form').submit(function() {
					var dataTableEtu = $('#tabEtudiants').dataTable();
					var dataTableEnt = $('#tabEntreprises').dataTable();
					var rowsEtu = dataTableEtu.fnGetNodes(), inputsEtu = [];
					var rowsEnt = dataTableEnt.fnGetNodes(), inputsEnt = [];
					for (var i = 0, len = rowsEtu.length; i < len; i++) {
						var $fields = $(rowsEtu[i]).find('input[name]:hidden:checked');
						$fields.each(function(idx, el) {
							inputsEtu.push('<input type="hidden" name="'
							+ $(el).attr('name') + '" value="'
							+ $(el).val() + '">');
						});
					}
					$(this).append(inputsEtu.join(''));
					for (var i = 0, len = rowsEnt.length; i < len; i++) {
						var $fields = $(rowsEnt[i]).find('input[name]:hidden:checked');
						$fields.each(function(idx, el) {
							inputsEnt.push('<input type="hidden" name="'
							+ $(el).attr('name') + '" value="'
							+ $(el).val() + '">');
						});
					}
					$(this).append(inputsEnt.join(''));
				});
			});
		</script>
		<div id="main">
		<script type="text/javascript">
			function CocheTout(ref, name)
			{
				var form = ref;
				while (form.parentNode && form.nodeName.toLowerCase() != 'form')
				{
					form = form.parentNode;
				}
				var elements = form.getElementsByTagName('input');
				for (var i = 0; i < elements.length; i++)
				{
					if (elements[i].type == 'checkbox' && elements[i].name == name)
					{
						elements[i].checked = ref.checked;
					}
				}
			}
		</script>
		<br/>
		<h2>Etudiants</h2>
			<div class="resptab2">
				<form action="index.php?modele=-1" method="post">
			<table id="tabEtudiants">
	
			<thead>
				<tr>
					<td>
						<form>
							<input onclick="CocheTout(this, 'mails[]');" type="checkbox" name="oneCheckboxToSelectThemAll"></input>
						</form>
					</td>
					<td>
						<b>Nom</b>
					</td>
					<td>
						<b>Prénom</b>
					</td>
					<td>
						<b>Email</b>
					</td>
					<td>
						<b>Téléphone</b>
					</td>
					<td>
						<b>Formation</b>
					</td>
					<td>
						<b>Etat</b>
					</td>
	
					<td>
						<b>Choix</<b>
				</td>
				</tr>
			</thead>
			<tbody>
	
			<?php
				foreach ($tabEtuTemp as $etuTemp) {
					echo '<tr>
						<td>
						<input type="checkbox" name="mails[]" value='.$etuTemp->getMailEtu()."+".$etuTemp->getId()."+tmpEtu".'>
						</td>
						<td>
						<a href="index.php?profil='.$etuTemp->getID().'&type=tmpEtu">'.$etuTemp->getNomEtu().'</a>
						</td>
						<td>'
						.$etuTemp->getPrenomEtu().
						'</td>
						</td>
						<td>
						<a href="mailto:'.$etuTemp->getMailEtu().'">'.$etuTemp->getMailEtu().'</a>
						</td>
						<td>'
						.$etuTemp->getNumTelEtu().
						'</td>
						<td>'
						.$etuTemp->getFormationEtu().
						'</td>
						<td>
							<a href="index.php?validation=ok&id='.$etuTemp->getId().'&type=tmpEtu" onclick="return checkValidate()">Valider</a>
							</td>'.
							'<td>
							';
							if($etuTemp->getListeChoixEtu() == ""){
								echo 'Pas_faits';
							}else{
								echo 'Bien_faits';
							}
							echo '
							</td>
						</tr>';
				}
				foreach ($tabEtu as $etuTemp) {
					echo '<tr>
						<td>
						<input type="checkbox" name="mails[]" value='.$etuTemp->getMailEtu()."+".$etuTemp->getId()."+Etu".'>
						</td>
						<td>
						<a href="index.php?profil='.$etuTemp->getID().'&type=Etu">'.strtoupper($etuTemp->getNomEtu()).'</a>
						</td>
						<td>'
						.ucfirst(strtolower($etuTemp->getPrenomEtu())).
						'</td>
						<td> <a href="mailto:'.$etuTemp->getMailEtu().'">'.$etuTemp->getMailEtu().'</a>
						</td>
						<td>'
						.$etuTemp->getNumtelEtu().
						'</td>
						<td>'
						.$etuTemp->getFormationEtu().
						'</td>
						<td>
							<a href="index.php?geler=ok&id='.$etuTemp->getId().'&type=Etu" onclick="return checkFreeze()">Geler</a>
						</td>'
						.'<td>';
						if($etuTemp->getListeChoixEtu() == ""){
							echo 'Pas_faits';
						}else{
							echo 'Bien_faits';
						}
						echo '
						</td>
					</tr>';
				}
			?>
		</tbody>
			</table>
			<input type ="submit" name="mail" value="Envoyer mail">
			<input type="submit" name="supprimer" value="Supprimer" onclick="return checkDelete()">
		</form>
		</div>
	
			<br/><br/>
			<h2>Entreprises</h2>
			<div class="resptab2">
				<form action="index.php?modele=0" method="post">
			<table id="tabEntreprises">
	
			<thead>
				<tr>
					<td>
						<form>
							<input onclick="CocheTout(this, 'mails[]');" type="checkbox" name="oneCheckboxToSelectThemAll"></input>
						</form>
					</td>
					<td><b>
						Entreprise</b>
					</td>
					<td>
						<b>Nom</b>
					</td>
					<td>
						<b>Pr&eacute;nom</b>
					</td>
					<td>
						<b>Téléphone</b>
					</td>
					<td>
						<b>Email</b>
					</td>
					<td>
						<b>Etat</b>
					</td>
	
				</tr>
	
			</thead>
			<tbody>
	
			<?php
				foreach ($tabEnt as $entTemp) {
					echo '<tr>
						<td>
						<input type="checkbox" name="mails[]" value='.$entTemp->getMailEnt()."+".$entTemp->getId()."+Ent".'>
						</td>
						<td>
						<a href="index.php?profil='.$entTemp->getID().'&type=Ent">'.$entTemp->getNomEnt().'</a>
						</td>
						<td>'
						.strtoupper($entTemp->getNomContact()).
						'</td>
						<td>'
						.ucfirst(strtolower($entTemp->getPrenomContact())).
						'</td>
						<td>'
						.$entTemp->getNumTelContact().
						'</td>
						<td>
						<a href="mailto:'.$entTemp->getMailEnt().'">'.$entTemp->getMailEnt().'</a>
						</td>
						<td>
							<a>Geler</a>
						</td>
					</tr>';
				}
			?>
		</tbody>
			</table>
			<input type="submit" name="mail" value="Envoyer mail">
			<input type="submit" name="supprimer" value="Supprimer" onclick="return checkDelete()">
		</form>
		</div>
	
			<script>
			function checkDelete() {
				if (confirm('Êtes-vous sûr(e) de vouloir supprimer les comptes sélectionnés ? Cette action ne peut pas être annulée !')) {
					   return confirm('Veuillez confirmer une seconde fois la suppression irréversible de ces comptes.');
				} else {
					return false;
				}
			}
			function checkFreeze() {
				return confirm('Êtes-vous sûr(e) de vouloir geler ce compte ?');
			}
			function checkValidate() {
				return confirm('Êtes-vous sûr(e) de vouloir valider ce compte ?');
			}
			</script>
	
		</div>
			<?php
			echo $util->generePied();
			?>
		</body>
		</html>
	
		<?php
		}



	//
/**
 * Fonction permettant l'affichage de la page de configuration de l'évènement.
 */
 public function afficherConfig() {
 $util = new UtilitairePageHtml();
 echo $util->genereBandeauApresConnexion();
 $dao = new Dao();
	 $heuresCreneaux = $dao->getListeCreneaux();
 $tabConfig = $dao->getConfiguration();
 $heureDebutMatin = $tabConfig['heureDebutMatin'];
 $heureDebutAprem = $tabConfig['heureDebutAprem'];
 $nbCreneauxMatin = $tabConfig['nbCreneauxMatin'];
 $nbCreneauxAprem = $tabConfig['nbCreneauxAprem'];
 $dureeCreneau = $tabConfig['dureeCreneau'];
 $heureCreneauPause = (new DateTime($tabConfig['heureCreneauPause']))->format("H:i");
 $heureCreneauPauseMatin = (new DateTime($tabConfig['heureCreneauPauseMatin']))->format("H:i");

 $dateDebutInscriptionEtu = $tabConfig['dateDebutInscriptionEtu'];
 $dateDebutInscriptionEnt = $tabConfig['dateDebutInscriptionEnt'];
 $dateFinInscription = $tabConfig['dateFinInscription'];
 $dateFinInscriptionEnt = $tabConfig['dateFinInscriptionEnt'];
 $dateDebutVuePlanning = $tabConfig['dateDebutVuePlanning'];
 $dateEvenement = $tabConfig['dateEvenement'];
 $siteEvenement = $tabConfig['siteEvenement'];
 $adresseIUT = $tabConfig['adresseIUT'];
 $mailAdministrateur = $tabConfig['mailAdministrateur'];
 $telAdministrateur = $tabConfig['telAdministrateur'];
 $nomAdministrateur = $tabConfig['nomAdministrateur'];
?>
<!DOCTYPE html>
<html>
<head>
 <link rel="stylesheet" type="text/css" href="vue/css/general.css">

 <script type="text/javascript">

 	function readData() {
 		var oSelect1 = document.getElementById("heureCreneauPauseApresmidi");
 		var oOpts1 = oSelect1.getElementsByTagName("option");
 		var oSelect2 = document.getElementById("HeurePauseMatin");
 		var oOpts2 = oSelect2.getElementsByTagName("option");
 		var duree_de_creneau = document.getElementById("duree_crenau").options[document.getElementById("duree_crenau").selectedIndex].value;
 		var oOption1, oInner1, oOption2, oInner2;

 		oSelect1.innerHTML= "";
 		while(oOpts1[1]){
 			oSelect1.removeChild(oOpts1[1]); //je supprime les éléments de ma liste
 		}

 		var longueur = <?php echo "$nbCreneauxAprem"; ?>;
 		var creneau_min = <?php $tmp_min = explode(":", $heureDebutAprem)[1];
 		 																echo "$tmp_min"?>;
 		var creneau_heure = <?php $tmp_heure = explode(":", $heureDebutAprem)[0];
 																			echo "$tmp_heure"; ?>;
 		for (var i = 0; i < longueur; i++ ){
 			creneau_min = parseInt(creneau_min);
 			duree_de_creneau = parseInt(duree_de_creneau);
 			if (creneau_min<10 && creneau_heure < 10){
 				var tmp = "0"+creneau_heure+":"+"0"+creneau_min;
 			}
 			else if (creneau_min<10){
 				var tmp = creneau_heure+":"+"0"+creneau_min;
 			}
 			else if (creneau_heure<10){
 				var tmp = "0"+creneau_heure+":"+creneau_min;
 			}
 			else {
 				var tmp = creneau_heure+":"+creneau_min; //je concatène à un format HH:MM
 			}
 			oOption1 = document.createElement("option"); //je crée ici mon options
 			oInner1 = document.createTextNode(tmp.toString()); //je crée le text qui ira dans mon option
 			oOption1.appendChild(oInner1);
 			oSelect1.appendChild(oOption1);
 			if(creneau_min+duree_de_creneau >= 60){
 				creneau_heure++;
 			}
 			creneau_min=(creneau_min+duree_de_creneau)%60;
 		}

 		oSelect2.innerHTML= "";
 		while(oOpts2[1]){
 			oSelect2.removeChild(oOpts2[1]); //je supprime les éléments de ma liste
 		}
 		var longueur = <?php echo "$nbCreneauxAprem"; ?>;
 		creneau_min = <?php $tmp_min = explode(":", $heureDebutMatin)[1]; echo "$tmp_min"?>;
 		creneau_heure = <?php $tmp_heure = explode(":", $heureDebutMatin)[0]; echo "$tmp_heure"; ?>;
 		for (var i = 0; i < longueur; i++ ){
 			creneau_min = parseInt(creneau_min);
 			duree_de_creneau = parseInt(duree_de_creneau);
 			if (creneau_min<10 && creneau_heure < 10){
 				var tmp = "0"+creneau_heure+":"+"0"+creneau_min;
 			}
 			else if (creneau_min<10){
 				var tmp = creneau_heure+":"+"0"+creneau_min;
 			}
 			else if (creneau_heure<10){
 				var tmp = "0"+creneau_heure+":"+creneau_min;
 			}
 			else {
 				var tmp = creneau_heure+":"+creneau_min; //je concatène à un format HH:MM
 			}
 			oOption2 = document.createElement("option"); //je crée ici mon options
 			oInner2 = document.createTextNode(tmp.toString()); //je crée le text qui ira dans mon option
 			oOption2.appendChild(oInner2);
 			oSelect2.appendChild(oOption2);
 			if(creneau_min+duree_de_creneau >= 60){
 				creneau_heure++;
 			}
 			creneau_min=(creneau_min+duree_de_creneau)%60;
 		}
 	}
  </script>

 <title></title>
 <meta charset="UTF-8">
</head>
<body>
<div id="main">
 <br/><span class="categorie_profil">Configuration actuelle :</span>
 <br/><br/>
 <?php
	 echo'
	 Les emplois du temps débuteront le matin à : '.$heureDebutMatin.'.
	 <br/><br/>Les emplois du temps débuteront l\'après-midi à : '.$heureDebutAprem.'.
	 <br/><br/>Il y aura '.$nbCreneauxMatin.' créneau(x) le matin et '.$nbCreneauxAprem.' l\'après-midi.
	 <br/><br/>Chaque créneau dure '.$dureeCreneau.' minutes.
	 <br/><br/>La pause durant l\'après-midi a lieu à '.$heureCreneauPause.'.
				 <br/><br/>La pause du matin a lieu à '.$heureCreneauPauseMatin.'.

	 <br/><br/>Les inscriptions entreprise débutent le '.$dateDebutInscriptionEnt.' et se terminent le '.$dateFinInscriptionEnt.'.
	 <br/><br/>Les inscriptions étudiant débutent le '.$dateDebutInscriptionEtu.'.
	 <br/><br/>Les inscriptions se terminent le '.$dateFinInscription.'.
	 <br/><br/>Les plannings seront visibles à partir du '.$dateDebutVuePlanning.'.
	 <br/><br/>L\'évènement aura lieu le '.$dateEvenement.' au site '.$siteEvenement.' ('.$adresseIUT.').
	 <br/><br/>Le numéro de téléphone affiché en pied de page est le '.$telAdministrateur.'.
	 <br/><br/>L\'adresse mail utilisée pour envoyer les mails depuis le site est : '.$mailAdministrateur.'.
	 <br/><br/>Le nom utilisé comme expéditeur des mails est : '.$nomAdministrateur.'.
	 ';
 ?>

 <br/><br/><span class="categorie_profil">Nouvelle configuration :</span>
 <form action="index.php" method="POST">
	 <br/>
	 <label>Début de la matinée (format hh:mm) : </label><input type="text" name="heureDebutMatin"/>
	 <br/><br/>
	 <label>Nombre de créneaux dans la matinée : </label><input type="text" name="nbCreneauxMatin"/>
	 <br/><br/>
	 <label>Début de l'après-midi (format hh:mm) : </label><input type="text" name="heureDebutAprem"/>
	 <br/><br/>
	 <label>Nombre de créneaux dans l'après-midi : </label><input type="text" name="nbCreneauxAprem"/>
	 <br/><br/>
	 <label>Durée en minutes d'un créneau : </label>
		 <select name = "dureeCreneau" id="duree_crenau" onchange="readData()">
			<option value = ""><?="---"?></option>
			<?php
			for ($i=5; $i < 35 ; $i+=5) {
				 ?>
					<option value = <?php echo "$i"?>> <?=$i?></option>
					<?php
			}
				 ?>
	   </select>

	 <br/><br/>
	 <label>Heure de la pause de l'après-midi :
	 <select name = "heureCreneauPause" id="heureCreneauPauseApresmidi">
		 <option value = ""><?=$heureCreneauPause?></option>
		 <?php
		 foreach($heuresCreneaux as $heure){
			 if($heure > $heureDebutAprem ) {
			 ?>
			 <option value = "<?=$heure?>"><?=$heure?></option>
			 <?php
			 }
		 }
			?>
	 </select>
	 <br/><br/>
	 <label>Heure de la pause du matin :
	 <select name = "heureCreneauPauseMatin" id="HeurePauseMatin">
		 <option value = ""><?=$heureCreneauPauseMatin?></option>
		 <?php
		 foreach($heuresCreneaux as $heure){
			 if($heure < $heureDebutAprem ) {
			 ?>
			 <option value = "<?=$heure?>"><?=$heure?></option>
			 <?php
			 }
		 }
			?>
	 </select>

	 <br/><br/>
	 <label>Début des inscriptions entreprises (format YYYY-MM-DD) : </label><input type="text" name="dateDebutInscriptionEnt"/>
	 <br/><br/>
	 <label>Deadline inscriptions entreprises (format YYYY-MM-DD) : </label><input type="text" name="dateFinInscriptionEnt"/>
	 <br/><br/>
	 <label>Début inscriptions étudiants (format YYYY-MM-DD) : </label><input type="text" name="dateDebutInscriptionEtu"/>
	 <br/><br/>
	 <label>Deadline inscriptions étudiants (format YYYY-MM-DD) : </label><input type="text" name="dateFinInscription"/>
	 <br/><br/>
	 <label>Date visibilité du planning (format YYYY-MM-DD): </label><input type="text" name="dateDebutVuePlanning"/>
	 <br/><br/>
	 <label>Date de l'évènement (format YYYY-MM-DD): </label><input type="text" name="dateEvenement"/>
	 <br/><br/>
	 <label>Lieu de l'évènement (site X) : </label><input type="text" name="siteEvenement"/>
	 <br/><br/>
	 <label>Adresse de l'IUT où se déroulera l'évènement : </label><input type="text" name="adresseIUT"/>
	 <br/><br/>
	 <label>Adresse email utilisée pour envoyer des mails depuis le site : </label><input type="text" name="mailAdministrateur"/>
	 <br/><br/>
	 <label>Nom utilisé pour signer les mails envoyés depuis le site (format : Prenom Nom) : </label><input type = "text" name = "nomAdministrateur"/>
	 <br/><br/>
	 <label>Le numéro de téléphone indiqué en pied de page (sans espace entre les numéros): </label><input id = "telAdmin" type="text" name="telAdministrateur"/>
	 <p id = "champErreurNumTel"></p>
	 <br/>
	 <input type="submit" name="changementConfig" value="Confirmer"/>
 </form>
</div>

<script type = "text/javascript">
 var numTel = document.getElementById('telAdmin');
 var champErreur = document.getElementById('champErreurNumTel');
 numTel.addEventListener('change',verifTelephone,false);
function verifTelephone() {
 if(numTel.value.length != 10 || !/^\d+$/.test(numTel.value)) {
	 numTel.style.borderColor = "red";
	 champErreur.innerHTML = "<span style=\"color:red\">Format invalide (le numéro ne peut être composé que de 10 chiffres)</span>";
	 return true;
 } else {
	 numTel.style.borderColor = "black";
	 champErreur.innerHTML = "";
	 return false;
 }
}
</script>
 <?php
 echo $util->generePied();
 ?>


<?php
}
	/**
	 * Fonction permettant, selon le nombre de places restantes disponibles pour une formation pour une entreprise, de mettre l'option du select selon une couleur précise.
	 * @param  int     $idEntreprise      l'identifiant de l'entreprise
	 * @param  String  $formationEtudiant la formation de l'étudiant
	 */
	private function choixCouleurs($idEntreprise,$formationEtudiant){
			$dao = new Dao();
			$nbPlaces = $dao->getNbPlacesRestantes($idEntreprise,$formationEtudiant);
			echo ' class = "';
			if($nbPlaces == 2){
				echo 'placesVertes';
				echo '" title="De nombreuses places encore disponibles.';
			}
			elseif($nbPlaces == 1){
				echo 'placesOranges';
				echo '" title="Quelques places encore disponibles.';
			}
			elseif($nbPlaces == -1){
				echo "placesCompletes\" ";
				echo "disabled=\"disabled" ;
				echo '" title="Plus aucune place disponible.';
			}
			elseif($nbPlaces == 0){
				echo 'placesRouges';
				echo '" title="Dernières places disponibles.';
			}
			echo '" ';
		}
	/**
	 * Fonction permettant l'affichage de la page de choix des entreprises par les étudiants.
	 */
	public function afficherChoix(){
		$util = new UtilitairePageHtml();
		echo $util->genereBandeauApresConnexion();
		$dao = new Dao();
		$etudiantCourant = $dao->getEtu($_SESSION['idUser'])[0];
		// $listeEntreprises = $dao->getEntreprisesParFormation($etudiantCourant->getFormationEtu());
		$listeEntreprises = $dao->getEntreprisesParFormation($dao->getFormationEtudiant($_SESSION['idUser']));

	?>
	<!DOCTYPE html>
	<html>
	<head>
		<link rel="stylesheet" type="text/css" href="vue/css/general.css">
		<title></title>
		<meta charset="UTF-8">
	</head>
	<body>
	<div id="main">

		<?php
			if ($etudiantCourant->getListeChoixEtu() == "") {
				echo "<br/>Vous n'avez pas encore fait de choix.";
			}
			else {
				echo "<br/>";
				$choix = explode(",",$etudiantCourant->getListeChoixEtu());
				$compteur = 1;
				$newList = $etudiantCourant->getListeChoixEtu();
				foreach ($choix as $entreprise) {
					$truc = $dao->getEnt(intval($entreprise));
					if (isset($truc[0])) {
						$objEnt = $truc[0];
						echo "Choix ".$compteur." : ";
						echo '<a href="index.php?profil='.$objEnt->getId().'&type=Ent">'.$objEnt->getNomEnt().'</a><br/><br/>';
						$compteur = $compteur + 1;
					}
					else {
						echo "Votre choix ".$compteur." n'existe plus. Il a été retiré de votre liste de choix.<br/><br/>";
						$compteur = $compteur + 1;
						if (strpos($newList, $entreprise.',') != false) {
							$newList = str_replace($entreprise.',', "", $newList);
						}
						else {
							$newList = str_replace($entreprise, "", $newList);
						}
						$dao->editChoixEtudiant($_SESSION['idUser'],$newList);
					}
				}
			}
		?>

		<br/>

<?php
$dateNow = new DateTime("now");
$tabConfig = $dao->getConfiguration();
$dateLimitEtu = new DateTime($tabConfig['dateFinInscription']);
						//Correction du décalage d'une journée
						$dateLimitEtu->setTime(23,59,59);
					if ($dateNow > $dateLimitEtu) {
           echo "<b>Vous ne pouvez plus refaire vos choix. ";
           echo "Choix des entreprises termin&eacute;s depuis le  ".date_format($dateLimitEtu, "d/m/Y")."</b><br>";
           }
           else {
           ?>

		Vous pouvez faire ou refaire vos choix. Le premier choix sera favorisé par rapport aux suivants. Les doublons ne permettront pas l'envoi du formulaire.<br><br>
            <?php
            echo "<b>Attention fin des choix des entreprises est pr&eacute;vue le ".date_format($dateLimitEtu, "d/m/Y")." au soir.</b><br>";
            ?>

		<br/><br/>
		<div id = "legendeChoixEtudiants">
		<h3>Légende des couleurs</h3>
		<p>
			Les entreprises dont le nom est :
		</p>
		<ul>
			<li class = "placesVertes">En vert n'ont eu pour le moment que très peu de demandes.</li>
			<li class = "placesOranges">En orange ont une minorité de créneaux libres.</li>
			<li class = "placesRouges">En rouge n'ont plus que quelques créneaux libres.</li>
			<li class = "placesCompletes">En gris ne peuvent plus être sélectionnées faute de places.</li>
		</ul>
		</div>

		<form action="index.php" method="POST" onsubmit="return verifier();">

			<select id="ent1" name="choix1" onchange="Changement1()">
				<option value="Faire un choix...">Faire un choix...</option>
				<?php
				$formationEtu = $etudiantCourant->getFormationEtu();
				foreach ($listeEntreprises as $entreprise) {
					$id_entreprise = $entreprise[0];
					$nom_entreprise = $dao->getNomEntreprise($id_entreprise);

					echo '<option value="'.$id_entreprise.'"';
					$this->choixCouleurs($id_entreprise,$formationEtu);
					echo '>'.$nom_entreprise.'</option>';
					//Mise en forme des options dans le code source
					echo "\n\t\t\t\t";
				}
				?>
			</select>

			<br/><br/>

			<select id="ent2" name="choix2" onchange="Changement2()" style="visibility:hidden">
				<option value="Faire un choix...">Faire un choix...</option>
				<?php
				$formationEtu = $etudiantCourant->getFormationEtu();
				foreach ($listeEntreprises as $entreprise) {
					$id_entreprise = $entreprise[0];
					$nom_entreprise = $dao->getNomEntreprise($id_entreprise);

					echo '<option value="'.$id_entreprise.'"';
					$this->choixCouleurs($id_entreprise,$formationEtu);
					echo '>'.$nom_entreprise.'</option>';
					//Mise en forme des options dans le code source
					echo "\n\t\t\t\t";
				}
				?>
			</select>


			<br/><br/>

			<select id="ent3" name="choix3" onchange="Changement3()" style="visibility:hidden;">
				<option value="Faire un choix...">Faire un choix...</option>
				<?php
				$formationEtu = $etudiantCourant->getFormationEtu();
				foreach ($listeEntreprises as $entreprise) {
					$id_entreprise = $entreprise[0];
					$nom_entreprise = $dao->getNomEntreprise($id_entreprise);

					echo '<option value="'.$id_entreprise.'"';
					$this->choixCouleurs($id_entreprise,$formationEtu);
					echo '>'.$nom_entreprise.'</option>';
					//Mise en forme des options dans le code source
					echo "\n\t\t\t\t";
				}
				?>
			</select>

			<br/><br/>

			<select id="ent4" name="choix4" onchange="Changement4()" style="visibility:hidden;">
				<option value="Faire un choix...">Faire un choix...</option>
				<?php
				$formationEtu = $etudiantCourant->getFormationEtu();
				foreach ($listeEntreprises as $entreprise) {
					$id_entreprise = $entreprise[0];
					$nom_entreprise = $dao->getNomEntreprise($id_entreprise);

					echo '<option value="'.$id_entreprise.'"';
					$this->choixCouleurs($id_entreprise,$formationEtu);
					echo '>'.$nom_entreprise.'</option>';
					//Mise en forme des options dans le code source
					echo "\n\t\t\t\t";
				}
				?>
			</select>


			<br/><br/>
			
			<select id="ent5" name="choix5" onchange="Changement5()" style="visibility:hidden;">
				<option value="Faire un choix...">Faire un choix...</option>
				<?php
				$formationEtu = $etudiantCourant->getFormationEtu();
				foreach ($listeEntreprises as $entreprise) {
					$id_entreprise = $entreprise[0];
					$nom_entreprise = $dao->getNomEntreprise($id_entreprise);

					echo '<option value="'.$id_entreprise.'"';
					$this->choixCouleurs($id_entreprise,$formationEtu);
					echo '>'.$nom_entreprise.'</option>';
					//Mise en forme des options dans le code source
					echo "\n\t\t\t\t";
				}
				?>
			</select>


			<br/><br/>

			<input type="submit" value="Valider les nouveaux changements" name="changementListeEtu"/>

		</form>

		<script>
function Changement1() {
			if (document.getElementById("ent1").value == "Faire un choix...") {
				document.getElementById("ent2").style.visibility = "hidden";
				document.getElementById("ent3").style.visibility = "hidden";
				document.getElementById("ent4").style.visibility = "hidden";
				document.getElementById("ent5").style.visibility = "hidden";
				document.getElementById("ent2").value = "Faire un choix...";
				document.getElementById("ent3").value = "Faire un choix...";
				document.getElementById("ent4").value = "Faire un choix...";
				document.getElementById("ent5").value = "Faire un choix...";
			}
			else {
				document.getElementById("ent2").style.visibility = "";
			}
		}
function Changement2() {
			if (document.getElementById("ent2").value == "Faire un choix...") {
				document.getElementById("ent3").style.visibility = "hidden";
				document.getElementById("ent4").style.visibility = "hidden";
				document.getElementById("ent5").style.visibility = "hidden";
				document.getElementById("ent3").value = "Faire un choix...";
				document.getElementById("ent4").value = "Faire un choix...";
				document.getElementById("ent5").value = "Faire un choix...";
			}
			else {
				document.getElementById("ent3").style.visibility = "";
			}
		}

		function Changement3() {
			if (document.getElementById("ent3").value == "Faire un choix...") {
				document.getElementById("ent4").style.visibility = "hidden";
				document.getElementById("ent5").style.visibility = "hidden";
				document.getElementById("ent4").value = "Faire un choix...";
				document.getElementById("ent5").value = "Faire un choix...";
			}
			else {
				document.getElementById("ent4").style.visibility = "";
			}
		}
		function Changement4() {
			if (document.getElementById("ent4").value == "Faire un choix...") {
				document.getElementById("ent5").style.visibility = "hidden";
				
			}
			else {
				document.getElementById("ent5").style.visibility = "";
			}
		}
	function Changement5() {
		}
		function verifier() {
			var value1 = document.getElementById("ent1").value;
			var value2 = document.getElementById("ent2").value;
			var value3 = document.getElementById("ent3").value;
			var value4 = document.getElementById("ent4").value;
			var value5 = document.getElementById("ent5").value;
			if (value1 == "Faire un choix...") {
				return true;
			}
			if (value2 == "Faire un choix..." && value1 != "Faire un choix...") {
				return true;
			}
			if (value3 == "Faire un choix..." && value2 != value1) {
				return true;
			}
			if (value4 == "Faire un choix..." && value3 != value2 && value3 != value1 && value2 != value1) {
				return true;
			}
			if (value5 == "Faire un choix..." && value4!=value1 && value4!=value2 && value4!=value3 && value3 != value2 && value3 != value1 && value2 != value1) {
				return true;
			}
			if (value1 != "Faire un choix" &&
				  value2 != "Faire un choix" &&
				  value3 != "Faire un choix" &&
				  value4 != "Faire un choix" &&
				  value5 != "Faire un choix" &&
				  value3 != value2 && value3 != value1 && value2 != value1 &&
					value4 != value1 && value4 != value2 && value4 != value3&&
					value5 != value4 && value5 != value3 && value5 != value2 && value5 != value1){
				return true;
			}
			return false;
		}
		</script>
          <?php } ?>
	</div>
		<?php
		echo $util->generePied();
		?>
	</body>
	</html>

	<?php
	}
	/**
	 * Fonction permettant l'affichage de la liste des entreprises recherchant la formation de l'étudiant.
	 */
	public function afficherEntreprises(){
		$dao = new Dao();
		$tabEntreprises = $dao->getEntreprisesParFormation($dao->getFormationEtudiant($_SESSION['idUser']));
		$util = new UtilitairePageHtml();
		echo $util->genereBandeauApresConnexion();
	?>
	<!DOCTYPE html>
	<html>
	<head>
		<link rel="stylesheet" type="text/css" href="vue/css/general.css">
		<title></title>
		<meta charset="UTF-8">
	</head>
	<body>
	<div id="main">
		<br/><br/><span style="categorie_profil">Liste des entreprises recherchant votre formation :</span><br/><br/>

		<?php
			echo "<br/>";
			if (sizeof($tabEntreprises) > 0 && !is_bool($tabEntreprises)) {
				foreach ($tabEntreprises as $entreprise) {
					$id_entreprise = $entreprise['entPropose'];
					$nom_entreprise = $dao->getNomEntreprise($id_entreprise);
					if($nom_entreprise != "-----------"){
						echo '<a href="index.php?profil='.$id_entreprise.'&type=Ent">'.$nom_entreprise.'</a><br/><br/>';
					}
				}
			}
			else {
				echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Actuellement, aucune entreprise ne propose de formation correspondante à la votre.';
			}
		?>


	</div>
		<?php
		echo $util->generePied();
		?>
	</body>
	</html>

	<?php
	}
	/**
	 * Fonction permettant l'affichage de la page de modification d'un compte étudiant.
	 */
	public function afficherCompteEtu(){
		$util = new UtilitairePageHtml();
		echo $util->genereBandeauApresConnexion();
	$dao = new Dao();
	$id = $_SESSION['idUser'];
	$tabprofil = $dao->getEtu($id);
	$profil = $tabprofil[0];
	$util = new UtilitairePageHtml();
	echo '
	<!DOCTYPE html>
	<html>
	<head>
		<link rel="stylesheet" type="text/css" href="vue/css/general.css">
		<title></title>
		<meta charset="UTF-8">
	</head>
	<br/><br/><br/>
	<span class="categorie_profil">Nom et prénom de l\'étudiant :</span> '.$profil->getPrenomEtu().' '.$profil->getNomEtu().'
	<br/><br/>
	<span class="categorie_profil">Email :</span> <a href="mailto:'.$profil->getMailEtu().'">'.$profil->getMailEtu().'</a>
	<br/><br/>
	<span class="categorie_profil">Téléphone :</span> '.$profil->getNumTelEtu().'
	<br/><br/>
	<span class="categorie_profil">Formation :</span> '.$profil->getFormationEtu().'
	';
		//<!-- Nom -->
		?>
		<script>
      //On surligne les cases non valides
      function surligne(champ, erreur) {
      if(erreur)
        champ.style.backgroundColor = "#fba";
      else
        champ.style.backgroundColor = "";
      }
      function verifString(champ, txt, longMax) {
        if(champ.value.length > longMax) {
          surligne(champ, true);
          document.getElementById(txt).innerHTML = longMax + " caractères maximum autorisé";
          return true;
        } else {
          surligne(champ, false);
          document.getElementById(txt).innerHTML = "";
          return false;
        }
      }
      function verifNombre(champ, txt, longMax) {
        if(champ.value.length > longMax || (!/^\d+$/.test(champ.value) && champ.value.length != 0)) {
          surligne(champ, true);
          document.getElementById(txt).innerHTML = "Un nombre de taille maximum " + longMax + " est attendu";
          return true;
        } else {
          surligne(champ, false);
          document.getElementById(txt).innerHTML = "";
          return false;
        }
      }
      function verifTelephone(champ, txt) {
        if(champ.value.length != 10 || !/^\d+$/.test(champ.value)) {
          surligne(champ, true);
          document.getElementById(txt).innerHTML = "Format invalide";
          return true;
        } else {
          surligne(champ, false);
          document.getElementById(txt).innerHTML = "";
          return false;
        }
      }
      function verifEmail(champ, txt){
        var reg = new RegExp("^[a-z0-9]+([_|\.|-]{1}[a-z0-9]+)*@[a-z0-9]+([_|\.|-]{1}[a-z0-9]+)*[\.]{1}[a-z]{2,6}$", "i");
        if(!reg.test(champ.value)) {
          surligne(champ, true);
          document.getElementById(txt).innerHTML = "L\'e-mail n\'est pas valide.";
          return true;
        } else {
          surligne(champ, false);
          document.getElementById(txt).innerHTML = "";
          return false;
        }
      }
      function verifMdp(txt){
        var passw = document.getElementById("passw");
				var passwBis = document.getElementById("passwBis");
        if (passw.value != passwBis.value) {
          surligne(passw, true);
          surligne(passwBis, true);
          document.getElementById(txt).innerHTML = "Les 2 valeurs sont différentes";
          return true;
        } else if (passw.value.length > 20 || passw.value.length < 5) {
          surligne(passw, true);
          surligne(passwBis, true);
          document.getElementById(txt).innerHTML = "Le mot de passe doit faire 5 à 20 caractères";
          return true;
        } else {
          surligne(passw, false);
          surligne(passwBis, false);
          document.getElementById(txt).innerHTML = "";
          return false;
        }
      }
      </script>


			<script type="text/javascript">
				EnableSubmit = function(val)
				{
				    var sbmt = document.getElementById("submit");
				    if (val.checked == true)
				    {
								document.getElementById("boutonUpload"+val.id).style = "display";
				        sbmt.disabled = false;
				    }
				    else
				    {
								document.getElementById("boutonUpload"+val.id).style = "display:none";
				        sbmt.disabled = true;
				    }
				}
			</script>
			<script>
			VerifSubmit = function()
				{
				html = html.replace(/</g, "&lt;").replace(/>/g, "&gt;");
				var passw = document.getElementById("passw");
				var passwBis = document.getElementById("passwBis");
					if (passw.value != passwBis.value) {
							alert("Les mots de passe ne coïncident pas.");
					        return false;
					}
					if (/^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i.test(document.getElementById("mail").value))
					  {
					    return true;
					  }
					  else {
					  	alert("L\'adresse email n'est pas correcte !");
					 	return false;
					  }
				}
			</script>
		<?php
		echo'
		<!--Les scripts pour vérifier chaque case-->
		<br><br/>
		----------------------------------------------------<br/>
		<h2>Pour effectuer des changements : </h2>
		<style>
		#tabModifEnt tr td{
    padding: 15px;
    border: 1px solid navy;
		}
		</style>
		<form action="index.php" method="post" onSubmit="return VerifSubmit();">
		<div class="resptab">
		<TABLE id="tabModifEnt">
	  	<CAPTION> Identité </CAPTION>
	  	<TR>
	 			<TD> <label for="nomEtu"> Nom</label>
				<br/>
				<input required type="text" name="nomEtu" id="nomEtu" value="'.$profil->getNomEtu().'" onblur="verifString(this, \'messageNomEtu\', 20)">
				<p id="messageNomEtu" style="color:red"></p>
				<label for="prenomEtu"> Prénom</label>
				<br/>
				<input required type="text" name="prenomEtu" id="prenomEtu" value="'.$profil->getPrenomEtu().'" onblur="verifString(this, \'messagePrenomEtu\', 20)">
				<p id="messagePrenomEtu" style="color:red"></p>
				<label for="email"> Adresse e-mail</label>
				<br/>
				<input required type="text" name="email" id="email" value="'.$profil->getMailEtu().'" onblur="verifEmail(this, \'messageEmail\')">
	 			<p id="messageEmail" style="color:red"></p>
				<label for="numTelEtu"> Numéro de téléphone</label>
				<br/>
	 			<input required type="text" id ="numTelEtu" name="numTelEtu" value="'.$profil->getNumTelEtu().'" onblur="verifTelephone(this, \'messageTel\')">
				<br /> <br/>
				</TD>
				<p id="messageTel" style="color:red"></p>
	 			<TD> 	<input type="submit" name="modification_etudiant_identite" value="confirmer"/> </TD>
		</TABLE>
		</div>
		</form>
		<form action="index.php" method="post" enctype="multipart/form-data">
		<div class="resptab">
		<TABLE id="tabModifEnt">
			<CAPTION> Modifier mon CV </CAPTION>
			<TR>
				<TD>
					<label for="nom"> Modifier votre CV (format .pdf | 1Mo max)</label>
					<br/>
					<input type="hidden" name="MAX_SIZE" value=1048576>
					<input type="file" name="cv" required/>
				</TD>
				<p id="messageTel" style="color:red"></p>
				<TD> 	<input type="submit" name="modification_cv" value="confirmer"/> </TD>
		</TABLE>
		</div>
		</form>
		<br/>
		<form action="index.php" method="post" >
		<div class="resptab">
		<TABLE id="tabModifEnt">
	  	<CAPTION> Modifier le mot de passe </CAPTION>
	  	<TR>
	 			<TD> <label for="mdpActuel"> Mot de passe actuel</label>
				<br/>
				<input required type="password" name="mdpActuel" id = "mdpActuel">
				<br/><br/>
				<label for="passw"> Nouveau mot de passe</label>
				<br/>
				<input required type="password" name="mdpNouveau1" id="passw">
				<br/><br/>
				<label for="passwBis"> Confirmez</label>
				<br/>
				<input required type="password" name="mdpNouveau2" onblur="verifMdp(\'messageMdp\')" id="passwBis">
				<p id="messageMdp" style="color:red"></p> </TD>
	 			<TD> 	<input type="submit" name="modification_etudiant_motdepasse" value="confirmer"/> </TD>
		</TABLE>
		</div>
		</form>
		<br/><br/><br/>
		</html></body>
</html>
		';
		echo $util->generePied();
		?>
	</body>
	</html>

	<?php
	}
	/**
	 * Fonction permettant l'affichage des formations possibles d'une entreprise.
	 */
	public function afficherFormations(){
		$util = new UtilitairePageHtml();
		echo $util->genereBandeauApresConnexion();
		$dao = new Dao();
		$config = $dao -> getConfiguration();
	?>
	<!DOCTYPE html>
	<html>
	<head>
		<link rel="stylesheet" type="text/css" href="vue/css/general.css">
		<title></title>
		<meta charset="UTF-8">
	</head>
	<body>
	<div id="main">
		<br/>&nbsp;&nbsp;&nbsp;&nbsp;Bonjour,
		<br/><br/>&nbsp;&nbsp;&nbsp;&nbsp;Ici seront affichées les formations possibles de l'entreprise. Celle-ci pourra les modifier en respectant les contraintes de son compte.
		<br/><br/>&nbsp;&nbsp;&nbsp;&nbsp;Une pause à midi est prévue pour les entretiens qui se déroulent toute la journée.
		<?php
		echo "<br/><br/><b>&nbsp;&nbsp;&nbsp;&nbsp;Un entretien dure ".$config['dureeCreneau']." minutes.</b></div>";
		$id = $_SESSION['idUser'];
		$listeFormation = $dao -> getFormationsAffichage($id);
		$formation = "Formation";
		$formation::afficherForm($listeFormation);
		echo $util->generePied();
		?>
	</body>
	</html>

	<?php
	}
	/**
	 * Fonction permettant l'affichage de la page de modification d'un compte entreprise.
	 */
	public function afficherCompteEnt(){
		$util = new UtilitairePageHtml();
		echo $util->genereBandeauApresConnexion();
		$dao = new Dao();
		$id = $_SESSION['idUser'];
		$tabprofil = $dao->getEnt($id);
		$profil = $tabprofil[0];
		$util = new UtilitairePageHtml();
		$dispo = "";
		if ($profil->getTypeCreneau() == "journee") {
			$dispo = "Journée.";
		}
		if ($profil->getTypeCreneau() == "matin") {
			$dispo = "Matinée.";
		}
		if ($profil->getTypeCreneau() == "apres_midi") {
			$dispo = "Après-midi.";
		}
		echo '
		<br/><br/><br/>
		<span class="categorie_profil">Nom de l\'entreprise :</span> '.$profil->getNomEnt().'
		<br/><br/>
		<span class="categorie_profil">Ville de l\'entreprise :</span> '.$profil->getVilleEnt().'
		<br/><br/>
		<span class="categorie_profil">Code Postal :</span> '.$profil->getCodePostal().'
		<br/><br/>
		<span class="categorie_profil">Adresse :</span> '.$profil->getAdresseEnt().'
		<br/><br/>
		<span class="categorie_profil">Disponibilité :</span> '.$dispo.'
		<br/><br/>
		<span class="categorie_profil">Nom du contact :</span> '.$profil->getPrenomContact().' '.$profil->getNomContact().'
		<br/><br/>
		<span class="categorie_profil">Email :</span> <a style="color:rgb(10,10,200)" href="mailto:'.$profil->getMailEnt().'">'.$profil->getMailEnt().'</a>
		<br/><br/>
		<span class="categorie_profil">Téléphone :</span> '.$profil->getNumTelContact().'
		<br/><br/>
		<span class="categorie_profil">Recherche :</span> '.$profil->getFormationsRecherchees().' avec '.$profil->getNbRecruteurs().' recruteur(s).
		<br/><br/>
		<span class="categorie_profil">Nombre de sessions en parallèle :</span> '.$profil->getNbStands().'
		<br/><br/>
		<span class="categorie_profil">Visioconférence :</span> '.$profil->getOffre().'
		<br/><br/>
		<span class="categorie_profil">Description de l\'offre :</span>'./*$profil->getOffre().*/'
		<br/><br/>
		
<!--		<form action="index.php" method="post">
		    <input type="submit" id="visioconference" name="visioconf" value="Visioconférence">
        </form>-->
			';


		//<!-- Nom -->
		?>
		<script>
      //On surligne les cases non valides
      function surligne(champ, erreur) {
      if(erreur)
        champ.style.backgroundColor = "#fba";
      else
        champ.style.backgroundColor = "";
      }
      function verifString(champ, txt, longMax) {
        if(champ.value.length > longMax) {
          surligne(champ, true);
					champ.value = "";
          document.getElementById(txt).innerHTML = longMax + " caractères maximum autorisé";
          return true;
        } else {
          surligne(champ, false);
          document.getElementById(txt).innerHTML = "";
          return false;
        }
      }
      function verifNombre(champ, txt, longMax) {
        if(champ.value.length > longMax || (!/^\d+$/.test(champ.value) && champ.value.length != 0)) {
          surligne(champ, true);
          document.getElementById(txt).innerHTML = "Un nombre de taille maximum " + longMax + " est attendu";
					champ.value = "";
          return true;
        } else {
          surligne(champ, false);
          document.getElementById(txt).innerHTML = "";
          return false;
        }
      }
      function verifCodePostal(champ, txt) {
        if(champ.value.length != 5 || !/^\d+$/.test(champ.value)) {
          surligne(champ, true);
          document.getElementById(txt).innerHTML = "Le code postal doit être rentré au format 44000";
					champ.value = "";
          return true;
        } else {
          surligne(champ, false);
          document.getElementById(txt).innerHTML = "";
          return false;
        }
      }
      function verifTelephone(champ, txt) {
        if(champ.value.length != 10 || !/^\d+$/.test(champ.value)) {
          surligne(champ, true);
          document.getElementById(txt).innerHTML = "Format invalide";
          return true;
        } else {
          surligne(champ, false);
          document.getElementById(txt).innerHTML = "";
          return false;
        }
      }
      function verifEmail(champ, txt){
        var reg = new RegExp("^[a-z0-9]+([_|\.|-]{1}[a-z0-9]+)*@[a-z0-9]+([_|\.|-]{1}[a-z0-9]+)*[\.]{1}[a-z]{2,6}$", "i");
        if(!reg.test(champ.value)) {
          surligne(champ, true);
          document.getElementById(txt).innerHTML = "L\'e-mail n\'est pas valide.";
					champ.value = "";
          return true;
        } else {
          surligne(champ, false);
          document.getElementById(txt).innerHTML = "";
          return false;
        }
      }
      function verifMdp(txt){
        var passw = document.getElementById("passw");
				var passwBis = document.getElementById("passwBis");
        if (passw.value != passwBis.value) {
          surligne(passw, true);
          surligne(passwBis, true);
					passw.value = "";
					passwBis.value = "";
          document.getElementById(txt).innerHTML = "Les 2 valeurs sont différentes";
          return true;
        } else if (passw.value.length > 20 || passw.value.length < 5) {
          surligne(passw, true);
          surligne(passwBis, true);
					passw.value = "";
					passwBis.value = "";
          document.getElementById(txt).innerHTML = "Le mot de passe doit faire 5 à 20 caractères";
          return true;
        } else {
          surligne(passw, false);
          surligne(passwBis, false);
          document.getElementById(txt).innerHTML = "";
          return false;
        }
      }
      </script>


			<script type="text/javascript">
				EnableSubmit = function(val)
				{
				    var sbmt = document.getElementById("submit");
						console.log(val.id);
				    if (val.checked == true)
				    {
							document.getElementById("boutonUpload"+val.id).style = "display";
				      //  sbmt.disabled = false;
				    }
				    else
				    {
							document.getElementById("boutonUpload"+val.id).style = "display:none";
				        // sbmt.disabled = true;
				    }
				}
			</script>
			<script>
			VerifSubmit = function()
				{
				html = html.replace(/</g, "&lt;").replace(/>/g, "&gt;");
				var nb_repas = document.getElementById("nb_repas");
				var checkboxRepas = document.getElementById("checkbox_repas");
				var passw = document.getElementById("passw");
				var passwBis = document.getElementById("passwBis");
					if (checkboxRepas.checked == true) {
					    if (nb_repas.value == "" || nb_repas.value == null)
					    {
					    	alert("Vous n\'avez pas précisé combien de repas seront à prévoir.");
					        return false;
					    }
					}
					if (passw.value != passwBis.value) {
							alert("Les mots de passe ne coïncident pas.");
					        return false;
					}
					if (/^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i.test(document.getElementById("mail").value))
					  {
					    return true;
					  }
					  else {
					  	alert("L\'adresse email n'est pas correcte !");
					 	return false;
					  }
				}
			</script>
		<?php
		echo'
			<!--Les scripts pour vérifier chaque case-->
		<br><br/><br/><br/>
		----------------------------------------------------<br/><br/>
		<h2>Pour effectuer des changements : </h2>
		<style>
		#tabModifEnt tr td{
    padding: 15px;
    border: 1px solid navy;
		}
		</style>
		<form action="index.php" method="post" onSubmit="return VerifSubmit();">
			<TABLE id="tabModifEnt">
		  	<CAPTION> Organisation </CAPTION>
		  	<TR>
		 			<TD>
						<label for="disponibiliteSociete">Disponibilité</label>
						<br/>
						<select id = "disponibiliteSociete" required name="disponibiliteSociete"/>';
						if ($profil->getTypeCreneau() == "apres_midi") {
							echo '<option value="apres_midi" selected >Après-midi</option>';
							}
						if ($profil->getTypeCreneau() == "journee") {
							echo '<option value="matin">Matin</option>
										<option value="apres_midi">Après-midi</option>
										<option value="journee" selected> Journée</option>
										';
						}
						echo '</select>
						<br/><br/>
						<label for="nbRecruteursSociete">Nombre de recruteurs</label>
						<br/>
						<input required type="number" id = "nbRecruteursSociete" name="nbRecruteursSociete" min="1" max="20" value="'.$profil->getNbRecruteurs().'" >
						<br/><br/>
						<label for="nbStandsSociete">Nombre de sessions en parallèle</label>
						<br/>
						<input required type="number" id = "nbStandsSociete" name="nbStandsSociete" min="1" max="10" value="'.$profil->getNbStands().'" >
						<br/><br/>
					</td>
					<td><input type="submit" name="modification_entreprise_organisation" value="confirmer"/></td>
			</TABLE>
		</form><br/>
		<form action="index.php" method="post" enctype="multipart/form-data">

		<TABLE id="tabModifEnt">
	  	';
			$compteur = 0;
			$dateNow = new DateTime("now");
			$tabConfig = $dao->getConfiguration();
			$dateLimitEnt = new DateTime($tabConfig['dateFinInscriptionEnt']);
			//Correction du décalage d'une journée
			$dateLimitEnt->setTime(23,59,59);
			$dateDebutEnt = new DateTime((string)$tabConfig['dateDebutInscriptionEnt']);
			$formationsRecherchees = explode(",",$profil->getFormationsRecherchees());
			$listeFormations = $dao->getListeFormations();
			$listeDepartements = array();
			if ($dateNow <= $dateLimitEnt && $dateNow >= $dateDebutEnt) {
		echo
        '<CAPTION> Formations recherchées</CAPTION>
			<TR>
				<TD> ';
				foreach ($listeFormations as $formation) {
					if(!in_array($formation->getDepartement(), $listeDepartements)) {
						array_push($listeDepartements, $formation->getDepartement());
					}
				}
				foreach ($listeDepartements as $departement) {
					echo '<span><b>Département '.$departement.' :</b></span><br/>'."\n\t\t\t";
					foreach ($listeFormations as $formation) {
						if($formation->getDepartement() == $departement) {
							echo '<input type="checkbox" name="formation['.$compteur.']" value="'.$formation->getInitiales().'" id="'.$compteur.'" onClick="EnableSubmit(this)" ';
						if (in_array($formation->getInitiales(), $formationsRecherchees)) {
							echo 'checked ';
						}
						echo '>'."\n\t\t\t\t".'<a id="lienFormation" href="'. $formation->getLien() .'" target="_blank">'.$formation->getDescription().'</a>';
						if(file_exists("offre/".$profil->getNomEnt().'_offre_'.$formation->getInitiales().'.pdf'))
						{
							echo'<a href="offre/'.$profil->getNomEnt().'_offre_'.$formation->getInitiales().'.pdf"> [Voir l\'offre enregistrée] </a>';
						}
						 echo '<br> <input type="file" name="offre_'.$formation->getInitiales().'" style="display:none" id="boutonUpload'.$compteur.'"/> '."\n\t\t\t\t\t\t".'<br/>'."\n\t\t\t\t\t\t ";

						$compteur = $compteur + 1;
						}

					}
				}
		 		echo '<TD> 	<input type="submit" name="modification_entreprise_formations" value="confirmer"/> </TD>
			</TABLE>
			</form><br/>';
							}
		echo '
		<form action="index.php" method="post" >
			<TABLE id="tabModifEnt">
		  	<CAPTION> Informations sur la société </CAPTION>
		  	<TR>
		 			<TD> <label for="nomSociete"> Nom </label>
						<br/>
						<input required id = "nomSociete" type="text" name="nomSociete" value="'.$profil->getNomEnt().'" onblur="verifString(this, \'messageNom\', 20)">
						<p id="messageNom" style="color:red"></p>
						<label for="villeSociete"> Ville </label>
						<br/>
						<input required id = "villeSociete" type="text" name="villeSociete" value="'.$profil->getVilleEnt().'" onblur="verifString(this, \'messageVille\', 20)">
						<p id="messageVille" style="color:red"></p>
						<label for="codePostalSociete"> Code postal </label>
						<br/>
						<input required id = "codePostalSociete" type="text" name="codePostalSociete" value="'.$profil->getCodePostal().'" onblur="verifCodePostal(this, \'messageCP\')">
						<p id="messageCP" style="color:red"></p>
						<label for="adresseSociete"> Adresse </label>
						<br/>
						<input required id = "adresseSociete" type="text" name="adresseSociete" value="'.$profil->getAdresseEnt().'" onblur="verifString(this, \'messageAdresse\', 30)">
			 			<p id="messageAdresse" style="color:red"></p>
						<label for="offre_txt"> Offre d\'emploi</label>
						<br/>
						<textarea name=offre_txt rows="8" cols="80">'.$profil->getOffre().'</textarea>
					</TD>
		 			<TD>
						<input type="submit" name="modification_entreprise_informations" value="confirmer"/>
					</TD>
				</TR>
			</TABLE>
		</form>
		<br/>

		<form action="index.php" method="post" >
		<TABLE id="tabModifEnt">
	  	<CAPTION> Contact </CAPTION>
	  	<TR>
	 			<TD> <label for="nomContactSociete"> Nom du contact</label>
					<br/>
					<input required type="text" id = "nomContactSociete" name="nomContactSociete" value="'.$profil->getNomContact().'" onblur="verifString(this, \'messageNomContact\', 20)">
					<p id="messageNomContact" style="color:red"></p>
					<label for="prenomContactSociete"> Prénom du contact</label>
					<br/>
					<input required id="prenomContactSociete" type="text" name="prenomContactSociete" value="'.$profil->getPrenomContact().'" onblur="verifString(this, \'messagePrenomContact\', 20)" >
					<p id="messagePrenomContact" style="color:red"></p>
					<label for="emailSociete"> Email</label>
					<br/>
					<input required id = "emailSociete" type="text" name="emailSociete" value="'.$profil->getMailEnt().'" onblur="verifEmail(this, \'messageEmail\')">
					<p id="messageEmail" style="color:red"></p>
					<label for="numTelSociete"> Téléphone</label>
					<br/>
					<input required id = "numTelSociete" type="text" name="numTelSociete" value="'.$profil->getNumTelContact(). ')" id="passwBis">
					<p id="messageMdp" style="color:red"></p>
				</TD>
	 			<TD>
					<input type="submit" name="modification_entreprise_motdepasse" value="confirmer"/>
				</TD>
			</tr>
		</TABLE>
		</form>
		<br/>
		<form action="index.php" method="post">
		<TABLE id="tabModifEnt">
		<CAPTION> Choix pour la visioconférence </CAPTION>
		<TR>
		    <TD> <label for="visio"> Visioconférence </label>
		        <input required id="visio" type="radio" name="visio" value="Oui"> Oui
		        <input required id="visio" type="radio" name="visio" value="Non"> Non 
		    </TD>
		    <TD>
				<input type="submit" name="modification_choixvisio" value="confirmer"/>
			</TD>
		
        </TR>
        </TABLE>
        </form>
		<br/><br/><br/>
		\'';
		echo $util->generePied();
}
	/////////////////////∕FINFINFINFINFINFIFNIFNIFNFINFINFINFINFINFINFIFNFNIFNIFINFINFINFINFIFNIFN///////////////////////////
	/**
	 * Fonction permettant l'affichage de la page "Autres" destinée à l'administrateur.
	 */
	public function afficherAutres(){
		$util = new UtilitairePageHtml();
		$dao = new Dao();
		$tabDetails = $dao->getDetails();
		echo $util->genereBandeauApresConnexion();
	?>
	<!DOCTYPE html>
	<html>
	<head>
		<link rel="stylesheet" type="text/css" href="vue/css/general.css">
		<title></title>
		<meta charset="UTF-8">
	</head>
	<body>
	<div id="main">
		<br/><br/>
		<div>
		<table id="tabStatistiques">
		<tr>
			<td>Nombre total de participants</td>
			<td><?php echo ($tabDetails['nbEnt'] + $tabDetails['nbEtu']);?></td>
		</tr>
		<tr>
			<td>Nombre d'étudiants</td>
			<td><?php echo $tabDetails['nbEtu'];?></td>
		</tr>
		<tr>
			<td>Nombre d'entreprises</td>
			<td><?php echo $tabDetails['nbEnt'];?></td>
		</tr>
		
		<tr>
			<td>Nombre de recrutements attendus</td>
			<td><?php echo $tabDetails['nbAlternants'];?></td>
		</tr>
           <tr>
			<td>Nombre d'inscriptions finalis&eacute;es</td>
			<td><?php echo $tabDetails['nbInscritsfinalisees'];?></td>
		</tr>

		<tr>
			<td>Nombre de repas à prévoir</td>
			<td><?php echo $tabDetails['nbRepas'];?></td>
		</tr>

	</table>
	</div>
		<br/><br/>
		<!-- IMPORTANT : demande de génération des emplois du temps -->
		<?php
		$date = getdate();
			?>
			<form method="POST" action="index.php" onsubmit="return ConfirmerGeneration();">planning
			<input type="submit" value="Générer les emplois du temps" name="startGeneration"/>
              	</form>
			<script>
				function ConfirmerGeneration() {
					return confirm('Êtes-vous sûr(e) de vouloir générer les emplois du temps avec les données actuelles ?');
				}
			</script>
       <br/><br>
            <a href="index.php?export=CreneauxEntreprise"><b>Nombre Total de cr&eacute;neaux par entreprise : </b>

   			<img title="Export complet du planning" alt="Export complet du planning" src="./vue/img/page_excel.png"></a>
            <br/><br>

            <a href="index.php?export=AffectationsEtudiants"><b>Affectations des Etudiants : </b>

                <img title="Export complet du planning" alt="Export complet du planning" src="./vue/img/page_excel.png"></a>
            <br/><br>
            <a href="index.php?export=ExportDistCrFormEntr.php"><b>Distribution des cr&eacute;neaux formations-Entreprises : </b>

    		<img title="Export complet du planning" alt="Export complet du planning" src="./vue/img/page_excel.png"></a>
            <br/><br>
            <a href="index.php?export=EntResAffIns"><b>Par Entreprise le nombre de cr&eacute;neaux r&eacute;serv&eacute;s, affect&eacute;s et inscrits : </b>

    		<img title="Export complet du planning" alt="Export complet du planning" src="./vue/img/page_excel.png"></a>
            <br/><br>

            <a href="index.php?export=EtudiantChoixAff"><b>Par &eacute;tudiant le nombre de choix et d'affectations : </b>

    		<img title="Export complet du planning" alt="Export complet du planning" src="./vue/img/page_excel.png"></a>
            <br/><br>

    		<a href="index.php?export=EtudiantChoixNonSatisfait"><b>Etudiants non satisfaits de leurs choix : </b>

            <img title="Export complet du planning" alt="Export complet du planning" src="./vue/img/page_excel.png"></a>
            <br/><br>

                 <a href="vue/ExportPlanning.php"> <b>Planning : </b>
        <img title="Export complet du planning" alt="Export complet du planning" src="./vue/img/page_excel.png"></a>
        <br/><br>

				<a href="index.php?export=PlanningEntreprises"><b>Planning des entreprises : </b>
				<img title="Export complet du planning" alt="Export complet du planning" src="./vue/img/page_excel.png"></a>
			<?php
		$this->affichageModifBandeau();
		?>

	</div>


	</body>
	</html>

	<?php
	echo $util->generePied();

	}
	//PROJET
	/**
	 * Fonction permettant l'affichage de la page "Autres" destinée au responsable.
	 */
	public function afficherAutresResponsable(){
		
		$util = new UtilitairePageHtml();
		$dao = new Dao();
		$tabDetails = $dao->getDetailsResponsable();
		echo $util->genereBandeauApresConnexion();
	?>
	<!DOCTYPE html>
	<html>
	<head>
		<link rel="stylesheet" type="text/css" href="vue/css/general.css">
		<title></title>
		<meta charset="UTF-8">
	</head>
	<body>
	<div id="main">
		<br/><br/>
		<div>
		<table id="tabStatistiques">
		<tr>
			<td>Nombre de participants lié à votre formation</td>
			<td><?php echo ($tabDetails['nbEnt'] + $tabDetails['nbEtu']);?></td>
		</tr>
		<tr>
			<td>Nombre d'étudiants inscrits dans votre formation</td>
			<td><?php echo $tabDetails['nbEtu'];?></td>
		</tr>
		<tr>
			<td>Nombre d'entreprises intéressées par votre formation</td>
			<td><?php echo $tabDetails['nbEnt'];?></td>
		</tr>
           <tr>
			<td>Nombre d'inscriptions finalis&eacute;es</td>
			<td><?php echo $tabDetails['nbInscritsfinalisees'];?></td>
		</tr>

	</table>
	</div>
		<br/><br/>
		<!-- IMPORTANT : demande de génération des emplois du temps -->
		
            <a href="index.php?export=CreneauxEntrepriseResponsable"><b>Nombre Total de cr&eacute;neaux par entreprise : </b>

   			<img title="Export complet du planning" alt="Export complet du planning" src="./vue/img/page_excel.png"></a>
            <br/><br>

            <a href="index.php?export=AffectationsEtudiantsResponsable"><b>Affectations des Etudiants : </b>

                <img title="Export complet du planning" alt="Export complet du planning" src="./vue/img/page_excel.png"></a>
            <br/><br>
            <a href="index.php?export=DistCrFormEntr.php"><b>Distribution des cr&eacute;neaux formations-Entreprises : </b>

    		<img title="Export complet du planning" alt="Export complet du planning" src="./vue/img/page_excel.png"></a>
            <br/><br>
            <a href="index.php?export=EntResAffIns"><b>Par Entreprise le nombre de cr&eacute;neaux r&eacute;serv&eacute;s, affect&eacute;s et inscrits : </b>

    		<img title="Export complet du planning" alt="Export complet du planning" src="./vue/img/page_excel.png"></a>
            <br/><br>

            <a href="index.php?export=EtudiantChoixAffResp"><b>Par &eacute;tudiant le nombre de choix et d'affectations : </b>

    		<img title="Export complet du planning" alt="Export complet du planning" src="./vue/img/page_excel.png"></a>
            <br/><br>

    		<a href="index.php?export=EtudiantChoixNonSatisfaitResp"><b>Etudiants non satisfaits de leurs choix : </b>

            <img title="Export complet du planning" alt="Export complet du planning" src="./vue/img/page_excel.png"></a>
            <br/><br>

                 <a href="vue/ExportPlanning.php"> <b>Planning : </b>
        <img title="Export complet du planning" alt="Export complet du planning" src="./vue/img/page_excel.png"></a>
			<br/><br>
			<a href ="index.php?export=vueMonCompte.php"> <b>Mon compte</b>
	</div>


	</body>
	</html>

	<?php
	echo $util->generePied();

	}
	/////////
	/**
	 * Fonction permettant l'affichage du formulaire de modification du bandeau du site.
	 */
	private function affichageModifBandeau(){
		?>
		<form method = "post" action = "index.php?choix=ok&menu=6" enctype="multipart/form-data">
			<p>
				<b>Sélectionnez le nouveau bandeau du site</b>
			</p>
			<input type="file" name="mon_nouveau_bandeau" id="mon_nouveau_bandeau" onclick="value=''" />
			<br /><br />
			<input type = "submit" value = "Importer le nouveau bandeau" name="modificationBandeau">
		</form>
		<?php
	}
	/**
	 * Fonction permettant l'affichage de la liste des formations.
	 * @param  array   $tableauFormations              un tableau de formations avec pour chacune d'elle, les informations sur le nombre de créneaux affectés à la formation
	 * @param  array   $tableauFormationsNonChoisies   des listes de formations non choisies
	 * @param  array   $tabListFormation               tableau des formations
	 */
	public function afficherListeFormations($tableauFormations, $tableauFormationsNonChoisies, $tabListFormation){
	$util = new UtilitairePageHtml();
	echo $util->genereBandeauApresConnexion("lorem", "ipsum");
	?>

	<div id="main">
		<h1>Liste des formations</h1>
		<br/>
		<div class="resptab" >
			<table id = "tableauFormationsAdmin">
				<thead>
					<td>Nom de la formation</td>
					<td>Nombre de créneaux réservés</td>
					<td>Nombre de créneaux affectés</td>
					<td>Nombre d'étudiants inscrits</td>
			</thead>
			<tbody>
				<?php
				$nbreCreneauxReserves = 0;
				$nbCreneauxAffectes = 0;
				$nbEtudiantsInscrits = 0;
				foreach ($tableauFormations as $formation) {
					$nbreCreneauxReserves += $formation['nbCreneauxReserves'];
					$nbCreneauxAffectes += $formation['nbCreneauxAffectes'];
					$nbEtudiantsInscrits += $formation['nbEtudiantsInscrits'];
				?>
				<tr>
					<td>
						<a href = "index.php?affichageFormation=<?=$formation['typeFormation']?>">
							<?=$formation['typeFormation']?>
						</a>
					</td>
					<td><?=$formation['nbCreneauxReserves']?></td>
					<td><?=$formation['nbCreneauxAffectes']?></td>
					<td><?=$formation['nbEtudiantsInscrits']?></td>
				</tr>
				<?php
				}
				?>
			<tr>
				<td><b>Totaux<b></td>
				<td><b><?=$nbreCreneauxReserves?></b></td>
				<td><b><?=$nbCreneauxAffectes?></b></td>
				<td><b><?=$nbEtudiantsInscrits?></b></td>
			</tr>
			</tbody>
			</table>
		</div>
		<?php
		if(sizeof($tableauFormationsNonChoisies) != 0){
		?>
		<br>
		<br>
		<div class="resptab">
			<table id = "tableauFormationsAdmin">
				<caption>Liste des formations qui n'ont pas encore été choisies par les entreprises</caption>
				<thead>
					<tr>
						<td>Département</td>
						<td>Initiales</td>
						<td>Description</td>
						<td>Lien</td>
						<td>Supprimer des formations</td>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($tableauFormationsNonChoisies as $formationNonChoisie) {
					?>
						<tr>
							<td><?=$formationNonChoisie['departement']?></td>
							<td><?=$formationNonChoisie['initiales']?></td>
							<td><?=$formationNonChoisie['description']?></td>
							<td><?=$formationNonChoisie['lien']?></td>
							<td><a href = "index.php?suppressionFormation=<?=$formationNonChoisie['initiales']?>">Supprimer</a></td>
						</tr>
					<?php
					}
					?>
				</tbody>
			</table>
		</div>
		<?php
		}
	 	?>
		<br>
		<br>
		<div class="resptab">
			<form action="index.php" method="post">
				<table>
					<thead>
						<td>Département</td>
						<td>Initiales</td>
						<td>Description</td>
						<td>Lien</td>
					</thead>
					<tbody>
						<tr>
							<td>
								<input type="text" list="formations" name="departement" placeholder="INFORMATIQUE">
								<datalist id="formations">
									<?php
										foreach ($tabListFormation as  $value) {
											echo "<option value='".$value."'></option>\n";
										}
									 ?>
								</datalist>
							</td>
							<td><input type="text" name="initiales" placeholder="DUT INFO"></td>
							<td><input type="text" name="description" placeholder="DUT Informatique (INFO 2ème année)"></td>
							<td><input type="text" name="lien" placeholder="https://univ-nantes.fr"></td>
						</tr>
					</tbody>
				</table>
				<br>
				<input type="submit" name="addFormation" value="Ajouter">
			</form>
		</div>
	</div>
	<?php
	echo $util->generePied();
	}
	/**
	 * Fonction permettant d'afficher une seule formation avec ses détails.
	 * @param  array $tabFormation  un tableau composé des détails d'une entreprise
	 * @param  String $url          Url correspondant aux informations de la formation
	 */
	public function afficherUneFormation($tabFormation, $url){
		$util = new UtilitairePageHtml();
		echo $util->genereBandeauApresConnexion();
		?>

		<div id="main">
			<h1><?=$tabFormation[0]['typeFormation']?></h1>
			Lien d'information : <a href="<?=$url?>"><?=$url?></a>
			<br><br>
			<div class="resptab" style = "height:auto">
				<table>
					<tr>
						<td>Entreprises proposées</td>
						<td>Nombre de créneaux réservés</td>
						<td>Nombre de créneaux affectés</td>
						<td>Nombre d'étudiants inscrits</td>
					</tr>
					<?php
					$nbTotalCreneauxAffectes = 0;
					$nbTotalEtudiantsInscrits = 0;
					$nbTotalCreneauxReserves = 0;
					foreach ($tabFormation as $elt) {
						$idFormation = $elt['IDformation'];
						$nbTotalCreneauxAffectes += $elt['NBCreneauxAffectes'];
						$nbTotalEtudiantsInscrits += $elt['nbEtudinantsInscrits'];
						$nbTotalCreneauxReserves += $elt['nbcreneauxReserves'];
						?>
						<tr>
							<td>
									<a href="index.php?profil=<?=$elt['idEnt']?>&type=Ent"><?=$elt['nomEnt']?></a>
							</td>
							<td><b><?=$elt['nbcreneauxReserves']?></b></td>
							<td><?=$elt['NBCreneauxAffectes']?></td>
							<td><?=$elt['nbEtudinantsInscrits']?></td>
							
						</tr>
					<?php
					}
					?>
					<tr>
						<td><b>TOTAUX</b></td>
						<td><b><?=$nbTotalCreneauxReserves?></b></td>
						<td><b><?=$nbTotalCreneauxAffectes?></b></td>
						<td><b><?=$nbTotalEtudiantsInscrits?></b></td>
						
					</tr>
				</table>
				<br>
				<br>
			</div>
			<a href = "index.php?choix=ok&menu=3">Retourner à la liste des formations</a>
		</div>
		<?php
		echo $util->generePied();
	}
	
		//PROJET
	/**
	 * Fonction permettant l'affichage de la liste des formations.
	 * @param  array   $tableauFormations              un tableau de formations avec pour chacune d'elle, les informations sur le nombre de créneaux affectés à la formation
	 * @param  array   $tabListFormation               tableau des formations
	 */
	public function afficherListeFormationsResponsable($tableauFormations, $tabListFormation){
		$util = new UtilitairePageHtml();
		echo $util->genereBandeauApresConnexion("lorem", "ipsum");
		?>
	
		<div id="main">
			<h1>Liste des formations</h1>
			<br/>
			<div class="resptab" >
				<table id = "tableauFormationsAdmin">
					<thead>
						<td>Nom de la formation</td>
						<td>Nombre de créneaux réservés</td>
						<td>Nombre de créneaux affectés</td>
						<td>Nombre d'étudiants inscrits</td>
				</thead>
				<tbody>
					<?php
					$nbreCreneauxReserves = 0;
					$nbCreneauxAffectes = 0;
					$nbEtudiantsInscrits = 0;
					foreach ($tableauFormations as $formation) {
						$nbreCreneauxReserves += $formation['nbCreneauxReserves'];
						$nbCreneauxAffectes += $formation['nbCreneauxAffectes'];
						$nbEtudiantsInscrits += $formation['nbEtudiantsInscrits'];
					?>
					<tr>
						<td>
							<a href = "index.php?affichageFormation=<?=$formation['typeFormation']?>">
								<?=$formation['typeFormation']?>
							</a>
						</td>
						<td><?=$formation['nbCreneauxReserves']?></td>
						<td><?=$formation['nbCreneauxAffectes']?></td>
						<td><?=$formation['nbEtudiantsInscrits']?></td>
					</tr>
					<?php
					}
					?>
				<tr>
					<td><b>Totaux<b></td>
					<td><b><?=$nbreCreneauxReserves?></b></td>
					<td><b><?=$nbCreneauxAffectes?></b></td>
					<td><b><?=$nbEtudiantsInscrits?></b></td>
				</tr>
				</tbody>
				</table>
			</div>
		</div>
		<?php
		echo $util->generePied();
		}
		//////////
}
?>
