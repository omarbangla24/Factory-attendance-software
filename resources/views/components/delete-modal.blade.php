@props(['title' => 'Confirm Delete', 'message' => 'Are you sure you want to delete this item?', 'form', 'buttonText' => 'Delete'])

<div x-data="{ open: false }" @click.away="open = false" class="relative">
    <!-- Trigger Button -->
    <button @click="open = true" {{ $attributes->merge(['class' => 'text-red-600 hover:text-red-900 text-sm font-medium']) }}>
        {{ $slot }}
    </button>

    <!-- Modal -->
    <div x-show="open" x-transition class="fixed inset-0 bg-black bg-opacity-50 z-40 flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-lg shadow-xl max-w-sm w-full p-6 transform transition-all">
            <!-- Close Button -->
            <button @click="open = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Icon -->
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 0v2m0-6v0m0 0h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>

            <!-- Content -->
            <h3 class="text-lg font-medium text-gray-900 mb-2 text-center">{{ $title }}</h3>
            <p class="text-sm text-gray-500 text-center mb-6">{{ $message }}</p>

            <!-- Buttons -->
            <div class="flex gap-3">
                <button @click="open = false" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">
                    Cancel
                </button>
                <form method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">
                        {{ $buttonText }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
