@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
<style>
    #news-editor .ql-toolbar.ql-snow {
        border: none;
        border-bottom: 1px solid #e5e7eb;
        background: linear-gradient(180deg, #faf5ff 0%, #ffffff 100%);
        padding: 10px 12px;
    }
    #news-editor .ql-container.ql-snow {
        border: none;
        min-height: 360px;
        font-size: 15px;
        font-family: Inter, system-ui, sans-serif;
    }
    #news-editor .ql-editor {
        min-height: 360px;
        line-height: 1.7;
    }
    #news-editor .ql-editor.ql-blank::before {
        color: #9ca3af;
        font-style: normal;
    }
    #news-editor .ql-snow .ql-stroke { stroke: #6b21a8; }
    #news-editor .ql-snow .ql-fill { fill: #6b21a8; }
    #news-editor .ql-snow.ql-toolbar button:hover,
    #news-editor .ql-snow .ql-toolbar button.ql-active {
        color: #7c3aed;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const hidden = document.getElementById('content');
    const form = document.getElementById('newsForm');
    if (!hidden || !form || typeof Quill === 'undefined') return;

    const quill = new Quill('#news-editor', {
        theme: 'snow',
        placeholder: 'Tulis isi berita di sini...',
        modules: {
            toolbar: [
                [{ header: [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ color: [] }, { background: [] }],
                [{ list: 'ordered' }, { list: 'bullet' }],
                [{ align: [] }],
                ['blockquote', 'code-block'],
                ['link', 'image'],
                ['clean']
            ]
        }
    });

    if (hidden.value) {
        quill.root.innerHTML = hidden.value;
    }

    form.addEventListener('submit', function () {
        hidden.value = quill.root.innerHTML;
        if (!hidden.value || hidden.value === '<p><br></p>') {
            hidden.value = '';
        }
    });
});
</script>
@endpush
