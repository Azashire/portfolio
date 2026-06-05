<!DOCTYPE html>
<html lang="fr">
<?php include "head.php" ?>


<body>
<?php include "header.php" ?>
<?php include "navbar.php" ?>

<?php


require_once "auth.php";
require_once "db.php";

$erreurs = [];
$succes  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    if ($action === 'creer') {

        $nom = trim($_POST['nom_nouveau'] ?? '');

        if ($nom === '') {
            $erreurs[] = 'Le nom du tag est obligatoire.';
        } elseif (strlen($nom) > 50) {
            $erreurs[] = 'Le nom ne peut pas dépasser 50 caractères.';
        } else {
            try {
                $stmt = $pdo->prepare('INSERT INTO tags (nom) VALUES (:nom)');
                $stmt->execute([':nom' => $nom]);
                $succes = 'Tag « ' . htmlspecialchars($nom) . ' » créé.';
            } catch (PDOException $e) {

                if ($e->getCode() === '23000') {
                    $erreurs[] = 'Un tag « ' . htmlspecialchars($nom) . ' » existe déjà.';
                } else {
                    $erreurs[] = 'Erreur : ' . $e->getMessage();
                }
            }
        }
    }


    elseif ($action === 'renommer') {

        $id  = intval($_POST['id']          ?? 0);
        $nom = trim($_POST['nom_modifie']   ?? '');

        if ($id <= 0) {
            $erreurs[] = 'Tag invalide.';
        } elseif ($nom === '') {
            $erreurs[] = 'Le nouveau nom est obligatoire.';
        } elseif (strlen($nom) > 50) {
            $erreurs[] = 'Le nom ne peut pas dépasser 50 caractères.';
        } else {
            try {
                $stmt = $pdo->prepare('UPDATE tags SET nom = :nom WHERE id = :id');
                $stmt->execute([':nom' => $nom, ':id' => $id]);
                $succes = 'Tag renommé en « ' . htmlspecialchars($nom) . ' ».';
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') {
                    $erreurs[] = 'Un tag « ' . htmlspecialchars($nom) . ' » existe déjà.';
                } else {
                    $erreurs[] = 'Erreur : ' . $e->getMessage();
                }
            }
        }
    }


    elseif ($action === 'supprimer') {

        $id = intval($_POST['id'] ?? 0);

        if ($id <= 0) {
            $erreurs[] = 'Tag invalide.';
        } else {
            try {

                $stmt = $pdo->prepare('DELETE FROM tags WHERE id = :id');
                $stmt->execute([':id' => $id]);
                $succes = 'Tag supprimé.';
            } catch (PDOException $e) {
                $erreurs[] = 'Erreur : ' . $e->getMessage();
            }
        }
    }
}


$tags = $pdo->query('
    SELECT t.id, t.nom, COUNT(pt.id_projet) AS nb_projets
    FROM tags t
    LEFT JOIN projet_tag pt ON pt.id_tag = t.id
    GROUP BY t.id
    ORDER BY t.nom ASC
')->fetchAll();
?>


<div class="ajouterBody">
<section class="ajouterFormSection gererTagsSection">

    <h2>Gérer les tags</h2>

    <?php if (!empty($erreurs)): ?>
        <ul class="erreurs">
            <?php foreach ($erreurs as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php if ($succes !== ''): ?>
        <p class="succes"><?= $succes ?></p>
    <?php endif; ?>



    <section class="tagsSousSection">
        <h3>Créer un tag</h3>
        <form method="POST" action="" class="formCreerTag">
            <input type="hidden" name="action" value="creer">
            <div class="ligneFormTag">
                <input
                    type="text"
                    name="nom_nouveau"
                    maxlength="50"
                    placeholder="Nom du nouveau tag"
                    required>
                <button type="submit" class="btnSubmit">Créer</button>
            </div>
        </form>
    </section>

    <hr class="separateur">


    <section class="tagsSousSection">
        <h3>Tags existants</h3>

        <?php if (empty($tags)): ?>
            <p class="aucunTag">Aucun tag créé pour l'instant.</p>
        <?php else: ?>
            <ul class="listeTags">
                <?php foreach ($tags as $tag): ?>
                    <li class="ligneTag">
                        <form method="POST" action="" class="formRenommerTag">
                            <input type="hidden" name="action" value="renommer">
                            <input type="hidden" name="id" value="<?= $tag['id'] ?>">
                            <input
                                type="text"
                                name="nom_modifie"
                                maxlength="50"
                                required
                                value="<?= htmlspecialchars($tag['nom']) ?>">
                            <button type="submit" class="btnRenommer">Renommer</button>
                        </form>
                        <span class="nbProjets">
                            <?= $tag['nb_projets'] ?> projet<?= $tag['nb_projets'] > 1 ? 's' : '' ?>
                        </span>
                        <form method="POST" action=""
                              onsubmit="return confirmerSuppression(<?= $tag['nb_projets'] ?>, '<?= htmlspecialchars($tag['nom'], ENT_QUOTES) ?>')">
                            <input type="hidden" name="action" value="supprimer">
                            <input type="hidden" name="id" value="<?= $tag['id'] ?>">
                            <button type="submit" class="btnSupprimerTag">✕</button>
                        </form>

                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

</section>
</div>

<script>
function confirmerSuppression(nbProjets, nomTag) {
    if (nbProjets > 0) {
        return confirm(
            'Le tag « ' + nomTag + ' » est utilisé par ' + nbProjets + ' projet(s).\n' +
            'Le supprimer retirera ce tag de ces projets (les projets ne seront pas supprimés).\n\n' +
            'Continuer ?'
        );
    }
    return confirm('Supprimer le tag « ' + nomTag + ' » ?');
}
</script>

<?php include "footer.php" ?>
</body>
</html>
