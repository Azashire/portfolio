<!DOCTYPE html>
<html lang="fr">
<?php include "head.php" ?>


<body>
<?php include "header.php" ?>
<?php include "navbar.php" ?>

<?php

require_once "auth.php";
require_once "db.php";
require_once "vendor/autoload.php";

$purifierConfig = HTMLPurifier_Config::createDefault();
$purifierConfig->set('HTML.AllowedElements', [
    'p', 'br',
    'strong', 'em', 'u', 's',
    'h2', 'h3', 'h4',
    'ul', 'ol', 'li',
    'a',
    'code', 'pre',
    'blockquote',
    'hr',
    'img',
]);
$purifierConfig->set('HTML.AllowedAttributes', [
    '*.class',
    'a.href', 'a.target', 'a.rel',
    'img.src', 'img.alt', 'img.width', 'img.height',
]);
$purifierConfig->set('AutoFormat.AutoParagraph', false);
$purifierConfig->set('Attr.AllowedFrameTargets', ['_blank', '_self', '_top']);

$purifier = new HTMLPurifier($purifierConfig);

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    die('Identifiant de projet invalide.');
}

$stmtProjet = $pdo->prepare('SELECT * FROM projets WHERE id = :id');
$stmtProjet->execute([':id' => $id]);
$projet = $stmtProjet->fetch();

if (!$projet) {
    http_response_code(404);
    die('Projet introuvable.');
}

$tousLesTags = $pdo->query('SELECT id, nom FROM tags ORDER BY nom ASC')->fetchAll();

$stmtTagsProjet = $pdo->prepare(
    'SELECT id_tag FROM projet_tag WHERE id_projet = :id'
);
$stmtTagsProjet->execute([':id' => $id]);

$tagsActuels = array_column($stmtTagsProjet->fetchAll(), 'id_tag');



if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'supprimer') {

    try {
        $stmt = $pdo->prepare('DELETE FROM projets WHERE id = :id');
        $stmt->execute([':id' => $id]);

        if ($projet['image'] && file_exists(__DIR__ . 'modifier_projet.php/' . $projet['image'])) {
            unlink(__DIR__ . 'modifier_projet.php/' . $projet['image']);
        }

        header('Location: projects.php');
        exit;

    } catch (PDOException $e) {
        die('Erreur lors de la suppression : ' . $e->getMessage());
    }
}


$erreurs     = [];
$titre       = $projet['titre'];
$descCourte  = $projet['desc_courte'];
$descLongue  = $projet['desc_longue'];
$dateReal    = $projet['date_realisation'];
$tagsChoisis = $tagsActuels;
$imageActuelle = $projet['image'];
$nbPersonnes = $projet['nb_personnes'] ?? '';
$tempsReal   = $projet['temps_realisation'] ?? '';
$projetPhare = (bool)($projet['projet_phare'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') !== 'supprimer') {


    $titre       = trim($_POST['titre']           ?? '');
    $descCourte  = trim($_POST['desc_courte']      ?? '');
    $descLongue  = trim($_POST['desc_longue']      ?? '');
    $dateReal    = trim($_POST['date_realisation'] ?? '');
    $tagsChoisis = isset($_POST['tags']) ? array_map('intval', $_POST['tags']) : [];
    $nbPersonnes = trim($_POST['nbPers']   ?? '');
    $tempsReal   = trim($_POST['timeUsed'] ?? '');
    $projetPhare = isset($_POST['projet_phare']) ? 1 : 0;


    $nouvelleImage = null;
    $supprimerImage = isset($_POST['supprimer_image']);

    $imageEnvoyee = isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE;

    if ($imageEnvoyee) {

        $fichier = $_FILES['image'];

        if ($fichier['error'] !== UPLOAD_ERR_OK) {
            $erreurs[] = 'Erreur lors de l\'upload (code ' . $fichier['error'] . ').';
        } else {

            $finfo          = new finfo(FILEINFO_MIME_TYPE);
            $mimeReel       = $finfo->file($fichier['tmp_name']);
            $typesAutorises = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

            if (!in_array($mimeReel, $typesAutorises)) {
                $erreurs[] = 'Format non autorisé. Formats acceptés : JPG, PNG, GIF, WEBP.';
            } elseif ($fichier['size'] > 5 * 1024 * 1024) {
                $erreurs[] = 'L\'image ne doit pas dépasser 5 Mo.';
            } else {
                $extension   = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));
                $nomFichier  = uniqid('projet_', true) . 'portfolio' . $extension;
                $dossierDest = __DIR__ . '/uploads/';
                $cheminDest  = $dossierDest . $nomFichier;

                if (!is_dir($dossierDest)) {
                    mkdir($dossierDest, 0755, true);
                }

                if (!move_uploaded_file($fichier['tmp_name'], $cheminDest)) {
                    $erreurs[] = 'Impossible de déplacer l\'image.';
                } else {
                    $nouvelleImage = 'uploads/' . $nomFichier;
                }
            }
        }
    }

    if ($titre === '') $erreurs[] = 'Le titre est obligatoire.';
    elseif (strlen($titre) > 150) $erreurs[] = 'Le titre ne peut pas dépasser 150 caractères.';

    if ($descCourte === '') $erreurs[] = 'La description courte est obligatoire.';
    elseif (strlen($descCourte) > 300) $erreurs[] = 'La description courte ne peut pas dépasser 300 caractères.';

    if ($descLongue === '') $erreurs[] = 'La description longue est obligatoire.';
    else $descLongue = $purifier->purify($descLongue);
    if ($dateReal   === '') $erreurs[] = 'La date de réalisation est obligatoire.';

    $nbPersonnesInt = null;
    if ($nbPersonnes !== '') {
        $nbPersonnesInt = intval($nbPersonnes);
        if ($nbPersonnesInt < 1 || $nbPersonnesInt > 255) {
            $erreurs[] = 'Le nombre de personnes doit être compris entre 1 et 255.';
        }
    }
    $tempsRealFinal = $tempsReal !== '' ? $tempsReal : null;

    if (empty($erreurs)) {
        try {
            $pdo->beginTransaction();
            if ($nouvelleImage) {
                $imageFinal = $nouvelleImage;
            } elseif ($supprimerImage) {
                $imageFinal = null;
            } else {
                $imageFinal = $imageActuelle;
            }

            $stmtUpdate = $pdo->prepare(
                'UPDATE projets
                 SET titre = :titre,
                     desc_courte = :desc_courte,
                     desc_longue = :desc_longue,
                     image = :image,
                     date_realisation = :date_realisation,
                     nb_personnes = :nb_personnes,
                     temps_realisation = :temps_realisation,
                     projet_phare = :projet_phare
                 WHERE id = :id'
            );
            $stmtUpdate->execute([
                ':titre'             => $titre,
                ':desc_courte'       => $descCourte,
                ':desc_longue'       => $descLongue,
                ':image'             => $imageFinal,
                ':date_realisation'  => $dateReal,
                ':nb_personnes'      => $nbPersonnesInt,
                ':temps_realisation' => $tempsRealFinal,
                ':projet_phare'      => $projetPhare,
                ':id'                => $id,
            ]);

            $pdo->prepare('DELETE FROM projet_tag WHERE id_projet = :id')
                ->execute([':id' => $id]);

            if (!empty($tagsChoisis)) {
                $stmtLiaison = $pdo->prepare(
                    'INSERT INTO projet_tag (id_projet, id_tag) VALUES (:id_projet, :id_tag)'
                );
                foreach ($tagsChoisis as $idTag) {
                    $stmtLiaison->execute([':id_projet' => $id, ':id_tag' => $idTag]);
                }
            }

            $pdo->commit();

            $ancienneImageSupprimee = ($nouvelleImage && $imageActuelle) || ($supprimerImage && $imageActuelle);
            if ($ancienneImageSupprimee && file_exists(__DIR__ . 'modifier_projet.php/' . $imageActuelle)) {
                unlink(__DIR__ . 'modifier_projet.php/' . $imageActuelle);
            }

            header('Location: projects.php');
            exit;

        } catch (PDOException $e) {
            $pdo->rollBack();
            if ($nouvelleImage && file_exists(__DIR__ . 'modifier_projet.php/' . $nouvelleImage)) {
                unlink(__DIR__ . 'modifier_projet.php/' . $nouvelleImage);
            }
            $erreurs[] = 'Erreur lors de la mise à jour : ' . $e->getMessage();
        }
    }
}
?>


<div class="ajouterBody">
    <section class="ajouterFormSection">

        <h2>Modifier « <?= htmlspecialchars($projet['titre']) ?> »</h2>

        <?php if (!empty($erreurs)): ?>
            <ul class="erreurs">
                <?php foreach ($erreurs as $erreur): ?>
                    <li><?= htmlspecialchars($erreur) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form method="POST" action="modifier_projet.php?id=<?= $id ?>" enctype="multipart/form-data">

            <div class="champFormulaire">
                <label for="titre">Titre</label>
                <input type="text" id="titre" name="titre" maxlength="150" required
                       value="<?= htmlspecialchars($titre) ?>">
            </div>

            <div class="champFormulaire">
                <label for="date_realisation">Date de réalisation</label>
                <input type="date" id="date_realisation" name="date_realisation" required
                       max="<?= date('Y-m-d') ?>"
                       value="<?= htmlspecialchars($dateReal) ?>">
            </div>

            <div class="champFormulaire">
                <label>Image</label>

                <?php if ($imageActuelle): ?>
                    <div class="imageActuelle">
                        <img src="<?= htmlspecialchars($imageActuelle) ?>"
                             alt="Image actuelle"
                             class="apercuImageActuelle">
                        <label class="checkboxLabel">
                            <input type="checkbox" name="supprimer_image" value="1">
                            Supprimer l'image actuelle
                        </label>
                    </div>
                <?php endif; ?>

                <input type="file" id="image" name="image" accept="image/*" class="inputFichier">
                <span class="hint">Laisser vide pour conserver l'image actuelle. Uploader une nouvelle image remplace l'ancienne.</span>
            </div>

            <div class="champFormulaire">
                <label for="desc_courte">Description courte <span class="hint">(affichée sur la carte)</span></label>
                <input type="text" id="desc_courte" name="desc_courte" maxlength="300" required
                       value="<?= htmlspecialchars($descCourte) ?>">
            </div>

            <div class="champFormulaire">
                <label for="desc_longue">Description longue <span class="hint">(affichée dans la modale)</span></label>
                <textarea id="desc_longue" name="desc_longue" rows="6"
                          required><?= htmlspecialchars($descLongue) ?></textarea>
            </div>

            <div class="champFormulaire">
                <label>Tags</label>
                <div class="checkboxTags">
                    <?php foreach ($tousLesTags as $tag): ?>
                        <label class="checkboxLabel">
                            <input type="checkbox" name="tags[]" value="<?= $tag['id'] ?>"
                                   <?= in_array($tag['id'], $tagsChoisis) ? 'checked' : '' ?>>
                            <?= htmlspecialchars($tag['nom']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="champFormulaire">
                <label for="nbPers"><i class="bi bi-person"></i> Nombre de personnes <span class="hint">(optionnel)</span></label>
                <input
                    type="number"
                    id="nbPers"
                    name="nbPers"
                    min="1"
                    max="255"
                    value="<?= htmlspecialchars($nbPersonnes) ?>">
            </div>

            <div class="champFormulaire">
                <label for="timeUsed"><i class="bi bi-hourglass-bottom"></i> Temps de réalisation <span class="hint">(optionnel — ex: "3 semaines", "2 mois")</span></label>
                <input
                    type="text"
                    id="timeUsed"
                    name="timeUsed"
                    maxlength="128"
                    value="<?= htmlspecialchars($tempsReal) ?>">
            </div>

            <label class="checkboxLabel checkboxPhare">
                <input
                    type="checkbox"
                    name="projet_phare"
                    value="1"
                    <?= $projetPhare ? 'checked' : '' ?>>
                <i class="bi bi-star-fill"></i> Projet phare <span class="hint"></span>
            </label>

            <div class="actionsFormulaire">
                <button type="submit" class="btnSubmit">Enregistrer les modifications</button>
            </div>

        </form>

        <form method="POST" action="modifier_projet.php?id=<?= $id ?>"
              class="formSupprimer"
              onsubmit="return confirm('Supprimer définitivement ce projet ? Cette action est irréversible.')">
            <input type="hidden" name="action" value="supprimer">
            <button type="submit" class="btnSupprimer">Supprimer ce projet</button>
        </form>

    </section>
</div>

<?php include "footer.php" ?>
</body>
</html>
