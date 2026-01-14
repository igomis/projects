@extends('layouts.app')

@section('title', 'Entrar')

@section('content')
    <div class="mx-auto max-w-xl">
        <section class="rounded-3xl border border-slate-200 bg-white/80 p-8 shadow-sm">
            <div class="text-center">
                <p class="text-sm font-semibold uppercase tracking-wide text-slate-500">Acces</p>
                <h1 class="mt-2 text-3xl font-semibold text-slate-900">Inicia sessio</h1>
                <p class="mt-2 text-slate-600">Introdueix les teues credencials per gestionar projectes.</p>
            </div>
            <form class="mt-8 space-y-5" action="#" method="post">
                <div>
                    <label class="text-sm font-medium text-slate-700" for="email">Correu</label>
                    <input class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-slate-400 focus:outline-none" id="email" name="email" placeholder="admin@example.test" type="email">
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700" for="password">Contrasenya</label>
                    <input class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-slate-400 focus:outline-none" id="password" name="password" placeholder="********" type="password">
                </div>
                <div class="flex items-center justify-between text-sm text-slate-600">
                    <label class="flex items-center gap-2">
                        <input class="h-4 w-4 rounded border-slate-300 text-slate-900" type="checkbox">
                        Recorda'm
                    </label>
                    <a class="font-semibold text-slate-700 hover:text-slate-900" href="#">He oblidat la contrasenya</a>
                </div>
                <button class="w-full rounded-2xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800" type="submit">Entrar</button>
            </form>
        </section>
    </div>
@endsection
