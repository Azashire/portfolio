<!DOCTYPE html>
<html lang="fr">
<?php include "head.php" ?>


<body>
<?php include "header.php" ?>
<?php include "navbar.php" ?>

<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once "db.php";

$sql = '
    SELECT
        p.id,
        p.titre,
        p.desc_courte,
        p.desc_longue,
        p.image,
        p.date_realisation,
        p.nb_personnes,
        p.temps_realisation,
        GROUP_CONCAT(t.nom ORDER BY t.nom SEPARATOR \',\') AS tags
    FROM projets p
    LEFT JOIN projet_tag pt ON pt.id_projet = p.id
    LEFT JOIN tags t        ON t.id = pt.id_tag
    WHERE p.projet_phare = 1
    GROUP BY p.id
    ORDER BY p.date_realisation DESC
';
$projetsPharesData = $pdo->query($sql)->fetchAll();
?>

<div class="homeBody">

    <section>
        <h3>Qui suis-je ?</h3>
        <p>
            Zacharie Cicorella Revol — Étudiant en informatique (BUT-1 Grenoble) — 18 ans
        </p>
        <p>
            Spécialisation dans le <u><b>développement</b></u>. En créant des projets de petite envergure, j'ai principalement fait du <u><b>front-end</b></u> et des <u><b>interfaces graphiques</b></u>. Je garde tout de même une grande <u><b>versatilité</b></u> et suis capable d'apprendre très rapidement<br>
            À la fin de mon parcours académique en informatique, mon objectif professionnel est de rejoindre une entreprise spécialisée dans le développement d'applications et de solutions informatiques. Je demeure cependant ouvert à toute opportunité liée de près ou de loin à l'informatique.<br>
        </p>
        <!-- <a href="cv"><p class="redirect">CV</p></a> -->
    </section>


    <?php if (!empty($projetsPharesData)): ?>
    <section class="sectionProjetsPhares">
        <h3>Projets phares</h3>
        <div class="projetsPhares">
            <?php foreach ($projetsPharesData as $projet):
                $tags = $projet['tags'] ?? '';
                $dateObj     = DateTime::createFromFormat('Y-m-d', $projet['date_realisation']);
                $moisFr      = ['','janvier','février','mars','avril','mai','juin',
                                'juillet','août','septembre','octobre','novembre','décembre'];
                $dateAffiche = $moisFr[(int)$dateObj->format('n')] . ' ' . $dateObj->format('Y');
            ?>
                <section class="project"
                    data-titre="<?= htmlspecialchars($projet['titre']) ?>"
                    data-tags="<?= htmlspecialchars($tags) ?>"
                    data-desc="<?= htmlspecialchars($projet['desc_courte']) ?>"
                    data-image="<?= htmlspecialchars($projet['image'] ?? '') ?>"
                    data-date="<?= htmlspecialchars($dateAffiche) ?>"
                    data-nb-personnes="<?= htmlspecialchars($projet['nb_personnes'] ?? '') ?>"
                    data-temps="<?= htmlspecialchars($projet['temps_realisation'] ?? '') ?>"
                    data-id="<?= $projet['id'] ?>">

                    <template class="descLongueTemplate">
                        <?= $projet['desc_longue'] ?>
                    </template>

                    <?php if ($projet['image']): ?>
                        <img class="projectImage"
                             src="<?= htmlspecialchars($projet['image']) ?>"
                             alt="<?= htmlspecialchars($projet['titre']) ?>">
                    <?php endif; ?>

                    <?php if (!empty($_SESSION['admin_connecte'])): ?>
                        <a href="modifier_projet.php?id=<?= $projet['id'] ?>"
                           class="btnModifier"
                           title="Modifier ce projet">✎</a>
                    <?php endif; ?>

                    <h3><?= htmlspecialchars($projet['titre']) ?></h3>

                    <article class="tags">
                        <p>Languages utilisés : </p>
                        <?php if ($tags !== ''): ?>
                            <?php foreach (array_filter(explode(',', $tags)) as $nomTag): ?>
                                <p class="tag"><?= htmlspecialchars(trim($nomTag)) ?></p>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </article>

                    <article class="projectDesc">
                        <p><?= htmlspecialchars($projet['desc_courte']) ?></p>
                    </article>

                    <article class="projectMeta">
                        <span><i class="bi bi-calendar3"></i> <?= htmlspecialchars($dateAffiche) ?></span>
                        <?php if (!empty($projet['nb_personnes'])): ?>
                            <span><i class="bi bi-person"></i> <?= (int)$projet['nb_personnes'] ?></span>
                        <?php endif; ?>
                        <?php if (!empty($projet['temps_realisation'])): ?>
                            <span><i class="bi bi-hourglass-bottom"></i> <?= htmlspecialchars($projet['temps_realisation']) ?></span>
                        <?php endif; ?>
                    </article>

                </section>
            <?php endforeach; ?>
        </div>

        <a href="projects"><p class="redirect">Tous les projets</p></a>
    </section>
    <?php endif; ?>

<section>
    <h3>Mon parcours</h3>
    <section class="parcours">
        <a href="https://iut2.univ-grenoble-alpes.fr/l-iut2/qui-sommes-nous-/les-departements-de-l-iut2/informatique-info--149104.kjsp" target="_blank"><img alt="logo université" src="assets/images/iut2.png"/></a>
        <div class="parcoursTxt">
            <p>IUT 2 Grenoble - Département Informatique</p>
            <p>BUT Info - Parcours A (Développement de solutions informatiques)</p>
            <p>2025-2026</p>
            <a href="https://iut2.univ-grenoble-alpes.fr/l-iut2/qui-sommes-nous-/les-departements-de-l-iut2/informatique-info--149104.kjsp" target="_blank"><p>Site web</p></a>
        </div>
    </section>
    <section class="parcours">
        <a href="https://www.institution-saint-francois.fr" target="_blank"><img alt="logo lycée" src="assets/images/collegelycee.png" style="padding: 25px"/></a>
        <div class="parcoursTxt">
            <p>Collège/Lycée Saint François Sainte Cécile</p>
            <p>BAC général Spé Mathématiques, Physique Chimie</p>
            <a href="https://www.institution-saint-francois.fr" target="_blank"><p>Site web</p></a>
        </div>
    </section>
</section>
</div>

<dialog class="projectModal" id="projectModal" aria-modal="true" aria-labelledby="modalTitre">
    <article class="modalContent">
        <header class="modalHeader">
            <h2 id="modalTitre"></h2>
            <button class="modalClose" id="modalClose" aria-label="Fermer">✕</button>
        </header>
        <img class="modalImage cache" id="modalImage" src="" alt="">
        <section class="modalTags">
            <p>Languages utilisés : </p>
        </section>
        <article class="modalMeta">
            <span id="modalDate"><i class="bi bi-calendar3"></i> </span>
            <span class="cache" id="modalNbPersonnes"><i class="bi bi-person"></i> </span>
            <span class="cache" id="modalTemps"><i class="bi bi-hourglass-bottom"></i> </span>
        </article>
        <section class="modalDesc">
            <p id="modalDescText"></p>
        </section>
    </article>
</dialog>

<?php include "footer.php" ?>

<script src="assets/js/projects.js"></script>

</body>
</html>
