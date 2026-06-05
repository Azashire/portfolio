<!DOCTYPE html>

<html lang="fr">

<?php include "head.php" ?>


<body>

    <?php include "header.php" ?>
    <?php include "navbar.php" ?>

    <?php
    if (session_status() === PHP_SESSION_NONE) session_start();
    require_once "db.php";

    $tousLesTags = $pdo->query('SELECT id, nom FROM tags ORDER BY nom ASC')->fetchAll();

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
        GROUP BY p.id
        ORDER BY p.date_realisation DESC
    ';
    $tousLesProjets = $pdo->query($sql)->fetchAll();
    ?>

    <div class="projectsBody">
    <aside>

        <input class="searchBar" type="search" name="searchProjects" id="searchProjects"
               placeholder="Rechercher...">

        <hr>

        <section class="filters">
            <?php foreach ($tousLesTags as $tag): ?>
                <p class="filter" data-tag="<?= htmlspecialchars($tag['nom']) ?>">
                    <?= htmlspecialchars($tag['nom']) ?>
                </p>
            <?php endforeach; ?>
        </section>
        <hr>

        <section class="projects">
            <?php foreach ($tousLesProjets as $projet): ?>
                <p><?= htmlspecialchars($projet['titre']) ?></p>
                <hr>
            <?php endforeach; ?>
        </section>

    </aside>

    <div class="myProjects">

        <section class="title">
            <h2>Mes projets</h2>
        </section>

        <p class="aucunResultat" id="aucunResultat">Aucun projet ne correspond à votre recherche.</p>

        <div class="projects">

            <?php foreach ($tousLesProjets as $projet):
                $tags = $projet['tags'] ?? '';
                $dateObj     = DateTime::createFromFormat('Y-m-d', $projet['date_realisation']);
                $moisFr      = ['', 'janvier','février','mars','avril','mai','juin',
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
                        <img
                            class="projectImage"
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
    </div>
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
