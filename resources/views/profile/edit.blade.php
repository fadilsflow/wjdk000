<x-app-layout>
    <x-slot name="subbar">
            <div class="max-w-2xl w-full mx-auto">
            <div class="text-2xl font-extrabold leading-tight">Profil</div>
            <div class="text-[color:var(--color-text-muted)] text-sm">Kelola informasi akun kamu</div>
        </div>
    </x-slot>

    <div class="p-4 sm:p-6 lg:p-8 max-w-3xl mx-auto space-y-6">
        <div class="card p-6 sm:p-8">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="card p-6 sm:p-8">
            @include('profile.partials.update-password-form')
        </div>

        <div class="card p-6 sm:p-8">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</x-app-layout>
