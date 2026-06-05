document.addEventListener('DOMContentLoaded', function () {
    /* --- Modale --- */
    const modal      = document.getElementById('projectModal');
    const modalTitre = document.getElementById('modalTitre');
    const modalDesc  = document.getElementById('modalDescText');
    const modalTags  = document.querySelector('.modalTags');
    const btnFermer  = document.getElementById('modalClose');
    /* --- Filtrage --- */
    const searchBar      = document.getElementById('searchProjects');
    const filtres        = document.querySelectorAll('.filter');       /* les <p class="filter"> dans l'aside */
    const aucunResultat  = document.getElementById('aucunResultat');
    /* --- Cartes --- */
    const projets = document.querySelectorAll('.project');
    const tagsActifs = new Set();

    function appliquerFiltres() {

        const recherche = searchBar.value.toLowerCase().trim();

        let nbVisibles = 0;

        projets.forEach(function (projet) {

            const titre = projet.dataset.titre.toLowerCase();
            const desc  = projet.dataset.desc.toLowerCase();

            const tags = projet.dataset.tags
                .split(',')
                .map(function (t) { return t.trim().toLowerCase(); });

            const correspondTexte = titre.includes(recherche) || desc.includes(recherche);

            const correspondTag = tagsActifs.size === 0 ||
                [...tagsActifs].every(function (t) { return tags.includes(t.toLowerCase()); });

            if (correspondTexte && correspondTag) {
                projet.style.display = 'block';
                nbVisibles++;
            } else {
                projet.style.display = 'none';
            }
        });
        aucunResultat.style.display = nbVisibles === 0 ? 'block' : 'none';
    }


    if (searchBar) {
        searchBar.addEventListener('input', function () {
            appliquerFiltres();
        });
    }


    filtres.forEach(function (filtre) {
        filtre.addEventListener('click', function () {

            const tagClique = filtre.dataset.tag;

            if (tagsActifs.has(tagClique)) {
                tagsActifs.delete(tagClique);
                filtre.classList.remove('filterActif');
            } else {
                tagsActifs.add(tagClique);
                filtre.classList.add('filterActif');
            }

            appliquerFiltres();
        });
    });

    const modalImage      = document.getElementById('modalImage');
    const modalDate       = document.getElementById('modalDate');
    const modalNbPers     = document.getElementById('modalNbPersonnes');
    const modalTemps      = document.getElementById('modalTemps');

    projets.forEach(function (projet) {
        projet.addEventListener('click', function () {

            const titre       = projet.dataset.titre;
            const tags        = projet.dataset.tags;
            const image       = projet.dataset.image;
            const date        = projet.dataset.date;
            const nbPersonnes = projet.dataset.nbPersonnes;  /* data-nb-personnes → camelCase auto */
            const temps       = projet.dataset.temps;

            modalTitre.textContent = titre;

            /* Date — toujours présente */
            modalDate.innerHTML = '<i class="bi bi-calendar3"></i> ' + date;

            /*
                Nb personnes et temps : on affiche le span uniquement si la valeur
                existe (non vide). On remet la classe "cache" sinon pour le masquer.
            */
            if (nbPersonnes) {
                modalNbPers.innerHTML = '<i class="bi bi-person"></i> ' + nbPersonnes;
                modalNbPers.classList.remove('cache');
            } else {
                modalNbPers.classList.add('cache');
            }

            if (temps) {
                modalTemps.innerHTML = '<i class="bi bi-hourglass-bottom"></i> ' + temps;
                modalTemps.classList.remove('cache');
            } else {
                modalTemps.classList.add('cache');
            }

            const template = projet.querySelector('.descLongueTemplate');
            modalDesc.innerHTML = template ? template.innerHTML : '';

            if (image) {
                modalImage.src = image;
                modalImage.alt = titre;
                modalImage.classList.remove('cache');
            } else {
                modalImage.src = '';
                modalImage.classList.add('cache');
            }

            modalTags.innerHTML = '<p>Languages utilisés : </p>';
            if (tags) {
                tags.split(',').forEach(function (tag) {
                    const tagEl = document.createElement('p');
                    tagEl.classList.add('tag');
                    tagEl.textContent = tag.trim();
                    modalTags.appendChild(tagEl);
                });
            }

            modal.showModal();
        });
    });

    btnFermer.addEventListener('click', function () {
        modal.close();
    });

    modal.addEventListener('click', function (e) {
        if (e.target === modal) {
            modal.close();
        }
    });

});
