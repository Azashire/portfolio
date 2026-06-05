<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<nav>
    <ul>
        <li><a href="index">Accueil</a></li>
        <!-- <li><a href="cv">CV</a></li> -->
        <li><a href="projects">Projets</a></li>
        <li><a href="contact">Contact</a></li>
        <?php if (!empty($_SESSION['admin_connecte'])): ?>
            <li><p>    ||||||     </p></li>
            <li><a href="ajouter_projet">+ Projets</a></li>
            <li><a href="gerer_tags">Tags</a></li>
            <li><a href="logout">Déconnexion</a></li>
        <?php endif; ?>
    </ul>
</nav>
