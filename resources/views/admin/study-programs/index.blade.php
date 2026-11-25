<x-app-layout>

    <x-slot:header>
        <x-page-header
            eyebrow="Manajemen"
            title="Program Studi"
            meta="Kelola program studi yang terdaftar">
            <x-slot:actions>
                <a href="{{ route('admin.study-programs.create') }}" class="btn-mark btn-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Program Studi
                </a>
            </x-slot:actions>
        </x-page-header>
    </x-slot:header>

    <div class="bg-white border border-rule rounded-xl shadow-card overflow-hidden">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nama Program Studi</th>
                    <th>Email</th>
                    <th>Dosen</th>
                    <th>Tim</th>
                    <th>Status</th>
                    <th class="text-right"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($programs as $program)
                    <tr>
                        <td>
                            <div class="font-medium text-ink-900">{{ $program->name }}</div>
                        </td>
                        <td class="mono-code text-ink-700/70">{{ $program->email ?? '—' }}</td>
                        <td class="mono-data text-center">{{ $program->users_count }}</td>
                        <td class="mono-data text-center">{{ $program->teams_count }}</td>
                        <td>
                            @if ($program->is_active)
                                <span class="badge-approved text-[10px]">Aktif</span>
                            @else
                                <span class="badge-cancelled text-[10px]">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.study-programs.edit', $program) }}" class="btn-ghost btn-sm">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="!py-10 text-center text-ink-700/50">
                            Belum ada program studi yang terdaftar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</x-app-layout>
