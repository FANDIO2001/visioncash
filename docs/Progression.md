# Progression du Projet VisionCash

## Fonctionnalités Implémentées

### ✅ Inscription (Registration)
- **Statut**: Complété
- **Endpoint**: `POST /api/v1/auth/register`
- **Date**: 29 mai 2026

#### Fichiers Créés/Modifiés:
- `database/migrations/2026_05_29_084755_add_phone_number_to_users_table.php` - Migration pour ajouter phone_number
- `app/Http/Requests/RegisterRequest.php` - Validation des données d'inscription
- `app/Http/Resources/UserResource.php` - Transformation JSON des données utilisateur
- `app/Http/Controllers/AuthController.php` - Contrôleur d'authentification
- `routes/api.php` - Route d'inscription ajoutée
- `app/Models/User.php` - Ajout de phone_number et implémentation de MustVerifyEmail

#### Champs Requis:
- `first_name` (prénom)
- `last_name` (nom)
- `email`
- `phone_number` (numéro de téléphone)
- `password` (mot de passe, min 8 caractères)
- `password_confirmation`

#### Notes:
- L'envoi d'email de verification est activé avec implémentation personnalisée pour API
- La validation des données est implémentée via RegisterRequest
- La réponse JSON utilise UserResource pour une structure cohérente
- Un email de vérification est envoyé automatiquement après l'inscription

### ✅ Connexion (Login)
- **Statut**: Complété
- **Endpoint**: `POST /api/v1/auth/login`
- **Date**: 29 mai 2026

#### Fichiers Créés/Modifiés:
- `app/Http/Requests/LoginRequest.php` - Validation des données de connexion
- `app/Http/Controllers/AuthController.php` - Méthode login ajoutée avec génération de tokens
- `routes/api.php` - Route de connexion ajoutée

#### Champs Requis:
- `email`
- `password`

#### Tokens Générés:
- **Access Token**: Expire après 15 minutes
- **Refresh Token**: Expire après 30 jours

#### Notes:
- Utilise Laravel Sanctum pour la gestion des tokens
- Les tokens existants sont supprimés avant d'en créer de nouveaux
- La réponse inclut les tokens avec leurs dates d'expiration et les données utilisateur

### ✅ Déconnexion (Logout)
- **Statut**: Complété
- **Endpoint**: `POST /api/v1/auth/logout`
- **Date**: 29 mai 2026

#### Fichiers Créés/Modifiés:
- `app/Http/Controllers/AuthController.php` - Méthode logout ajoutée
- `routes/api.php` - Route de déconnexion ajoutée avec middleware auth:sanctum

#### Authentification Requise:
- Header `Authorization: Bearer {access_token}`

#### Notes:
- Supprime tous les tokens de l'utilisateur (access et refresh tokens)
- Invalide les tokens côté serveur
- Nécessite d'être authentifié via le middleware sanctum

### ✅ Mot de passe oublié (Forgot Password)
- **Statut**: Complété
- **Endpoints**: 
  - `POST /api/v1/auth/forgot-password` - Demande de réinitialisation
  - `POST /api/v1/auth/reset-password` - Réinitialisation du mot de passe
- **Date**: 29 mai 2026

#### Fichiers Créés/Modifiés:
- `app/Http/Requests/ForgotPasswordRequest.php` - Validation de l'email
- `app/Http/Controllers/AuthController.php` - Méthodes forgotPassword et resetPassword ajoutées
- `routes/api.php` - Routes de réinitialisation ajoutées

#### Champs Requis (forgot-password):
- `email`

#### Champs Requis (reset-password):
- `email`
- `token`
- `password` (min 8 caractères)
- `password_confirmation`

#### Notes:
- Implémentation personnalisée pour API (pas de routes web)
- Token de réinitialisation expire après 10 minutes
- Token stocké dans la table `password_reset_tokens`
- Token envoyé par email via SMTP (notification Laravel)
- Email contient le token et les instructions de réinitialisation

### ✅ Vérification d'email (Email Verification)
- **Statut**: Complété
- **Endpoint**: `POST /api/v1/auth/verify-email`
- **Date**: 29 mai 2026

#### Fichiers Créés/Modifiés:
- `app/Notifications/EmailVerificationNotification.php` - Notification de vérification d'email
- `database/migrations/2026_05_29_105257_create_email_verification_tokens_table.php` - Migration pour la table de tokens
- `app/Http/Controllers/AuthController.php` - Méthodes register et verifyEmail modifiées
- `routes/api.php` - Route de vérification ajoutée

#### Champs Requis:
- `email`
- `token`

#### Notes:
- Implémentation personnalisée pour API (pas de routes web)
- Token de vérification expire après 60 minutes
- Token stocké dans la table `email_verification_tokens`
- Token envoyé par email via SMTP (notification Laravel)
- Email envoyé automatiquement après l'inscription
- Marque le champ `email_verified_at` dans la table users

### ✅ Renouvellement de session (Refresh Token)
- **Statut**: Complété
- **Endpoint**: `POST /api/v1/auth/refresh-token`
- **Date**: 29 mai 2026

#### Fichiers Créés/Modifiés:
- `app/Http/Requests/RefreshTokenRequest.php` - Validation du refresh token
- `app/Http/Controllers/AuthController.php` - Méthode refreshToken ajoutée
- `routes/api.php` - Route de renouvellement ajoutée

#### Champs Requis:
- `refresh_token`

#### Notes:
- Permet de régénérer un access token sans reconnexion manuelle
- Valide le refresh token et vérifie son expiration
- Génère un nouveau access token (15 minutes)
- Génère un nouveau refresh token (30 jours)
- L'ancien refresh token est invalidé pour la sécurité

### ✅ Authentification 2FA (Two-Factor Authentication)
- **Statut**: Complété
- **Endpoints**: 
  - `POST /api/v1/auth/2fa/enable` - Activer 2FA
  - `POST /api/v1/auth/2fa/verify` - Vérifier 2FA
  - `POST /api/v1/auth/2fa/disable` - Désactiver 2FA
- **Date**: 29 mai 2026

#### Fichiers Créés/Modifiés:
- `database/migrations/2026_05_29_113645_create_two_factor_auth_settings_table.php` - Migration pour la table 2FA
- `app/Models/TwoFactorAuthSettings.php` - Modèle 2FA
- `app/Models/User.php` - Relation twoFactorAuthSettings ajoutée
- `app/Http/Requests/Enable2FARequest.php` - Validation pour activer 2FA
- `app/Http/Requests/Verify2FARequest.php` - Validation pour vérifier 2FA
- `app/Http/Controllers/AuthController.php` - Méthodes enable2FA, verify2FA, disable2FA ajoutées
- `routes/api.php` - Routes 2FA ajoutées
- `composer.json` - Packages pragmarx/google2fa-laravel et bacon/bacon-qr-code ajoutés

#### Champs Requis (enable):
- `method` (sms ou totp)
- `phone_number` (requis si method=sms)

#### Champs Requis (verify):
- `code` (6 chiffres)

#### Notes:
- Support TOTP via Google Authenticator
- Support SMS (OTP envoyé par SMS - à implémenter complètement)
- QR code généré pour configuration TOTP
- Optionnel en Phase 1, obligatoire en Phase 2
- Stockage des secrets TOTP sécurisé

### ✅ Voir le profil (View Profile)
- **Statut**: Complété
- **Endpoint**: `GET /api/v1/profile`
- **Date**: 29 mai 2026

#### Fichiers Créés/Modifiés:
- `app/Http/Controllers/ProfileController.php` - Contrôleur de profil
- `routes/api.php` - Route de profil ajoutée

#### Authentification Requise:
- Header `Authorization: Bearer {access_token}`

#### Informations Affichées:
- `id`
- `first_name` (prénom)
- `last_name` (nom)
- `email`
- `phone_number` (numéro de téléphone)
- `avatar_url` (URL de l'avatar)
- `default_currency` (devise par défaut)
- `email_verified_at` (date de vérification d'email)
- `created_at` (date de création)
- `updated_at` (date de dernière mise à jour)

#### Notes:
- Nécessite d'être authentifié via le middleware sanctum
- Affiche les informations personnelles de l'utilisateur connecté

### ✅ Modifier le profil (Update Profile)
- **Statut**: Complété
- **Endpoint**: `PUT /api/v1/profile`
- **Date**: 29 mai 2026

#### Fichiers Créés/Modifiés:
- `app/Http/Requests/UpdateProfileRequest.php` - Validation de la mise à jour
- `app/Http/Controllers/ProfileController.php` - Méthode update ajoutée
- `routes/api.php` - Route de mise à jour ajoutée

#### Authentification Requise:
- Header `Authorization: Bearer {access_token}`

#### Champs Modifiables (tous optionnels):
- `first_name` (prénom)
- `last_name` (nom)
- `phone_number` (numéro de téléphone)
- `avatar_url` (URL de l'avatar)

#### Notes:
- Nécessite d'être authentifié via le middleware sanctum
- Seuls les champs fournis sont mis à jour (mise à jour partielle)
- Validation des données avant mise à jour

### ✅ Changer de mot de passe (Change Password)
- **Statut**: Complété
- **Endpoint**: `POST /api/v1/profile/change-password`
- **Date**: 29 mai 2026

#### Fichiers Créés/Modifiés:
- `app/Http/Requests/ChangePasswordRequest.php` - Validation du changement de mot de passe
- `app/Http/Controllers/ProfileController.php` - Méthode changePassword ajoutée
- `routes/api.php` - Route de changement de mot de passe ajoutée

#### Authentification Requise:
- Header `Authorization: Bearer {access_token}`

#### Champs Requis:
- `current_password` (ancien mot de passe)
- `password` (nouveau mot de passe, minimum 8 caractères)
- `password_confirmation` (confirmation du nouveau mot de passe)

#### Notes:
- Nécessite d'être authentifié via le middleware sanctum
- Vérification de l'ancien mot de passe avant le changement
- Validation de la complexité du nouveau mot de passe (min 8 caractères)
- Confirmation du nouveau mot de passe requise

### ✅ Choisir la devise (Select Currency)
- **Statut**: Complété
- **Endpoint**: `POST /api/v1/profile/select-currency`
- **Date**: 29 mai 2026

#### Fichiers Créés/Modifiés:
- `app/Http/Requests/SelectCurrencyRequest.php` - Validation de la sélection de devise
- `app/Http/Controllers/ProfileController.php` - Méthode selectCurrency ajoutée
- `routes/api.php` - Route de sélection de devise ajoutée

#### Authentification Requise:
- Header `Authorization: Bearer {access_token}`

#### Champs Requis:
- `currency` (XAF, EUR, USD, GBP)

#### Notes:
- Nécessite d'être authentifié via le middleware sanctum
- Devises disponibles: XAF, EUR, USD, GBP
- Devise stockée dans le champ `default_currency` de l'utilisateur
- Conversion automatique si multi-devises (à implémenter dans les transactions)

### ✅ Langue de l'interface (Select Language)
- **Statut**: Complété
- **Endpoint**: `POST /api/v1/profile/select-language`
- **Date**: 29 mai 2026

#### Fichiers Créés/Modifiés:
- `app/Http/Requests/SelectLanguageRequest.php` - Validation de la sélection de langue
- `app/Http/Controllers/ProfileController.php` - Méthode selectLanguage ajoutée
- `routes/api.php` - Route de sélection de langue ajoutée
- `database/migrations/2026_05_29_215926_add_preferred_language_to_users_table.php` - Migration pour ajouter le champ preferred_language

#### Authentification Requise:
- Header `Authorization: Bearer {access_token}`

#### Champs Requis:
- `language` (fr, en)

#### Notes:
- Nécessite d'être authentifié via le middleware sanctum
- Langues disponibles: fr (Français), en (Anglais)
- Langue stockée dans le champ `preferred_language` de l'utilisateur
- Possibilité d'étendre à d'autres langues en modifiant la validation

### ✅ Supprimer le compte (Delete Account)
- **Statut**: Complété
- **Endpoint**: `DELETE /api/v1/profile`
- **Date**: 29 mai 2026

#### Fichiers Créés/Modifiés:
- `app/Http/Requests/DeleteAccountRequest.php` - Validation de la suppression de compte
- `app/Http/Controllers/ProfileController.php` - Méthode deleteAccount ajoutée
- `routes/api.php` - Route de suppression de compte ajoutée

#### Authentification Requise:
- Header `Authorization: Bearer {access_token}`

#### Champs Requis:
- `password` (mot de passe pour confirmation)

#### Notes:
- Nécessite d'être authentifié via le middleware sanctum
- Suppression irréversible après confirmation par mot de passe
- Tous les tokens sont invalidés
- Compte supprimé avec soft delete (les données restent en base mais marquées comme supprimées)
- Toutes les données sont effacées de manière logique

### ✅ Exporter mes données (Export Data)
- **Statut**: Complété
- **Endpoint**: `GET /api/v1/profile/export`
- **Date**: 29 mai 2026

#### Fichiers Créés/Modifiés:
- `app/Http/Controllers/ProfileController.php` - Méthode exportData ajoutée
- `routes/api.php` - Route d'export ajoutée

#### Authentification Requise:
- Header `Authorization: Bearer {access_token}`

#### Données Exportées:
- `user` - Informations personnelles de l'utilisateur
- `accounts` - Liste des comptes bancaires
- `two_factor_auth_settings` - Paramètres 2FA
- `export_date` - Date de l'export

#### Notes:
- Nécessite d'être authentifié via le middleware sanctum
- Export de l'ensemble des données personnelles au format JSON
- Conformité RGPD (droit à la portabilité des données)
- Permet à l'utilisateur de récupérer toutes ses données

## À Faire

- [ ] Ajouter tests unitaires pour l'inscription et la connexion