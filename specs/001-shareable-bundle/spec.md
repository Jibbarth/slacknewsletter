# Feature Specification: Shareable Bundle

**Feature Branch**: `[001-shareable-bundle]`

**Created**: 2026-09-13

**Status**: Draft

**Input**: User description: "Je souhaite transformer cette app en App Shareable (aka transformation en bundle, mais toujours executable en stand alone)"

## Summary

Cette application de newsletter est aujourd'hui un projet isolé : pour la réutiliser ailleurs, il faut la copier ou la forker. L'objectif est de la transformer en **composant réutilisable** que d'autres projets peuvent installer et utiliser, tout en conservant la possibilité de la lancer depuis ce dépôt comme aujourd'hui. Bénéfice attendu : réutilisation simple, pas de duplication de code, mises à jour centralisées.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Publier le composant (Priority: P1)

Le mainteneur veut publier le composant afin que d'autres projets puissent l'installer et accéder aux fonctionnalités existantes.

**Why this priority**: Permet la réutilisation et évite les forks.

**Independent Test**: Un nouveau projet vierge peut installer le composant via le gestionnaire de paquets et accéder à la fonctionnalité d'origine (page d'accueil et commandes).

**Acceptance Scenarios**:

1. **Given** un nouveau projet vierge, **When** le mainteneur installe le composant avec le gestionnaire de paquets, **Then** le composant est enregistré automatiquement dans la configuration du projet.
2. **Given** le projet consommateur, **When** un utilisateur accède à l'URL racine, **Then** la réponse est la page d'accueil d'origine du composant.
3. **Given** le projet consommateur, **When** un utilisateur liste les commandes disponibles, **Then** les commandes du composant (browse, build, send) sont visibles et exécutables.

---

### User Story 2 - Lancer le composant de manière autonome (Priority: P2)

Le dépôt d'origine doit rester exécutable de manière autonome après transformation, sans configuration supplémentaire.

**Why this priority**: Le mainteneur garde son flux de travail actuel.

**Independent Test**: Lancer le serveur depuis le dépôt démarre l'application sans configuration additionnelle.

**Acceptance Scenarios**:

1. **Given** la racine du dépôt, **When** le mainteneur démarre le serveur, **Then** le serveur démarre sans erreur et la route par défaut renvoie la page d'accueil du composant.

---

### User Story 3 - Mettre à jour sans casser les consommateurs (Priority: P3)

Quand le mainteneur ajoute une nouvelle fonctionnalité au composant, les projets consommateurs existants doivent continuer à fonctionner.

**Why this priority**: Garantit la stabilité pour les utilisateurs en aval.

**Independent Test**: Après un changement rétro-compatible, les projets consommateurs passent toujours leurs tests fonctionnels.

**Acceptance Scenarios**:

1. **Given** un projet consommateur existant, **When** le mainteneur publie une nouvelle version mineure, **Then** la mise à jour s'effectue sans casser les routes ni les fonctionnalités existantes du consommateur.

---

### Edge Cases

- Conflit de namespace : le namespace du composant entre en collision avec des classes propres au consommateur. → Le namespace du composant doit être spécifique au vendeur pour minimiser ce risque ; la documentation explique comment le gérer.
- Surcharge de configuration : le consommateur veut personnaliser le comportement (templates, paramètres). → Le composant doit exposer des points de personnalisation documentés.
- Publication des ressources publiques : les fichiers statiques du composant ne doivent pas écraser ceux du consommateur. → Les ressources sont isolées dans un répertoire dédié du composant.
- Installation en échec : le gestionnaire de paquets ne trouve pas le composant (réseau, version). → Message d'erreur clair du gestionnaire ; guide de dépannage dans la documentation.
- Variables d'environnement manquantes chez le consommateur. → Erreur explicite au chargement, liste des variables requises documentée.
- Mise à jour majeure avec changements cassants. → Politique de versioning documentée (report des changements cassants aux versions majeures).

## Requirements *(mandatory)*

### Scope

**Included**:
- Transformation du dépôt en composant distribuable.
- Maintien de l'exécution autonome depuis le dépôt.
- Documentation d'installation et de personnalisation.

**Out of scope**:
- Modification du comportement fonctionnel existant (collecte, construction, envoi de la newsletter).
- Évolution du modèle de données ou des stockages existants.
- Migration ou compatibilité des données entre projets.
- Nouvelle interface utilisateur ou nouvelles commandes.

### Functional Requirements

- **FR-001**: Le système MUST permettre l'installation du composant via un gestionnaire de paquets standard (dépôt local pour le développement, registre public pour la distribution).
- **FR-002**: Le système MUST isoler le code du composant dans un namespace spécifique au vendeur, distinct de celui des projets consommateurs.
- **FR-003**: Le système MUST s'enregistrer automatiquement dans un projet consommateur au moment de l'installation.
- **FR-004**: Le système MUST charger ses services, sa configuration et ses routes sans intervention manuelle du consommateur (hormis les points de personnalisation documentés).
- **FR-005**: Le système MUST continuer à fonctionner comme application autonome dans le dépôt d'origine (serveur local et commandes).
- **FR-006**: Le système MUST documenter les étapes d'installation (dépôt local et registre public), la configuration requise et les points de personnalisation.
- **FR-007**: Le système MUST publier ses ressources publiques sans écraser celles du consommateur.

### Key Entities

- **Composant réutilisable** : le paquet distribué, installable depuis un autre projet.
- **Projet consommateur** : tout projet qui installe et utilise le composant.
- **Application autonome** : le dépôt d'origine, toujours exécutable seul.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Un nouveau projet vierge peut installer le composant et obtenir une page d'accueil fonctionnelle en moins de 5 minutes en suivant le README.
- **SC-002**: Le démarrage du serveur dans le dépôt d'origine ne produit aucune erreur sur un environnement propre.
- **SC-003**: Après une version mineure, les projets consommateurs existants mettent à jour sans échec de test fonctionnel (0 % de régression).
- **SC-004**: Couverture de documentation ≥ 90 % évaluée par un tiers sur la base d'une checklist de revue.

## Assumptions

- Le gestionnaire de paquets est disponible sur les machines des développeurs.
- Aucun autoloading personnalisé au-delà du standard du langage.
- Les consommateurs utilisent le mécanisme d'enregistrement standard des composants du framework.
- La publication des ressources repose sur le mécanisme par défaut du framework.