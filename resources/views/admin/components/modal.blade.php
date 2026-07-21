<!-- Modal Component -->
<div id="{{ $modalId }}" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-end sm:items-center justify-center p-0 sm:p-4" style="display: none;">
    <div class="bg-white rounded-t-2xl sm:rounded-lg shadow-xl w-full sm:max-w-3xl max-h-[92vh] sm:max-h-[90vh] flex flex-col">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-4 sm:p-6 border-b border-gray-200 flex-shrink-0 gap-3">
            <h3 class="text-lg sm:text-2xl font-bold text-gray-800 truncate">{{ $title }}</h3>
            <button type="button" onclick="closeModal('{{ $modalId }}')" class="text-gray-400 hover:text-gray-600 shrink-0 p-1">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <!-- Modal Body (Scrollable) -->
        <div class="p-4 sm:p-6 overflow-y-auto flex-1">
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
        <div class="flex flex-col-reverse sm:flex-row gap-2 sm:gap-4 p-4 sm:p-6 border-t border-gray-200 bg-white flex-shrink-0 sticky bottom-0">
            <button type="button" onclick="closeModal('{{ $modalId }}')" class="w-full sm:w-auto px-6 py-2.5 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">
                Batal
            </button>
            <button type="submit" form="{{ $modalId }}_form" class="w-full sm:w-auto px-6 py-2.5 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition font-semibold shadow-sm">
                <span id="{{ $modalId }}_submit_text">Simpan</span>
                <span id="{{ $modalId }}_loading" class="hidden">Menyimpan...</span>
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
    if (e.target.classList.contains('fixed') && e.target.id && e.target.id.toLowerCase().includes('modal')) {
        closeModal(e.target.id);
    }
});
</script>
