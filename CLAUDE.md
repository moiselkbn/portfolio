# CLAUDE.md

Contexte du projet Portfolio 2026 pour Claude Code. Lu automatiquement à chaque session.

## Sources de vérité
En cas de contradiction entre les trois : **CLAUDE.md > Figma > Notion.**
Exception documentée : pour la **valeur** des jetons de design (couleurs, tailles, espacements), c'est Figma qui fait autorité — voir section Interface. Cette exception ne s'étend à rien d'autre.
**Toute nouvelle décision ou tout changement de règle est répercuté immédiatement dans Notion et dans ce fichier — jamais dans un seul des deux.**

## Le projet
Portfolio 2026 — site vitrine qui présente mes projets à des recruteurs et clients potentiels, avec un moyen de me contacter.
Statut : MVP. Échéance : 30 septembre 2026 (deadline ferme).
Contrainte forte : stack imposée en PHP.
Hors périmètre : pas de mode sombre.

## Stack
- PHP 8.3.40, CSS, JavaScript, SQL
- Aucun framework front ni back : tout en vanilla
- Aucun gestionnaire de paquets, aucun bundler
- CSS en BEM, jetons de design dans `public/assets/style/style.css` (bloc `:root`)
- Base de données : MariaDB/MySQL, nom local `portfolio_2026_v3`
- Accès aux données : PDO direct, requêtes préparées, aucun ORM
- Environnement local : MAMP, port 8888
- Hébergement : O2Switch (mutualisé), déploiement manuel par FTP avec FileZilla
- Aucune intégration continue, aucun service d'infrastructure (cache, file d'attente, stockage externe)
- Ne jamais installer de dépendance externe sans demander — le but du projet est de tout faire en vanilla

## Commandes
Pas d'outillage : aucun test, aucun linter, aucun build, aucune migration.
Vérification après chaque modification : recharger la page et contrôler qu'il n'y a aucune erreur dans la console du navigateur ni dans les logs PHP.
Déploiement : envoi manuel par FTP (FileZilla) vers O2Switch.

## Architecture
- Point d'entrée : `public/index.php`
- Nouveau composant réutilisable → `includes/components/`
- `medias/` : médias compressés, un sous-dossier par projet
- Pas de pattern d'architecture nommé (rangement à l'instinct) : respecter l'existant plutôt que d'en imposer un nouveau
- Flux de données pas encore figé — demander avant de supposer une structure

## Routes
- 5 routes publiques : `/` · `/projects/{slug}` · `/about` · `/lab` · `/404`
- Contact : pas de route dédiée — ancre `#contact`, le formulaire vit dans le `<footer>`
- Routes admin (`/admin/*`) : encore à déterminer, demander avant d'en créer une

## Conventions
- Formatage : Prettier
- Imports en chemin absolu, jamais relatif
- Commentaires : expliquer le pourquoi, pas le comment, en anglais
- Une fonction qui dépasse ~20 lignes doit être découpée
- Interdit : valeur écrite en dur dans une condition sans raison de lisibilité ; `!important` en CSS sauf cas de force majeure
- Imposé : toute requête SQL est préparée, systématiquement (PDO, jamais de SQL concaténé)

## Nommage
- Dossiers : au pluriel
- Composants et classes : PascalCase
- Variables, fonctions, booléens : camelCase
- Constantes : SCREAMING_SNAKE_CASE
- Classes CSS : BEM
- Tables de base de données : en anglais, au singulier (`project`, pas `projects` ni `projet`)
- Abréviations tolérées uniquement si universelles (`btn`, `img`, `nav`)

## Git
- Tout se passe sur `main`, pas de branches (projet solo)
- Ne jamais commiter spontanément : proposer le message, attendre la validation, puis exécuter

## Tests
Aucun test dans ce projet.

## Base de données
- Moteur : MySQL, base locale `portfolio_2026_v3`
- Deux tables maximum : aucune table supplémentaire sans besoin réel démontré
- `project` : id, slug (généré automatiquement depuis le titre), title, year, cover_image, gallery,
  context, role, result, decisions, annex_stack, annex_repo_url, annex_retro, status,
  created_at, updated_at
- `admin_user` : id, username, password_hash, created_at
- `year` est un VARCHAR : accepte une année seule (`2025`) ou une plage (`2023–2026`) pour un projet repris
- `status` est un ENUM `draft` | `lab` | `featured` — un seul champ, trois états mutuellement exclusifs : `draft` (non publié, visible nulle part), `lab` (publié, grille de `/lab` en lightbox), `featured` (mis en avant sur l'accueil, avec page `/projects/{slug}` dédiée). Défaut : `lab`. Remplace les anciens booléens `is_featured` + `published` — décision du 9 septembre 2026, deux booléens autorisaient l'état incohérent « en avant mais non publié »
- `decisions`, `gallery` et `annex_stack` sont stockés en JSON — jamais `serialize()`
- `annex_retro` est stocké mais volontairement non affiché sur le site

## Sécurité
- Authentification par session PHP
- Deux comptes admin, mêmes permissions, aucun compte visiteur
- Mots de passe hachés avec `password_hash()` (bcrypt), jamais en clair
- Protections obligatoires : requêtes préparées (injection SQL), échappement systématique à l'affichage (XSS), jeton CSRF sur chaque formulaire
- Upload d'image : liste blanche d'extensions, vérification du type MIME réel via `finfo`, ré-encodage via GD, nom de fichier aléatoire, dossier d'upload sans droit d'exécution PHP
- Secrets (mot de passe base, clés) dans `.env`, jamais commités

## Configuration
- Fichier `.env` pour les valeurs d'environnement ; maintenir `.env.example` à jour à chaque nouvelle variable
- Variables indispensables : `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`
- Local : MAMP, port 8888 — projet dans `/Applications/MAMP/htdocs/PORTFOLIO_2026_V3`
- Production : https://moise.techniques-graphiques.be/

## Interface
Les jetons vivent dans `public/assets/style/style.css` (bloc `:root`) — toujours réutiliser ces variables plutôt qu'écrire une valeur en dur.
**En cas de divergence entre le CSS et le fichier Figma, Figma fait autorité sur les jetons.**
**Les noms CSS sont identiques aux noms des variables Figma** (`color/bg` → `--color-bg`, `space/1` → `--space-1`, `radius/base` → `--radius-base`, `ease/default` → `--ease`) — un seul vocabulaire entre les deux outils. Nommage sémantique : un jeton se nomme par son rôle, jamais par sa valeur.

- Palette strictement neutre, aucune couleur d'accent décorative — erreur et validation sont les seules couleurs, à usage fonctionnel uniquement
- `--color-bg` #FAFAFA · `--color-text` #1A1A1A · `--color-neutral-light` #D9D9D9 · `--color-neutral` #C4C4C4 · `--color-error` #E22D00 · `--color-success` #427536
- Focus : reprend `--color-text` — il n'existe volontairement pas de `--color-focus`. `outline: 3px solid` + `outline-offset: 2px`, ne jamais le supprimer
- Typographies : Instrument Serif pour les titres (une seule graisse, hiérarchie par la taille), Inter Tight pour le corps
- Titres desktop/mobile : 64/40 · 45/32 · 32/25 · 23/20 — `letter-spacing: -4%`, `line-height` 100 % sur H1 et 120 % ailleurs, interpolés en `clamp()` plutôt qu'un saut de breakpoint
- `Heading/Big H1 - 320` : taille hors échelle, réservée au chiffre géant de la page 404 (`ERROR404`) — ne pas l'utiliser ailleurs, ce n'est pas un palier de titre courant
- Corps : 12 / 14 / 16 / 20, `line-height` 120 % — le 12 est réservé aux méta-informations (légendes du Lab, copyright, statut de recherche) ; les labels de formulaire restent à 14
- Espacement (base 8) : `--space-0-5` 4 · `--space-1` 8 · `--space-2` 16 · `--space-3` 24 · `--space-4` 32 · `--space-5` 48 · `--space-6` 64
- Rayons : `--radius-none` 0 · `--radius-sm` 3 · `--radius-base` 6 · `--radius-lg` 12 · `--radius-full` 999
- Animations : 200 ms avec `--ease` = `cubic-bezier(0, 0, 0.23, 1)`
- États : survol doux ; `:active` inversé + `scale(0.97)` sans transition
- Pas d'ombre portée, pas de dégradé
- Responsive continu de 320 à 1920 px : breakpoints dictés par le contenu, jamais par un appareil

## Composants
Les composants existent déjà dans Figma (page « Prototype ») avec leurs états — ne pas en inventer d'autres au moment du code, et ne pas en omettre.
- `Nav Item` : Default · Hover · Focus · Active
- `Button` : Default · Hover · Focus · Active · Disabled (croisés avec External / Icon / Device)
- `Input` : tailles Small / Medium / Huge × états Default / Focus / Error
- `Accordeon` : Default · Active — **ouvert par défaut** sur la page projet (les décisions doivent être lisibles sans interaction)
- `Chip`, `Navbar`, `Icons`

## Formulaires
- Chaque champ a un **label visible associé** — jamais un placeholder seul, qui disparaît dès la saisie
- Les erreurs s'affichent **à la soumission**, en `--color-error`, à côté du champ concerné
- `disabled` est réservé à l'état « envoi en cours » (anti double-clic). **Jamais** pour « formulaire incomplet » : un bouton grisé n'explique pas ce qui manque et sort de la navigation clavier

## Contenu éditorial
- Langue du site : **anglais** (`<html lang="en">`). Cible principale en Belgique, mais le code, les commentaires et les identifiants sont déjà en anglais (voir Conventions) — un site unilingue anglais est cohérent avec ça, et l'anglais est la langue de travail standard du secteur tech, y compris pour un public belge francophone/néerlandophone. Pas de bascule FR/EN : un site fini dans une langue plutôt qu'à moitié traduit dans deux.
- Aucune mention croisée entre projets : un projet ne cite jamais un autre projet du portfolio, le visiteur n'a aucun moyen de savoir de quoi il s'agit

## Accessibilité, médias et performance
- Niveau visé : WCAG AA
- Non négociable : contraste 4,5:1 minimum, focus visible sur tout élément interactif, navigation clavier complète, texte alternatif sur toute image porteuse de sens
- Images : WebP, moins de 300 Ko chacune, compressées manuellement avant upload (aucun script d'optimisation dans ce projet)
- Vidéos : WebM, sans fallback MP4
- Budget de poids par page : à trancher — ne pas inventer de chiffre, demander
- Lighthouse visé : 90+ en performance et en accessibilité

## À ne jamais faire sans validation
- Supprimer un fichier
- Modifier la structure de la base de données
- Renommer un fichier
- Toucher à la configuration du projet (`.htaccess`, etc.)
- Installer une dépendance

## Méthode de travail attendue
- Répondre en français, sur un ton pédagogique : expliquer pourquoi, pas seulement quoi
- Proposer un plan avant toute modification, et attendre la validation avant d'écrire quoi que ce soit
- Avancer par petites étapes, pas de livraison en un seul bloc
- Face à un doute, s'arrêter et demander plutôt que de supposer
- Expliquer en quelques lignes chaque changement livré
- Expliquer chaque ligne de code livrée (syntaxe, fonctions natives, choix) — l'auteur est débutant et doit pouvoir défendre chaque ligne
- Tenir à jour la page Notion « Documentation et apprentissage » : dès qu'une nouvelle notion de code est introduite (fonction native, fonction du projet, fichier, dossier, extension, concept d'architecture, notion SQL ou de sécurité), l'y inscrire dans la bonne catégorie, en français, de façon pédagogique (lisible par un débutant), et compléter la section « Historique des ajouts ». Cette documentation vit **uniquement dans Notion**, jamais en fichiers dans le dépôt.
- Annoter les `TODO` / `FIXME` laissés dans le code et les récapituler
- Ne pas créer de nouveau fichier sans demande explicite
- Ne pas modifier la configuration du projet sans validation

## Ressources
- Dépôt : https://github.com/moiselkbn/portfolio
- Suivi des tâches et documentation : Notion, page « Portfolio 2026 V2 »
- Apprentissage (chaque notion de code expliquée) : Notion, page « Documentation et apprentissage » — https://app.notion.com/p/3d64ddccb1cc80cab5dfdd47553784ea
- Maquettes et jetons de design : Figma, fichier « Portfolio 2026 v2 »

## Terminé signifie
La page s'affiche sans erreur (console navigateur et logs PHP vides), le rendu a été vérifié dans le navigateur, l'accessibilité de base est respectée (navigation clavier, focus visible, texte alternatif sur les images), aucune trace de débogage ne reste (`var_dump`, `console.log`).
