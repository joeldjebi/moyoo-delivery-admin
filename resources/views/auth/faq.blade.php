@include('layouts.header')
@include('layouts.menu')

<div class="row">
    <div class="col-lg-12 mb-4 order-0">
        <div class="card">
            <div class="row">
                <div class="col-sm-7">
                    <div class="card-body">
                        <h5 class="card-title text-primary">FAQ</h5>
                        <p class="mb-4">Trouvez les réponses à vos questions les plus fréquentes</p>
                    </div>
                </div>
                <div class="col-sm-5 text-center text-sm-left">
                    <div class="card-body pb-0 px-0 px-md-4">
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('auth.pricing') }}" class="btn btn-outline-primary">
                                <i class="ti ti-crown me-1"></i>
                                Voir les Forfaits
                            </a>
                            <a href="{{ route('entreprise.index') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-arrow-left me-1"></i>
                                Retour
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Barre de recherche -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8 mx-auto">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="ti ti-search"></i>
                            </span>
                            <input type="text" class="form-control" id="faq-search" placeholder="Rechercher dans la FAQ...">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FAQ par catégories -->
<div class="row">
    <div class="col-12">
        @foreach($faqs as $category)
            <div class="card mb-4 faq-category" data-category="{{ strtolower($category['category']) }}">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-help-circle me-2"></i>
                        {{ $category['category'] }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="accordion" id="accordion-{{ strtolower(str_replace(' ', '-', $category['category'])) }}">
                        @foreach($category['questions'] as $index => $faq)
                            <div class="accordion-item faq-item" data-question="{{ strtolower($faq['question']) }}" data-answer="{{ strtolower($faq['answer']) }}">
                                <h2 class="accordion-header" id="heading-{{ $index }}">
                                    <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#collapse-{{ $index }}"
                                            aria-expanded="{{ $index == 0 ? 'true' : 'false' }}"
                                            aria-controls="collapse-{{ $index }}">
                                        {{ $faq['question'] }}
                                    </button>
                                </h2>
                                <div id="collapse-{{ $index }}"
                                     class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}"
                                     aria-labelledby="heading-{{ $index }}"
                                     data-bs-parent="#accordion-{{ strtolower(str_replace(' ', '-', $category['category'])) }}">
                                    <div class="accordion-body">
                                        {{ $faq['answer'] }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Contact support -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center">
                <div class="avatar avatar-xl mx-auto mb-3">
                    <span class="avatar-initial rounded bg-label-primary">
                        <i class="ti ti-headset ti-lg"></i>
                    </span>
                </div>
                <h5 class="mb-2">Vous ne trouvez pas votre réponse ?</h5>
                <p class="text-muted mb-4">Notre équipe de support est là pour vous aider</p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="mailto:support@moyoo.com" class="btn btn-outline-primary">
                        <i class="ti ti-mail me-1"></i>
                        support@moyoo.com
                    </a>
                    <a href="tel:+33123456789" class="btn btn-outline-success">
                        <i class="ti ti-phone me-1"></i>
                        +33 1 23 45 67 89
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('faq-search');
    const faqItems = document.querySelectorAll('.faq-item');
    const faqCategories = document.querySelectorAll('.faq-category');

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();

        faqItems.forEach(item => {
            const question = item.dataset.question;
            const answer = item.dataset.answer;
            const matches = question.includes(searchTerm) || answer.includes(searchTerm);

            if (matches) {
                item.style.display = 'block';
                item.closest('.faq-category').style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });

        // Masquer les catégories vides
        faqCategories.forEach(category => {
            const visibleItems = category.querySelectorAll('.faq-item[style*="block"], .faq-item:not([style*="none"])');
            if (visibleItems.length === 0) {
                category.style.display = 'none';
            }
        });

        // Si la recherche est vide, afficher tout
        if (searchTerm === '') {
            faqItems.forEach(item => {
                item.style.display = 'block';
            });
            faqCategories.forEach(category => {
                category.style.display = 'block';
            });
        }
    });
});
</script>

@include('layouts.footer')
