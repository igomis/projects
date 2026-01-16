# SA5. Aplicacions segures i reactives en Laravel

??? abstract "Duració i criteris d'avaluació"

    Duració estimada: 24 hores

    <hr />

    | Resultat d'aprenentatge  | Criteris d'avaluació  |
    | ------                   | -----                 |
    | RA6.-Genera pàgines web dinàmiques analitzant i utilitzant tecnologies i frameworks del servidor web que afigen codi al llenguatge de marques. | a) S'han identificat les diferències entre l'execució de codi en el servidor i en el client web. <br/>   b) S'han reconegut els avantatges d'unir totes dues tecnologies en el procés de desenvolupament de programes. <br/>   c) S'han identificat les tecnologies i frameworks relacionades amb la generació per part del servidor de pàgines web amb guions embeguts. <br/>d) S'han utilitzat aquestes tecnologies i frameworks per a generar pàgines web que incloguen interacció amb l'usuari.<br/> e) S'han utilitzat aquestes tecnologies i frameworks, per a generar pàgines web que incloguen verificació de formularis. <br/> f) S'han utilitzat aquestes tecnologies i frameworks per a generar pàgines web que incloguen modificació dinàmica del seu contingut i la seua estructura.<br/> g) S'han aplicat aquestes tecnologies i frameworks en la programació d'aplicacions web. |

## SA 5.1 Autenticació, hashing i autorització

### 🌬️🍃 Laravel Breeze: registre, login, logout

Laravel Breeze és el starter kit oficial més simple per implementar autenticació en Laravel. Inclou rutes, controladors i vistes per a registre, login i logout.

Per **instal·lar-lo**, cal usar els comandos corresponents per a afegir el paquet, generar el frontend i aplicar les **migracions**.

```bash
./vendor/bin/sail shell
composer require laravel/breeze --dev
php artisan breeze:install blade
npm install && npm run dev
php artisan migrate
``` 
**Recuperació de l'usuari autenticat**
Un cop l'usuari ha fet login, podem accedir a les seues dades:
```php
use Illuminate\Support\Facades\Auth;

// Usuari autenticat
$user = Auth::user();

// ID de l'usuari autenticat
$id = Auth::id();
```

També podem obtenir l’usuari des d'una **petició injectada al controlador**:
```php
public function dashboard(Request $request)
{
    $user = $request->user(); // Equivalent a Auth::user()
}
```

**Logout**
Laravel Breeze inclou logout preconfigurat:
```php
<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit">Logout</button>
</form>
```

**Restabliment de contrasenya**
Laravel Breeze genera automàticament els formularis i la lògica necessària, soles has de
configurar el correu SMTP al **fitxer `.env`**.
- 

**Hashing automàtic de contrasenyes**

Laravel utilitza el sistema `Hash` per a encriptar contrasenyes abans de guardar-les a la base de dades. Breeze ja ho implementa automàticament en el **registre**.

```php
use Illuminate\Support\Facades\Hash;

$user = User::create([
    'name' => $request->name,
    'email' => $request->email,
    'password' => Hash::make($request->password),
]);
```

### 🔒 Protecció

#### 🛣️ Protecció de rutes amb `auth`

Per a protegir rutes perquè només siguen accessibles per usuaris autenticats, es fa ús del middleware `auth`. Aquest es pot aplicar tant a **grups de rutes**:

```php
Route::middleware(['auth'])->group(function () {
    Route::resource('equips', EquipController::class);
});
```

com a **rutes individuals**:

```php
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth');
``` 
 
#### 🚦Protecció amb middlewares
 
##### 🔍 1.Què és un middleware?

Un middleware és una classe que actua com a intermediari entre una sol·licitud HTTP i la seua resposta. Permet executar lògica abans o després d’arribar al controlador.

S’usa per a:
 
- 🔐 Autenticació d’usuaris
- 🛡️ Validació de rols i permisos
- 🧼 Neteja o modificació de peticions/respostes

⚙️ Exemple senzill de **middleware**

```php
public function handle($request, Closure $next)
{
    if (Auth::check()) {
        return $next($request); // Continua amb la sol·licitud
    }
    return redirect('login'); // Redirigeix si no està autenticat
}
```

##### 🧩 2. Rols i permisos

Primer, hem d'afegir camp role a la taula users

1. Crear una **migració**:
   ```bash
   php artisan make:migration add_role_to_users_table --table=users
   ```
2. Afegir el **camp `role`**:
   ```php
   Schema::table('users', function (Blueprint $table) {
       $table->string('role')->default('user'); // Opcions: 'user', 'admin', etc.
   });
   ```
3. Actualitzar el **model `User`**:
   ```php
   class User extends Authenticatable
   {
       protected $fillable = ['name', 'email', 'password', 'role'];
   }
   ```

##### 🛡️ 3. Middleware personalitzat per a rols

1. Crear el **middleware**:
   ```bash
   php artisan make:middleware RoleMiddleware
   ```
2. Definir la **lògica**:
   ```php
   namespace App\Http\Middleware;

    use Closure;
    use Illuminate\Support\Facades\Auth;
    
    class RoleMiddleware
    {
        public function handle($request, Closure $next, $role)
        {
            if (Auth::check() && Auth::user()->role === $role) {
                return $next($request);
              }
            abort(403, 'Accés no autoritzat');
        }
    }
   ```
3. Aplicar-lo a una **ruta**:
 
   ```php
   Route::get('/admin', function () {
       return view('admin.dashboard');
   })->middleware('role:admin');
   ```
   o en **laravel 12**, directament al **controlador**:
   
```php
  use Illuminate\Routing\Attributes\Middleware;
  
   #[Middleware('role:admin')]
   class AdminController extends Controller
   {
     public function dashboard()
     {
       return view('admin.dashboard');
     }
   }
```

#### 🚪 Gates: autoritzacions simples

Els **Gates** són funcions d’autorització basades en clau/acció que viuen a `AuthServiceProvider`. Són útils per validar permisos puntuals sense crear una Policy completa. També es poden usar a vistes (`@can`), controladors (`authorize`) i serveis (`Gate::allows`).

**On es defineixen en Laravel 12:** al mètode `boot()` de `app/Providers/AuthServiceProvider.php` amb `Gate::define(...)`.

**Definir un gate**:

```php
use Illuminate\Support\Facades\Gate;
use App\Models\Post;

public function boot(): void
{
    Gate::define('update-post', function (User $user, Post $post) {
        return $user->id === $post->user_id;
    });
}
```

**Usar-lo**:

```php
// Controlador
$this->authorize('update-post', $post); // 403 si no pot

// Blade
@can('update-post', $post)
  <a href="{{ route('posts.edit', $post) }}">Editar</a>
@endcan
```

Tria **Gates** per casos senzills o accions aïllades; tria **Policies** per a lògica repetida associada a un model (view, create, update, delete).


#### 🛡️  Polítiques (`Policy`) per  autoritzacions
 
Les **policies** en Laravel permeten controlar l’accés a recursos de manera precisa i reutilitzable. Es poden aplicar automàticament als models o explícitament mitjançant mètodes com `authorize()`.


##### 🛠️ 1. Crear una Policy

Es genera una **política associada a un model** per controlar qui pot realitzar accions sobre aquest recurs.

```bash
./vendor/bin/sail artisan make:policy PostPolicy --model=Post
``` 

Es crea a **app/Policies/PostPolicy.php**.

##### 🧠 2. Definir les regles d’autorització

Dins la policy es **defineixen mètodes** com `view`, `create`, `update` o `delete` que retornen si un usuari pot realitzar o no eixa acció.

```php
public function update(User $user, Post $post)
{
  return $user->id === $post->user_id;
}
``` 

Cada mètode pot retornar:

- true (permés)
- false (denegat)
- O fins i tot llançar excepcions o missatges personalitzats

##### 🧾 3. Registrar la Policy

Cal **registrar la política** al proveïdor d’autenticació **(`AuthServiceProvider`)** per vincular el model amb la seua policy corresponent.

```php
use App\Models\Post;
use App\Policies\PostPolicy;

protected $policies = [
Post::class => PostPolicy::class,
];
``` 

En Laravel 12 també pots **deixar que s’auto-descobrisquen** si segueixes l’estructura per defecte (`app/Policies/FooPolicy.php` per al model `App\Models\Foo`). Si prefereixes registrar-les manualment, fes-ho al `AuthServiceProvider` com a l’exemple.

##### 🧪 4. Utilitzar-la en controladors

Dins dels controladors, s’utilitza la **funció `authorize()`** per verificar si l’usuari pot executar una acció determinada sobre un recurs.

```php
public function edit(Post $post)
{
  $this->authorize('update', $post);
  return view('posts.edit', compact('post'));
}
```

##### ✅ 5. Ús en vistes Blade

Amb la **directiva `@can`**, podem controlar l’accés a botons o seccions visuals segons la política definida.

```bladehtml
@can('update', $post)
<a href="{{ route('posts.edit', $post) }}">Editar</a>
@endcan
```

##### 🔁 6. Regles globals

És possible definir regles generals dins la policy (com per exemple donar accés total als usuaris administradors) mitjançant el **mètode `before()`**.

```php
public function before(User $user, $ability)
{
  if ($user->role === 'admin') {
    return true; // accés total
  }
}
``` 
 
## SA 5.2 Seguretat en Formularis  i feedback

### ⚠️ Personalitzar Missatges d'Error

Podem personalitzar els missatges d'error sobreescrivint el mètode **messages()** dins del Form Request:

```php
public function messages()
{
    return [
        'title.required' => 'El camp títol és obligatori.',
        'title.min' => 'El camp títol ha de tindre almenys 3 caràcters.',
        'year.required' => 'El camp any és obligatori.',
    ];
}
```
### 🛡️ Autorització dins de FormRequest amb Policies

Cada FormRequest inclou un **mètode  authorize()** per controlar si l’usuari té permís per a executar l’acció abans de validar les dades. És el lloc idoni per invocar una policy.

**Exemple amb policy:**
Si tenim una policy EquipPolicy@update, dins el FormRequest:
 
```php
public function authorize(): bool
{
    $equip = $this->route('equip'); // Obtenim el model Equip des de la ruta
                                    // Només funcionarà si en la definició de rutes has fet binding del model com Route::put('/equips/{equip}', ...).
    return $this->user()->can('update', $equip); // Crida a la policy
}
``` 

- Si no pot, Laravel llança automàticament un error **403 Forbidden**.
- Si pot, executa la validació.


**Alternativa amb Gate::allows()**

També pots usar:

```php
use Illuminate\Support\Facades\Gate;

public function authorize(): bool
{
    return Gate::allows('update', $this->route('equip'));
}
``` 

###  🔄  Elements bàsics en vistes amb formularis

- `@csrf`: Protecció contra atacs de tipus cross-site request forgery
- `@method(´PUT´)`: Permet enviar formularis amb verbs PUT o DELETE
- `old('camp')`: Manté les dades introduïdes en cas d’error de validació, després d'un error de validació.
- `@error('camp')`: Mostra els errors de validació associats a cada camp després d'un error de validació.

### Missatges flash amb `session()->flash()`

Permeten mostrar **missatges temporals** (èxit, error, etc.) després d’una acció, com una redirecció després de crear o modificar un recurs.

```php
return redirect()->route('equips.index')->with('ok', 'Equip creat correctament!');
```

A la vista **Blade**:

```bladehtml
@if (session('ok'))
    <div class="alert alert-success">
        {{ session('ok') }}
    </div>
@endif
```

#### ✨ UX i manteniment d'estat

L'ús combinat de `old()`, `@error`, missatges flash i bones pràctiques de disseny millora significativament l’experiència d’usuari (UX) en formularis.
 
#### ❗ Mostrar Errors de Validació a la Vista
   
Quan es produeixen errors de validació, Laravel redirigeix automàticament a la vista anterior amb la variable global `$errors`, que conté els errors de validació.

Exemple de com mostrar un **llistat d'errors** al començament del formulari:
```php
@if ($errors->any())
    <ul>
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif
```

A **sota de cada camp**:
  
```php
<div class="form-group">
    <label for="title">Títol:</label>
    <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}">
    @if ($errors->has('title'))
        <div class="text-danger">{{ $errors->first('title') }}</div>
    @endif
</div>
```

## 🌍 Ús d'idiomes en Laravel (Internalització)

Laravel proporciona eines senzilles i potents per a la **localització d'aplicacions**, permetent suportar múltiples idiomes. Aquesta funcionalitat és ideal per a desenvolupar aplicacions accessibles a usuaris de diferents regions i llengües.

#### 1️⃣ Configuració Inicial
 
L'idioma predeterminat s'estableix al fitxer `config/app.php` mitjançant el paràmetre `locale`:
```php
'locale' => 'ca', // o 'es', 'en', etc.
'fallback_locale' => 'en', //altenNATIU
```
 
#### 2️⃣  Fitxers de Traducció

 
Per defecte, Laravel no inclou el directori `lang`. Per personalitzar els fitxers de llenguatge o crear-ne de nous, executa:

```bash
php artisan lang:publish
composer require laravel-lang/lang --dev
php artisan lang:add es
php artisan lang:add ca
```
 
Els fitxers de traducció es guarden al directori `lang`. Cada idioma té la seua pròpia carpeta, amb fitxers `.php` que contenen arrays clau-valor per a les traduccions:

**Exemple d'estructura:**
```
/lang
    /en
        messages.php
    /es
        messages.php
```

**Exemples de ftixer**

```php
// lang/en/messages.php
return [
    'welcome' => 'Welcome to our application!',
];
```

Per a altres idiomes:

```php
// lang/es/messages.php
return [
    'welcome' => '¡Bienvenido a nuestra aplicación!',
];
```

#### 3️⃣ Traduccions amb Fitxers JSON

Laravel permet utilitzar fitxers JSON per a traduccions simples. Aquest enfocament és útil per a aplicacions amb cadenes de traducció úniques i desordenades.

**Exemple de Fitxer JSON:**

// lang/es.json

```json

{
    "I love programming.": "Me encanta programar."
}
```

Per accedir a aquestes cadenes:

```php
echo __('I love programming.');
```


#### 4️⃣ Ús de Traduccions en el Codi
 
Utilitza la funció auxiliar `__()` per obtenir les traduccions:

```php
echo __('messages.welcome'); // Welcome to our application!
```
Amb paràmetres:
 
```php
// lang/en/messages.php
return [
    'greeting' => 'Hello, :name!',
];
```
```php
echo __('messages.greeting', ['name' => 'John']); // Hello, John!
```
 
####  5️⃣ Canviar l'Idioma Dinàmicament

Per canviar l'idioma de l'aplicació en temps d'execució, utilitza el mètode `App::setLocale()`:

```php
use Illuminate\Support\Facades\App;

App::setLocale('es'); // Canvia a espanyol
```

Aquesta configuració només afecta la petició actual.



## 📬 Enviament de Correus en Laravel 12

Laravel proporciona una API elegant per a l’enviament de correus electrònics a través de múltiples serveis (SMTP, Mailgun, Postmark, Amazon SES...).

---

#### 1️⃣ Configuració del Servei de Correu

Edita el fitxer `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=el_teu_email@gmail.com
MAIL_PASSWORD=la_teua_contrasenya
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=el_teu_email@gmail.com
MAIL_FROM_NAME="Nom del Projecte"
```

El fitxer config/mail.php carregarà automàticament aquests valors.

#### 2️⃣ Crear una Classe de Correu

 
```bash
php artisan make:mail WelcomeMail
```

Aquest comandament crea una classe al directori `App\Mail`. Aquesta classe és on es defineix el contingut i el disseny del correu.

**Exemple**

```php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function build()
    {
        return $this->view('emails.welcome')
                    ->subject('Benvingut a la nostra aplicació');
    }
}
```

#### 3️⃣ Crear la Vista del Correu

resources/views/emails/benvinguda.blade.php

```bladehtml
<h1>Hola, {{ $usuari->name }}!</h1>
<p>Gràcies per unir-te a la nostra aplicació.</p>
```

### Enviar el Correu

Des d’un controlador:

```php
use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Mail;

Mail::to('usuari@example.com')->send(new WelcomeMail($user));
```

**Enviar correus a múltiples destinataris**

```php
Mail::to(['user1@example.com', 'user2@example.com'])->send(new WelcomeMail($user));
```

#### 5️⃣ Correus amb Markdown

Laravel permet crear correus amb components de Markdown. Generem un correu amb components:

```bash
php artisan make:mail WelcomeMail --markdown=emails.welcome
```

Aquest comandament crea una plantilla Markdown a `resources/views/emails`.

Exemple de plantilla Markdown:

```bladehtml
@component('mail::message')
    # Hola, {{ $usuari->name }}
    
    Benvingut/da a la nostra aplicació!
    
    @component('mail::button', ['url' => 'https://example.com'])
    Visita'ns
    @endcomponent
    
    Gràcies,<br>
    {{ config('app.name') }}
@endcomponent

```

#### 6️⃣ Correus en cua (asíncrons)

Per millorar el rendiment:

```php
Mail::to('usuari@example.com')->queue(new WelcomeMail($user));
```

Assegura't que el sistema de cues estiga configurat al fitxer `.env`:

```env
QUEUE_CONNECTION=database
```

## 🗂️ Gestió de Fitxers en Laravel 12

Laravel proporciona una API senzilla per treballar amb fitxers i directoris a través del component `Storage`.

 
#### 1️⃣ Configuració del Sistema de Fitxers

Els “discs” es configuren al fitxer `config/filesystems.php`.

##### Tipus comuns:

- `local`: emmagatzematge intern (no accessible públicament)
- `public`: fitxers accessibles via navegador
- `s3`: Amazon S3 o altres serveis compatibles

##### `.env`:

```env
FILESYSTEM_DISK=local
AWS_ACCESS_KEY_ID=el_teu_access_key
AWS_SECRET_ACCESS_KEY=el_teu_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=el_teu_bucket
```

#### 2️⃣ Operacions Bàsiques

**Guardar fitxers**
 
```php
use Illuminate\Support\Facades\Storage;

// Text
Storage::put('documents/info.txt', 'Contingut');

// Arxiu pujat
Storage::putFile('uploads', $request->file('document'));

// Amb nom personalitzat
Storage::putFileAs('uploads', $request->file('document'), 'factura.pdf');

```
 
***Llegir i verificar fitxers**

```php
// Obtenir el contingut d'un fitxer
$content = Storage::get('documents/file.txt');

// Verificar si un fitxer existeix
if (Storage::exists('documents/file.txt')) {
    // Fitxer existeix
}
```

**Eliminar Fitxers**
```php
// Eliminar un fitxer
Storage::delete('documents/file.txt');

// Eliminar múltiples fitxers
Storage::delete(['file1.txt', 'file2.txt']);
```

**Llistar Fitxers i carpetes**

```php
// Llistar tots els fitxers d'un directori
$files = Storage::files('documents');

// Llistar fitxers recursivament
$allFiles = Storage::allFiles('documents');

// Llistar carpetes
$directories = Storage::directories('documents');

// Llistar carpetes recursivament
$allDirectories = Storage::allDirectories('documents');
```

#### 3️⃣ Fitxers Públics

Per servir fitxers públicament, utilitza el disc public i crea un enllaç simbòlic:
```bash
php artisan storage:link
```

**Obtenir una URL Pública**

```php
$url = Storage::url('documents/file.txt'); // Genera una URL pública
```

#### 4️⃣ Amazon S3

Inclou les credencials d'Amazon S3 al fitxer `.env`:

```env
AWS_ACCESS_KEY_ID=el_teu_access_key
AWS_SECRET_ACCESS_KEY=el_teu_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=el_teu_bucket
```

**Exemple**

```php
use Illuminate\Support\Facades\Storage;

// Guardar un fitxer a S3
Storage::disk('s3')->put('documents/file.txt', 'Contingut');

// Obtenir una URL
$url = Storage::disk('s3')->url('documents/file.txt');
```
##   ✅ Testing en Laravel 12

Laravel inclou una infraestructura de proves robusta basada en PHPUnit, que permet realitzar proves unitàries, d'integració i de funcionalitat HTTP.
 

#### 🧪 Tipus de proves

- Proves Unitàries : Validen la lògica d’un component aïllat (model, servei, etc.).

- Proves de Funcionalitat (Feature Tests) : Simulen sol·licituds HTTP completes i proven controladors, middleware, i rutes.

- Proves de Base de Dades: Verifiquen la persistència i integritat de les dades (amb `RefreshDatabase` o `DatabaseTransactions`).

- Proves de Navegació amb Dusk: Permeten interactuar amb l’aplicació mitjançant un navegador real o virtual.

---

#### ⚙️ Configuració

##### `.env.testing`
Defineix la configuració per a l’entorn de proves:

```env
APP_ENV=testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```
S’utilitza automàticament en executar **php artisan test**.
 
#### ✏️ Crear proves

Per generar una prova:
```bash
php artisan make:test EquipServiceTest         # Prova unitària
php artisan make:test EquipCrudTest --unit      # Prova unitària (opció explícita)
php artisan make:test UserFeatureTest           # Prova de feature

```

**Exemple de Prova feature amb Validació i Autenticació** 

```php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EquipFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_creacio_equip_requereix_autenticacio_i_valida()
    {
        $user = User::factory()->create();

        // Prova redirecció sense login
        $this->get(route('equips.create'))
             ->assertRedirect(route('login'));

        // Prova validació invàlida
        $this->actingAs($user)
             ->post(route('equips.store'), [])
             ->assertSessionHasErrors(['nom', 'categoria']);

        // Prova validació vàlida
        $data = ['nom' => 'Nou Equip', 'categoria' => 'Base'];
        $this->actingAs($user)
             ->post(route('equips.store'), $data)
             ->assertRedirect(route('equips.index'))
             ->assertSessionHas('success'); // si s’usa missatge flash
    }
}

```

#### Proves de Base de Dades

#####   Migracions 

Utilitza el trait `RefreshDatabase` per executar les migracions abans de cada prova:

**Exemple de CRUD complet amb Base de Dades**

```php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Equip;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EquipCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_crud_complet_d_equip()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // CREATE
        $equip = Equip::factory()->create(['nom' => 'Equip A']);
        $this->assertDatabaseHas('equips', ['nom' => 'Equip A']);

        // UPDATE
        $equip->update(['nom' => 'Equip B']);
        $this->assertDatabaseHas('equips', ['nom' => 'Equip B']);

        // DELETE
        $equip->delete();
        $this->assertDatabaseMissing('equips', ['nom' => 'Equip B']);
    }
}

```

**Exemple de prova unitària amb Service o Repository**
```php
namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Estadi;
use App\Services\EquipService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EquipServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_crea_equip_correctament()
    {
        $estadi = Estadi::factory()->create();
        $service = new EquipService();
        $equip = $service->create([
            'nom' => 'València CF',
            'categoria' => 'Pro',
            'estadi_id' => $estadi->id,
        ]);

        $this->assertDatabaseHas('equips', ['nom' => 'València CF']);
        $this->assertEquals('València CF', $equip->nom);
    }
}

```

 
#### Bones pràctiques

-  Refactoritzar el codi amb Service, Repository, etc.
-  Usar factories per crear dades de prova.
-  Fer proves independents i narratives (test-first sempre que siga possible).
-  Provar estats de sessió, permisos i policies.
-  Executar proves en paral·lel si ho permet la màquina (php artisan test --parallel).

##  🌀 Laravel Livewire 3 – Desenvolupament d’interfícies reactives amb PHP

**Livewire 3** és una llibreria per Laravel que permet desenvolupar components interactius i reactius directament amb PHP, sense necessitat d'escriure JavaScript.

---

#### ✅ 1. Instal·lació i configuració bàsica

```bash
composer require livewire/livewire

```
 
Inclou els scripts de Livewire en les teues vistes Blade:
```bladehtml
@livewireStyles
@livewireScripts
```

Opcional per usar vite (Laravel 12):

```bash
php artisan livewire:configure-vite

```

#### 🧱 2. Crear components

**Generar un Component**
 
```bash
php artisan make:livewire HelloWorld
```

Això genera:
- Un fitxer de classe PHP: `app/Livewire/HelloWorld.php`
- Una plantilla Blade: `resources/views/livewire/hello-world.blade.php`

**Exemple de Component**

app/Livewire/HelloWorld.php

```php
namespace App\Livewire;

use Livewire\Component;

class HelloWorld extends Component
{
    public $message = "Hola, món!";

    public function render()
    {
        return view('livewire.hello-world');
    }
}
```

resources/views/livewire/hello-world.blade.php

```bladehtml
<div>
    <h1>{{ $message }}</h1>
</div>
```

**Inserir component en una vista**

```html
<livewire:hello-world />
```

#### 🔁 3. Propietats reactives i accions

##### Propietats i bindings
Les propietats de la classe PHP es poden vincular directament als camps d'un formulari HTML:

```php
class Counter extends Component
{
    //propietat
    public $count = 0;

    //mètode del component
    public function increment()
    {
        $this->count++;
    }

    public function render()
    {
        return view('livewire.counter');
    }
}
```

 
```bladehtml
<div>
    <button wire:click="increment">+</button>
    <h1>{{ $count }}</h1>
</div>
```


#### 💬 4. Validació

Livewire permet validar dades a mesura que l'usuari interactua amb el formulari:

**Tradicional**

```php
protected $rules = [
'nom' => 'required|string|min:3',
];

public function guardar() {
$this->validate();
}
```php

**En temps real**

```php
protected $rules = ['nom' => 'required|min:3'];

public function updated($propertyName)
{
    $this->validateOnly($propertyName);
}

```

#### 📦 5. Components amb formularis

 
```php
public $nom, $email;

public function enviar()
{
$this->validate([
'nom' => 'required',
'email' => 'required|email',
]);

// Guardar o enviar dades...
}
 
```
```bladehtml
<form wire:submit.prevent="enviar">
    <input type="text" wire:model="nom">
    @error('nom') <span>{{ $message }}</span> @enderror

    <input type="email" wire:model="email">
    @error('email') <span>{{ $message }}</span> @enderror

    <button>Enviar</button>
</form>
```

#### 🧠 6. Exemple complet

contador.php
```php
namespace App\Livewire;

use Livewire\Component;

class Contador extends Component
{
    public $valor = 0;

    public function incrementar() { $this->valor++; }

    public function render() {
        return view('livewire.contador');
    }
}
```
contador.blade.php
```bladehtml
<div>
    <button wire:click="incrementar">+</button>
    <span>{{ $valor }}</span>
</div>

```
#### 7. Altres funcionalitats útils

**Hooks**

- mount(): s’executa en inicialitzar el component.
- hydrate(): després de cada request.
- dehydrate(): abans d’enviar al client.

**Flash messages**

```php
session()->flash('missatge', 'Guardat!');
```

```bladehtml
@if (session()->has('missatge'))
<div>{{ session('missatge') }}</div>
@endif
```

#### 🔐 8. Amb autenticació i autorització

```php
public function mount()
{
    if (!auth()->check()) {
        abort(403);
    }
}

```

#### 🧪 9. Proves de components Livewire

```php
namespace Tests\Feature\Livewire;

use Tests\TestCase;
use Livewire\Livewire;
use App\Livewire\HistorialPartits;
use App\Models\Partit;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HistorialPartitsTest extends TestCase
{
    use RefreshDatabase;

    public function test_filtre_livewire_mostra_correctament()
    {
        Partit::factory()->create(['equip_local' => 'A']);
        Partit::factory()->create(['equip_local' => 'B']);

        Livewire::test(HistorialPartits::class)
            ->set('equip', 'A')
            ->call('filtrar')
            ->assertSee('A')
            ->assertDontSee('B');
    }
}

```

#### 🧩 10. Avantatges de Livewire

- Sense JavaScript
- Integració directa amb Laravel
- Ideal per a CRUD i components de UI dinàmics
- Compatible amb Alpine.js

##  Exercicis

###  🏟️ Exercici guiat: Reestructurar projecte Futbol Femení amb BD + Repository + Service

#### 🎯 Objectiu
Reestructurar l’aplicació de futbol femení (feta sense persistència) cap a una arquitectura escalable amb:
 
- Validació amb FormRequest
- Autenticació

---

#### 1.  🛡️ Afegir un escut a l'equip  (Branca escut)

**Crear una migració per afegir un camp `escut` a la taula `equips`**

```bash
./vendor/bin/sail artisan make:migration add_escut_to_equips_table
```

Modificar la migració [`add_escut_to_equips_table`](https://github.com/Curs-2025-26/futbol-femeni/blob/bdd/database/migrations/2025_08_31_044414_add_escut_to_equips_table.php)

**Aplicar la migració**

```bash
./vendor/bin/sail artisan migrate
```   

**Modificar el model `Equip` per incloure el camp `escut`**

```php 
protected $fillable = ['nom', 'estadi_id', 'titols', 'escut'];
``` 

**Modificar la vista `equips.create` per incloure un camp d'arxiu per pujar l'escut de l'equip i incloure enctype="multipart/form-data" al  formulari**

```bladehtml
<form action="{{ route('equips.store') }}" method="POST" enctype="multipart/form-data">

<div class="mb-4">
    <label for="escut" class="block text-sm font-medium text-gray-700 mb-1">Escut:</label>
    <input type="file" name="escut" id="escut"
        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
</div>
```


**Crear la  vista [`equips.edit`](https://github.com/Curs-2025-26/futbol-femeni/blob/escut/resources/views/equips/edit.blade.php) **

**Crear un enllaç simbòlic a la carpeta storage**

```bash
./vendor/bin/sail artisan storage:link
```
**Modificar el [controlador](https://github.com/Curs-2025-26/futbol-femeni/blob/escut/app/Http/Controllers/EquipController.php) per passar el fitxer al servei**

**Actualitzar les [validacions](https://github.com/Curs-2025-26/futbol-femeni/tree/escut/app/Http/Requests) per incorporar el camp escut**
**Actualitzar el mètode store,update i destroy del [servei](https://github.com/Curs-2025-26/futbol-femeni/blob/escut/app/Services/EquipService.php)**

**Modificat el [component](https://github.com/Curs-2025-26/futbol-femeni/blob/escut/resources/views/components/equip.blade.php) de la vista [`equips.show`](https://github.com/Curs-2025-26/futbol-femeni/blob/escut/resources/views/equips/show.blade.php) per mostrar l'escut de l'equip**


#### 🔑 2. Configurar l'autenticació amb Laravel Breeze

1. Canviar el nom del fitxer app.blade.php de la carpeta resources/views/layouts a equip.blade.php.
2. Guarda les rutes de la Guia d'Equips de Futbol Femení en algun fitxer per utilitzar després.
3. Instal·la Laravel Breeze:

```bash
composer require laravel/breeze --dev
php artisan breeze:install
npm install && npm run build
php artisan migrate
```
Triarem **blade amb alpine** i **PHPUNIT**

4. Canviar totes les vistes per a que extenguen de layouts.equip

5. Actualitza `resources/views/layouts/equip.blade.php` per a reutilitzar el layout de Breeze (`layouts/app.blade.php`), el mateix que fa servir el `dashboard`. Per exemple:

```blade
{{-- resources/views/layouts/equip.blade.php --}}
<x-app-layout>
   <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            @yield('title')
        </h2>
    </x-slot>
    @if (session('success'))
        <div class="bg-green-100 text-green-700 p-2 mb-4">{{ session('success') }}</div>
    @endif
  <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100"> 
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

#### 👥 3.  Afegir els rols al sistema
  
**Crea una nova migració per afegir el camp `role` a usuaris**
 
   ```bash
   php artisan make:migration add_role_to_users_table --table=users
   ```
   Afegeix el [camp](https://github.com/Curs-2025-26/futbol-femeni/blob/escut/database/migrations/2025_09_03_171410_add_role_to_users_table.php) i aplica-la:
   ```php
   Schema::table('users', function (Blueprint $table) {
       $table->string('role')->default('arbitre');
   });
   ```
   Aplica la migració:
   ```bash
   php artisan migrate
   ```

**Crea el seeders d'usuaris i crea un usuari administrador**

```bash
 php artisan make:seeder UserSeeder
``` 

**Executa el [seeder](https://github.com/Curs-2025-26/futbol-femeni/blob/escut/database/seeders/UserSeeder.php) havent canviat [DatabaseSeeder](https://github.com/Curs-2025-26/futbol-femeni/blob/escut/database/seeders/DatabaseSeeder.php )**

#### 🚪3. Middleware per a permisos de rol i manager
 
- **Genera el middleware**

```bash
php artisan make:middleware RoleMiddleware
```

- **Defineix el control dels rols en el metode [handle](https://github.com/Curs-2025-26/futbol-femeni/blob/escut/app/Http/Middleware/RoleMiddleware.php)**
 
- **Aplica  Middleware a [rutes](https://github.com/Curs-2025-26/futbol-femeni/blob/escut/routes/web.php)** fent que  les rutes per tal que els equips,estadis soles puguen modificar-los els administradors. 
 
 
#### 🧑‍💼👥 4. Associar managers a equips

 
**Crea una migració nova**

```bash
   php artisan make:migration add_team_id_to_users_table --table=users
```

**Afegeix el camp [`team_id`](https://github.com/Curs-2025-26/futbol-femeni/blob/escut/database/migrations/2025_09_03_174708_add_team_id_to_users_table.php)**  i **aplica la migració**

```bash
php artisan migrate
```

**Assigna equips als managers, creant u manager per equip i assigna-li** 

[EquipsSeeder](https://github.com/Curs-2025-26/futbol-femeni/blob/escut/database/seeders/EquipsSeeder.php)   

 
**Defineix la relació al model [`User`](https://github.com/Curs-2025-26/futbol-femeni/blob/escut/app/Models/User.php)**
 
**Defineix la relació inversa al model [`Equip`](https://github.com/Curs-2025-26/futbol-femeni/blob/escut/app/Models/Equip.php)**
  


#### 🧩🖼️ 5. Adaptar les vistes al component layout de Breeze

- **Modificar [`layouts/equip.blade.php`](https://github.com/Curs-2025-26/futbol-femeni/blob/escut/resources/views/layouts/equip.blade.php) per a utilitzar el layout de Breeze**.
 
- **Modificar el layout de Breeze per a incloure el menú de navegació en [`layouts\navigation.blade.php`](https://github.com/Curs-2025-26/futbol-femeni/blob/escut/resources/views/layouts/navigation.blade.php)** .
 
- **Modificar el menu de navegaciò [`partials/menu.blade.php`](https://github.com/Curs-2025-26/futbol-femeni/blob/escut/resources/views/partials/menu.blade.php) per acoplar-se**. 
 

####  🧑‍💼✏️🔒👥 6. El manager soles puga editar el seu equip

- **Crea la Política**

```bash
php artisan make:policy EquipPolicy --model=Equip
```

- **Defineix la Lògica a la Política** al fitxer generat [`app/Policies/EquipPolicy.php`](https://github.com/Curs-2025-26/futbol-femeni/blob/escut/app/Policies/EquipPolicy.php) per tal d'afegir les regles i permisos.
 
- **Defineix les regles d'autorització' en [`StoreEquipRequest.php`](https://github.com/Curs-2025-26/futbol-femeni/blob/escut/app/Http/Requests/StoreEquipRequest.php)**.

- **Fes el mateix amb  [`UpdateEquipRequest.php`](https://github.com/Curs-2025-26/futbol-femeni/blob/escut/app/Http/Requests/UpdateEquipRequest.php)**
  
- **Permet als manager accedir a les [rutes](https://github.com/Curs-2025-26/futbol-femeni/blob/escut/routes/web.php)** per poder modificar.

- **Utilitza  la Política al Controlador  [`EquipController`](https://github.com/Curs-2025-26/futbol-femeni/blob/escut/app/Http/Controllers/EquipController.php)**, utilitzant el mètode `authorize` per aplicar la política abans de permetre accions.
 
- **Utilitza  les directives `@can` per verificar els permisos a les vistes [`equis/index.blade.php`](https://github.com/Curs-2025-26/futbol-femeni/blob/escut/resources/views/equips/index.blade.php)**.
  
 
#### 🌍 7. Internacionalització

##### Publicar els Fitxers de Llenguatge 
  
```bash
php artisan lang:publish
composer require laravel-lang/lang --dev
php artisan lang:add es
php artisan lang:add ca
```
 
##### Configurar l'Idioma Predeterminat 

Al fitxer `.env`, ajusta l'opcions `locale` per establir l'idioma predeterminat:

```php
APP_LOCALE=ca
APP_FALLBACK_LOCALE=en
```

**Definir les Traduccions**
 
- [ca.json](https://github.com/Curs-2025-26/futbol-femeni/blob/escut/lang/ca.json)
- [es.json](https://github.com/Curs-2025-26/futbol-femeni/blob/escut/lang/es.json)
- [en.json](https://github.com/Curs-2025-26/futbol-femeni/blob/escut/lang/en.json)
 
 
##### Recuperar Cadenes de Traducció 

Utilitza la funció `__()` per obtenir les cadenes traduïdes en les vistes.

Per exemple:  [equips/index.blade.php](https://github.com/Curs-2025-26/futbol-femeni/blob/escut/resources/views/equips/index.blade.php)

##### Canviar l'Idioma Dinàmicament 

Crea  una [ruta](https://github.com/Curs-2025-26/futbol-femeni/blob/escut/routes/web.php ) per canviar idioma

**Crea  un Middleware per aplicar el idioma**

```bash
./vendor/bin/sail artisan make:middleware SetLocale
``` 
Defineix el [middleware](https://github.com/Curs-2025-26/futbol-femeni/blob/escut/app/Http/Middleware/SetLocale.php)

**Utilitza el layout [navigation.blade.php](https://github.com/Curs-2025-26/futbol-femeni/blob/escut/resources/views/layouts/navigation.blade.php) per possar els enllaços de canviar d'idioma**

**Modifica el fitxer [bootstrap/app.php](https://github.com/Curs-2025-26/futbol-femeni/blob/escut/bootstrap/app.php) per a fer efectiu el middleware**

#### 8. Proves

Les més senzilles i ràpides solen ser les unitàries sobre serveis i repositories (no cal muntar rutes ni vistes). Deixa els tests de controlador/feature només per comprovar que les rutes responen i apliquen middleware/policies bàsics.

1. Modifica o crea l'entorn de prova:

- [.env.testing](https://github.com/Curs-2025-26/futbol-femeni/blob/escut/.env.testing)
- [php.unit.xml](https://github.com/Curs-2025-26/futbol-femeni/blob/escut/phpunit.xml)

2. Crea els fitxers de proves per al CRUD d'equips (centrats en lògica):
```bash
php artisan make:test EquipServiceTest --unit
php artisan make:test EquipCrudFeatureTest
php artisan make:test EquipRepositoryTest --unit

```

3. Modifica  els fixer per tal d'incorporar les proves
   
- [`EquipServiceTest.php`](https://github.com/Curs-2025-26/futbol-femeni/blob/escut/tests/Unit/EquipServiceTest.php)
- [`EquipCrudFeatureTest.php`](https://github.com/Curs-2025-26/futbol-femeni/blob/escut/tests/Feature/EquipCrudFeatureTest.php)
- [`EquipRepositoryTest.php`](https://github.com/Curs-2025-26/futbol-femeni/blob/escut/tests/Unit/EquipRepositoryTest.php)


#### 9. Generar un correu electrònic amb la jornada actual (partits programats) i enviar-lo als managers dels equips.

##### 1. Crear una Comanda Artisan

```bash
php artisan make:command EnviarJornadaManagers
```

Al fitxer `app/Console/Commands/EnviarJornadaManagers.php`:

```php
namespace App\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Models\Partit;
use App\Mail\JornadaMail;

class EnviarJornadaManagers extends Command
{
    protected $signature = 'jornada:enviar';
    protected $description = 'Envia la jornada actual als managers';

    public function handle()
    {
        $partit = Partit::whereDate('data', '>', Carbon::now()) // Filtra partits posteriors a avui
            ->orderBy('data', 'asc') // Ordena per la data més propera
            ->first();
        $partits = Partit::with(['equipLocal', 'equipVisitant'])->where('jornada',$partit->jornada)->get();

        // Lògica per obtenir els correus dels managers
        $managers = User::where('role','manager')->get();


        foreach ($managers as $manager) {
            Mail::to($manager->email)->send(new JornadaMail($partits));
            $this->info($manager->name . ' ha rebut la jornada.');

        }

        $this->info('La jornada s\'ha enviat correctament als managers.');
    }
}
```

##### **2. Crear el Mail**

```bash
php artisan make:mail JornadaMail --markdown=emails.jornada
```

Al fitxer `app/Mail/JornadaMail.php`:

```php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;

class JornadaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $partits;

    public function __construct($partits)
    {
        $this->partits = $partits;
    }



    public function content(): Content
    {
        return new Content(
            markdown: 'emails.jornada',
            with: [
                'partits' => $this->partits,
            ],
        );
    }
}

```

#### **3. Crear la Vista del Correu**
Al fitxer `resources/views/emails/jornada.blade.php`:

```markdown
<x-mail::message>
    # Jornada {{ $partits->first()->jornada }}

    ## Partits Programats:
    @foreach($partits as $partit)
        - **{{ $partit->equipLocal->nom }}** vs **{{ $partit->equipVisitant->nom }}** ({{ $partit->data }})
    @endforeach

    Gràcies,
    **{{ config('app.name') }}**
</x-mail::message>
```

#### **4. Efectuar l'Enviament**
Pots enviar els correus manualment executant la comanda:

```bash
php artisan jornada:enviar
``` 

Pots programar la comanda al `Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('jornada:enviar')->weeklyOn(1, '8:00');
}
```

#### Pas 11. Crear un component livewire per a mostrar un històric de partits

1. Instal·la Livewire:
```bash
composer require livewire/livewire
php artisan livewire:publish
```

2. Afegeix els scripts de Livewire a la plantilla Blade:

```html
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased">
<div class="min-h-screen bg-gray-100 dark:bg-gray-900">
    @include('layouts.navigation')

    <!-- Page Heading -->
    @isset($header)
    <header class="bg-white dark:bg-gray-800 shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            {{ $header }}
        </div>
    </header>
    @endisset

    <!-- Page Content -->
    <main>
        {{ $slot }}
    </main>
</div>
@livewireScripts
</body>
</html>
```
3. Genera el component Livewire:
```bash
 php artisan make:livewire HistorialPartits
```

4. Modifica el component Livewire `app/Livewire/HistorialPartits.php`:
```php
namespace App\Livewire;

use App\Models\Partit;
use Livewire\Component;

class HistorialPartits extends Component
{
    public $partits;
    public $equip = '';
    public $data = '';

    public function mount()
    {
        $this->partits = Partit::with(['equipLocal', 'equipVisitant', 'estadi', 'arbitre'])->get();
    }


    public function filtrar()
    {
        $this->partits = Partit::with(['equipLocal', 'equipVisitant', 'estadi', 'arbitre'])
            ->when($this->equip, function ($query) {
                $query->whereHas('equipLocal', fn($q) => $q->where('nom', 'like', "%{$this->equip}%"))
                    ->orWhereHas('equipVisitant', fn($q) => $q->where('nom', 'like', "%{$this->equip}%"));
            })
            ->when($this->data, function ($query) {
                $query->whereDate('data', $this->data);
            })
            ->get();
    }

    public function render()
    {
        return view('livewire.historial-partits', ['partits' => $this->partits]);
    }
}
```

5. Modifica la vista del component Livewire `resources/views/livewire/historial-partits.blade.php`:
```php
<div>
    <div class="flex space-x-4">
        <input wire:model="equip" type="text" placeholder="Cerca equip" class="border px-4 py-2">
        <input wire:model="data" type="date" class="border px-4 py-2">
        <button wire:click="filtrar" class="bg-blue-500 text-white px-4 py-2">Filtrar</button>
    </div>

    <table class="table-auto w-full mt-4">
        <thead>
        <tr>
            <th>Data</th>
            <th>Equip Local</th>
            <th>Equip Visitant</th>
            <th>Resultat</th>
            <th>Estadi</th>
            <th>Àrbitre</th>
        </tr>
        </thead>
        <tbody>
        @foreach($partits as $partit)
            <tr>
                <td>{{ $partit->data }}</td>
                <td>{{ $partit->equipLocal->nom }}</td>
                <td>{{ $partit->equipVisitant->nom }}</td>
                <td>{{ $partit->resultat }}</td>
                <td>{{ $partit->estadi->nom }}</td>
                <td>{{ $partit->arbitre->name }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
```

6. Crea la vista `resources/views/partits/historic.blade.php`:
```php
@extends('layouts.futbolFemeni')

@section('title', __('Històric de partits' ))

@section('content')

    <!-- Afegim el component Livewire aquí -->
    <div class="mt-8">
        @livewire('historial-partits')
    </div>
@endsection
```

7. Modifica la ruta `routes/web.php`:
```php
Route::get('/historic', [PartitController::class, 'historic'])->name('partits.historic');

```

8. Modifica el controlador `app/Http/Controllers/PartitController.php`:
```php
public function historic()
{
    return view('partits.historic');
}
```

9. Afegix entrada en el menú `resources/views/layouts/navigation.blade.php`:
```php
<div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
    <x-nav-link :href="route('partits.historic')" :active="request()->routeIs('partits.historic')">
        {{ __('Històric Partits') }}
    </x-nav-link>
</div>
```


##  🏁 Exercici Final: Guia de Futbol Femení II

#### 🎯 Objectiu
Transformar i ampliar l’aplicació del projecte anterior per a incorporar:
 
- Validació robusta amb FormRequest
- Autenticació i autorització amb Laravel Breeze i Policies
- Components visuals, relacions entre models i funcionalitats avançades

---
#### 1. Autenticació i restriccions

- Laravel Breeze per login, logout
- Protegeix rutes amb `auth` i `@auth` en vistes
- Rols: `admin`, `manager`, `arbitre`
- Capacitats:
    - Managers: poden crear/editar/eliminar només el seu equip i les seues jugadores.
    - Àrbitres: poden modificar el resultat d’un partit només si són l’àrbitre assignat.
    - Administradors: poden crear i esborrar estadis i equips, i també tot el que poden fer els managers.
- Policies per controlar:
    - Jugadores: només el manager del seu equip (o admin).
    - Partits: només l’àrbitre assignat pot modificar resultats (o admin).
- No es permet crear partits manualment

---

#### 2. Formularis amb FormRequest

- Crea `EstadiRequest`, `JugadoraRequest`, `PartitRequest`
- Valida:
    - `data_naixement` mínima de 16 anys
    - `foto` (tipus .png i mida màxima)
    - `dorsal`, `capacitat`, `gols` (numèrics positius)
- Usa `authorize()` per controlar accés a modificació segons rol:
    - Managers només sobre el seu equip i les seues jugadores.
    - Àrbitres només per modificar resultats dels seus partits.
    - Administradors sense limitacions.

---

#### 3. Correu a àrbitres
- Enviar correu personalitzat a cada àrbitre amb:
    - Llistat de partits en què arbitrarà
    - Format HTML amigable

---

#### 4. Proves

- Prioritza proves unitàries sobre serveis/repositories (més ràpides i senzilles).
- Deixa els tests de controlador/feature només per verificar que les rutes responen i apliquen middleware/policies.
- Crea proves per:
   - Serveis/Repositories d’estadis, jugadores i partits.
   - FormRequest i Policies.
   - Un test de controlador/feature mínim per ruta crítica (p. ex. llistat partits, crear/edit equip).

---

#### 5. Classificació en temps real amb Livewire

- Taula amb:
    - Nom de l’equip, punts, gols a favor/en contra, diferència, etc.
- Component Livewire que es refresca automàticament
- Ordenació per punts i diferència de gols


