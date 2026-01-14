@extends('layouts.app')

@section('title', 'Projectes')

@section('content')
    <section class="mb-8">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-semibold text-slate-900">Projectes</h1>
                <p class="text-slate-600">Llistat general amb estat de visibilitat i paginacio.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <button class="rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:border-slate-400" type="button">Exporta</button>
                <a class="rounded-full bg-slate-900 px-5 py-2 text-sm font-semibold text-white hover:bg-slate-800" href="#">Nou projecte</a>
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white/80 p-6 shadow-sm">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-slate-900">Resultats</h2>
            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">12 projectes</span>
        </div>
        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                    <tr>
                        <th class="px-4 py-3">Projecte</th>
                        <th class="px-4 py-3">Equip</th>
                        <th class="px-4 py-3">Tecnologies</th>
                        <th class="px-4 py-3">Partner</th>
                        <th class="px-4 py-3">Stock</th>
                        <th class="px-4 py-3">Preu</th>
                        <th class="px-4 py-3">Visible</th>
                        <th class="px-4 py-3 text-right">Accions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <tr>
                        <td class="px-4 py-4">
                            <p class="font-semibold text-slate-900">Nova intranet corporativa</p>
                            <p class="text-xs text-slate-500">2024 · Llicencies anuals</p>
                        </td>
                        <td class="px-4 py-4 text-slate-700">Equip Delta</td>
                        <td class="px-4 py-4 text-slate-700">Laravel, Vue</td>
                        <td class="px-4 py-4 text-slate-700">TechNova</td>
                        <td class="px-4 py-4 text-slate-700">34</td>
                        <td class="px-4 py-4 text-slate-700">2.400 €</td>
                        <td class="px-4 py-4">
                            <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">Visible</span>
                        </td>
                        <td class="px-4 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <a class="rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-700 hover:border-slate-400" href="#">Veure</a>
                                <a class="rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-700 hover:border-slate-400" href="#">Editar</a>
                                <button class="rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700" type="button">Eliminar</button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-4 py-4">
                            <p class="font-semibold text-slate-900">Plataforma de reserves</p>
                            <p class="text-xs text-slate-500">2023 · Subscripcions</p>
                        </td>
                        <td class="px-4 py-4 text-slate-700">Equip Nord</td>
                        <td class="px-4 py-4 text-slate-700">React, API</td>
                        <td class="px-4 py-4 text-slate-700">CloudPath</td>
                        <td class="px-4 py-4 text-slate-700">12</td>
                        <td class="px-4 py-4 text-slate-700">1.100 €</td>
                        <td class="px-4 py-4">
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">No visible</span>
                        </td>
                        <td class="px-4 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <a class="rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-700 hover:border-slate-400" href="#">Veure</a>
                                <a class="rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-700 hover:border-slate-400" href="#">Editar</a>
                                <button class="rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700" type="button">Eliminar</button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-4 py-4">
                            <p class="font-semibold text-slate-900">Panel d'analitica</p>
                            <p class="text-xs text-slate-500">2022 · Llicencies</p>
                        </td>
                        <td class="px-4 py-4 text-slate-700">Equip Sol</td>
                        <td class="px-4 py-4 text-slate-700">Laravel, Tailwind</td>
                        <td class="px-4 py-4 text-slate-700">Innova</td>
                        <td class="px-4 py-4 text-slate-700">8</td>
                        <td class="px-4 py-4 text-slate-700">650 €</td>
                        <td class="px-4 py-4">
                            <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">Visible</span>
                        </td>
                        <td class="px-4 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <a class="rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-700 hover:border-slate-400" href="#">Veure</a>
                                <a class="rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-700 hover:border-slate-400" href="#">Editar</a>
                                <button class="rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700" type="button">Eliminar</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="mt-6 flex flex-wrap items-center justify-between gap-3 text-sm text-slate-600">
            <span>Mostrant 1-3 de 12</span>
            <div class="flex items-center gap-2">
                <button class="rounded-full border border-slate-200 px-3 py-1 text-sm font-semibold text-slate-700" type="button">Anterior</button>
                <button class="rounded-full bg-slate-900 px-3 py-1 text-sm font-semibold text-white" type="button">1</button>
                <button class="rounded-full border border-slate-200 px-3 py-1 text-sm font-semibold text-slate-700" type="button">2</button>
                <button class="rounded-full border border-slate-200 px-3 py-1 text-sm font-semibold text-slate-700" type="button">3</button>
                <button class="rounded-full border border-slate-200 px-3 py-1 text-sm font-semibold text-slate-700" type="button">Seguent</button>
            </div>
        </div>
    </section>
@endsection
