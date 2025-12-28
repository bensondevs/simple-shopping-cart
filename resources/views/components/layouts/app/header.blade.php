<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-white dark:bg-zinc-800">
<flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
    <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left"/>

    <a href="{{ route('products') }}" class="ms-2 me-5 flex items-center space-x-2 rtl:space-x-reverse lg:ms-0"
       wire:navigate>
        <x-app-logo/>
    </a>

    <flux:navbar class="-mb-px max-lg:hidden">
        <flux:navbar.item icon="shopping-bag" :href="route('products')" :current="request()->routeIs('products')"
                          wire:navigate>
            {{ __('Products') }}
        </flux:navbar.item>
        <flux:navbar.item icon="clipboard-document-list" :href="route('orders')" :current="request()->routeIs('orders')"
                          wire:navigate>
            {{ __('Orders') }}
        </flux:navbar.item>
    </flux:navbar>

    <flux:spacer/>

    <flux:navbar class="me-1.5 space-x-0.5 rtl:space-x-reverse py-0!">
        <livewire:cart-icon/>
    </flux:navbar>

    <!-- Desktop User Menu -->
    <flux:dropdown position="top" align="end">
        <flux:profile
            class="cursor-pointer"
            :initials="auth()->user()->initials()"
        />

        <flux:menu>
            <flux:menu.radio.group>
                <div class="p-0 text-sm font-normal">
                    <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                        <div class="grid flex-1 text-start text-sm leading-tight">
                            <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                            <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                        </div>
                    </div>
                </div>
            </flux:menu.radio.group>

            <flux:menu.separator/>

            <flux:menu.radio.group>
                <flux:menu.item :href="route('profile.edit')" icon="cog"
                                wire:navigate>{{ __('Settings') }}</flux:menu.item>
            </flux:menu.radio.group>

            <flux:menu.separator/>

            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                    {{ __('Log Out') }}
                </flux:menu.item>
            </form>
        </flux:menu>
    </flux:dropdown>
</flux:header>

<!-- Mobile Menu -->
<flux:sidebar stashable sticky
              class="lg:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
    <flux:sidebar.toggle class="lg:hidden" icon="x-mark"/>

    <a href="{{ route('products') }}" class="ms-1 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
        <x-app-logo/>
    </a>

    <flux:navlist variant="outline">
        <flux:navlist.group :heading="__('Shopping')">
            <flux:navlist.item
                icon="shopping-bag"
                :href="route('products')"
                :current="request()->routeIs('products')"
                wire:navigate
            >
                {{ __('Products') }}
            </flux:navlist.item>
            <flux:navlist.item
                icon="clipboard-document-list"
                :badge="\App\Models\Order::query()->whereBelongsTo(auth()->user())->where('status', \App\Enums\OrderStatus::Pending)->count()"
                :href="route('orders')"
                :current="request()->routeIs('orders')"
                wire:navigate
            >
                {{ __('Orders') }}
            </flux:navlist.item>
        </flux:navlist.group>
    </flux:navlist>

    <flux:spacer/>

    <flux:navlist variant="outline">
        <flux:navlist.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
            {{ __('Repository') }}
        </flux:navlist.item>

        <flux:navlist.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
            {{ __('Documentation') }}
        </flux:navlist.item>
    </flux:navlist>
</flux:sidebar>

<div class="relative z-10">
    {{ $slot }}
</div>

<livewire:cart-modal/>

<!-- Toast Notification -->
<div
    x-data="{
        show: false,
        message: '',
        type: 'success',
        timeout: null,
        showToast(event) {
            const detail = event.detail[0] || event.detail || {};
            this.message = detail.message || '';
            this.type = detail.type || 'success';
            // Delay showing toast to ensure modal closes first
            setTimeout(() => {
                this.show = true;
                clearTimeout(this.timeout);
                this.timeout = setTimeout(() => {
                    this.show = false;
                }, 5000);
            }, 300);
        }
    }"
    @toast.window="showToast($event)"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-2"
    class="fixed top-4 right-4 z-50 max-w-md"
    style="display: none;"
>
    <div
        class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900 p-4 shadow-lg"
    >
        <div class="flex items-center gap-3">
            <div
                :class="{
                    'text-green-600 dark:text-green-400': type === 'success',
                    'text-red-600 dark:text-red-400': type === 'error'
                }"
            >
                <svg
                    x-show="type === 'success'"
                    class="size-5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                >
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <svg
                    x-show="type === 'error'"
                    class="size-5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                >
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="flex-1">
                <flux:text class="font-medium text-zinc-900 dark:text-zinc-100" x-text="message"></flux:text>
            </div>
            <button
                @click="show = false"
                class="text-zinc-600 dark:text-zinc-400 opacity-70 hover:opacity-100"
            >
                <flux:icon.x-mark class="size-4"/>
            </button>
        </div>
    </div>
</div>

@fluxScripts
</body>
</html>
