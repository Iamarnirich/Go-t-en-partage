<?php
session_start();
if(isset($_SESSION["pseudo"])){
    echo '
        <div class = "Menu">
                    <ul>
                        <li>
                            <a href="compteHome.html"><i class="fas fa-solid fa-user"></i> <span>Profil</span></a>
                        </li>
                        <li>
                            <a href="..\PHP\abonnement.php"><i class="fas fa-solid fa-user-plus"></i> <span>Abonnements</span></a>
                        </li>
                        <li>
                            <a href="..\PHP\abonne.php"><i class="fas fa-solid fa-users"></i> <span>Abonnés</span></a>
                        </li>
                        <li>
                            <a href="../PHP/creation_liste.php"><i class="fas fa-solid fa-paper-plane"></i> <span>Publier une liste</span></a>
                        </li>
                    </ul>
                    <ul>
                        <li id="ModeAffichage">
                            <i class="fas fa-solid fa-lightbulb"></i> <span>Mode Sombre</span>
                        </li>
                        <li id="condition">
                            <i class="fas fa-regular fa-file"></i> <span>Conditions générales</span>
                        </li>
                        <li>
                            <a href="information.html"><i class="fas fa-solid fa-info"></i> <span>Informations</span></a>
                        </li>
                    </ul>
                </div>';
    }
    else{
        echo '
        <div class = "Menu">
                    <ul>
                        <li>
                            <a href="../PHP/connexion.php"><i class="fas fa-solid fa-user"></i> <span>Profil</span></a>
                        </li>
                        <li>
                            <a href="../PHP/connexion.php"><i class="fas fa-solid fa-user-plus"></i> <span>Abonnements</span></a>
                        </li>
                        <li>
                            <a href="../PHP/connexion.php"><i class="fas fa-solid fa-users"></i> <span>Abonnés</span></a>
                        </li>
                        <li>
                            <a href="../PHP/connexion.php"><i class="fas fa-solid fa-paper-plane"></i> <span>Publier une liste</span></a>
                        </li>
                    </ul>
                    <ul>
                        <li id="ModeAffichage">
                            <i class="fas fa-solid fa-lightbulb"></i> <span>Mode Sombre</span>
                        </li>
                        <li id="condition">
                            <i class="fas fa-regular fa-file"></i> <span>Conditions générales</span>
                        </li>
                        <li>
                            <a href="information.html"><i class="fas fa-solid fa-info"></i> <span>Informations</span></a>
                        </li>
                    </ul>
                </div>';
    }

?>