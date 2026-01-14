# Examen Laravel (MVC, Dades, Seguretat) · 3h

Aplica el que has vist als temes 03, 04 i 05 per construir un petit gestor de **projectes de programari** en Laravel **sense API, només web (Blade + controllers)**.


## Com començar (Sail + Docker)

1. Duplica `.env.example` a `.env`.
2. Executa:

```bash
  chmod 777 storage -R
   docker run --rm -v $(pwd):/app -w /app laravelsail/php84-composer:latest composer install
   ./vendor/bin/sail up -d
   ./vendor/bin/sail artisan key:generate
   ./vendor/bin/sail artisan migrate --seed
   docker run --rm -v $(pwd):/app -w /app node:20 npm install
   docker run --rm -v $(pwd):/app -w /app node:20 npm run build
```

3. Comprova: `http://localhost`. 
4. Implementa els casos d’ús demanats.


## Objectiu i requisits funcionals

- Llistar projectes amb paginació i estat visible/no visible.
- Detall d’un projecte amb equip, tecnologies i stock (p. ex. llicencies o disponibilitat).
- CRUD de projectes (crear, editar, eliminar) només per usuari autenticat. Alta/edició han de permetre seleccionar equip i múltiples tecnologies.
- Afegir nova entitat: crea model, migració i seeder per a `partners` amb camps `name` (string) i `country` (string nullable). Afegeix relació `projects.partner_id` (foreign key nullable) i mostra el partner al llistat/detall del projecte. Sembra almenys 2 partners i associa-les als projectes de prova.

## Requisits tècnics (allò que s’avalua)

- **MVC**: `routes/web.php`, controllers amb resource (route model binding), models amb relacions i `$fillable`.
- **Dades**: migracions i seeders preparades. Pots modificar-les si ho necessites. Usa Eloquent/Query Builder per a consultes i paginacio.
- **Seguretat**:
  - Formularis amb validació servidor (`FormRequest` o `validate()`), missatges d’error i `old()`.
  - Autenticació amb guard per defecte (sessions). Login/logout amb `Auth::attempt()`. Usa l’usuari sembrat.
  - Autoria/autoriazació: policy o gate (p. ex. `ProjectPolicy`). Només rol `admin` pot eliminar; crear/editar almenys per rol `admin` (o `user` autenticat, segons la teua política).
  - Protegeix mass assignment (`$fillable`) i CSRF (directiva Blade).
- **Presentació**: Blade net, layout comú, flash messages (èxit/errades). No cal estil boig; funcional i clar.

## Dades inicials

Ja tens migracions i models per a:

- `teams` (equips) (`name`, `country`, `bio`)
- `technologies` (tecnologies) (`name`)
- `projects` (projectes) (`title`, `publication_year`, `price`, `stock`, `description`, `is_visible`, `team_id`) amb pivot `project_technology`
- `users` amb camp `role` (`admin`/`user`)
- Seeder crea:
  - Usuari `admin@example.test` / password `secret` (rol `admin`)
  - 3 equips, 4 tecnologies, 4 projectes relacionats

## Checklist ràpida (submergeix-te en 3h)

- [ ] Rutes web organitzades (`Route::resource('projects', ...)`), middlewares `auth`/`can`.
- [ ] Controllers amb validació, redireccions amb missatges, ús de policies.
- [ ] Vistes Blade per llistat, detall, formularis create/edit (amb errors i `old()`).
- [ ] Login/logout bàsic i enllaços de navegació condicionals.
- [ ] Paginacio a l’index.
- [ ] Control d’autoriazació per a accions protegides.
- [ ] Nova entitat `publishers`: migració, model, seeder, relació amb projectes i visualització al llistat/detall.

## Avaluació (50% funcional, 30% codi, 20% seguretat)

- Funcional: llista paginada, detall, CRUD, navegació i missatges.
- Codi: ús correcte de MVC, neteja, reutilització de layout/components, ús d’Eloquent i Route Model Binding; inclou la nova entitat `publishers`.
- Seguretat: validació, CSRF, mass assignment, auth + policy/gate per rol.
- Es valora (opcional): CRUD d’equips/tecnologies, tests bàsics, paginació personalitzada, estat visible.

## Notes

- Pots crear `FormRequest`, `Policy`, seeders addicionals o middlewares si ho necessites.

 ## Ordre suggerida

- Dona d'alta les rutes i el controlador ProjectController (resource) per accedir a les vistes de projects
- Modifica la vista wellcome per afegir la ruta al login en el botó d'entrar
- Modifica la vista projects/index per a que mostre els projectes de la bbdd i habilita els enllaços
- Fes que la ruta dashboard rediriguisca al index de projectController.
- Crea els 
- Prepara models i relacions (Project, Team, Technology) i comprova els fillable/casts
- Afegeix la nova entitat `publishers`: migracio, model, seeder i relacio a projects
- Ajusta el seeder de projectes per associar equips, tecnologies i partner
- Implementa la paginacio a l'index
- Monta formularis create/edit amb validacio i missatges d'error + old()
- Implementa auth login/logout i enllaços de navegacio condicionals
- Afegeix policy/gate per crear/editar/eliminar (rol admin)
- Revisa flash messages i flux complet CRUD
# projects
