<!-- Modal Component -->
<div id="{{ $modalId }}" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center" style="display: none;">
    <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full mx-4 max-h-[90vh] flex flex-col">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200 flex-shrink-0">
            <h3 class="text-2xl font-bold text-gray-800">{{ $title }}</h3>
            <button type="button" onclick="closeModal('{{ $modalId }}')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <!-- Modal Body (Scrollable) -->
        <div class="p-6 overflow-y-auto flex-1">
            <div id="{{ $modalId }}_errors" class="hidden mb-4 bg-red-50 border border-red-200 rounded-lg p-4">
                <ul class="list-disc list-inside text-red-600 text-sm"></ul>
            </div>
            
            <form id="{{ $modalId }}_form" class="space-y-6" enctype="multipart/form-data">
                @csrf
                <div id="{{ $modalId }}_form_content">
                    <!-- Form content will be loaded via AJAX -->
                </div>
            </form>
        </div>
        
        <!-- Modal Footer (Sticky) -->
        <div class="flex gap-4 p-6 border-t border-gray-200 bg-white flex-shrink-0 sticky bottom-0">
            <button type="submit" form="{{ $modalId }}_form" class="px-6 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition font-semibold shadow-sm">
                <span id="{{ $modalId }}_submit_text">Simpan</span>
                <span id="{{ $modalId }}_loading" class="hidden">Menyimpan...</span>
            </button>
            <button type="button" onclick="closeModal('{{ $modalId }}')" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">
                Batal
            </button>
        </div>
    </div>
</div>

<script>
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        // Reset form
        const form = document.getElementById(modalId + '_form');
        if (form) {
            form.reset();
            // Clear errors
            const errorDiv = document.getElementById(modalId + '_errors');
            if (errorDiv) {
                errorDiv.classList.add('hidden');
                errorDiv.querySelector('ul').innerHTML = '';
            }
        }
    }
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('fixed') && e.target.id && e.target.id.includes('modal')) {
        closeModal(e.target.id);
    }
});
</script>
