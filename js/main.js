document.addEventListener('DOMContentLoaded', function() {
    // Função para carregar componentes HTML (header e footer)
    const loadComponent = (filePath, targetElementId) => {
        const targetElement = document.getElementById(targetElementId);
        if (targetElement) {
            fetch(filePath)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Failed to load ${filePath}: ${response.statusText}`);
                    }
                    return response.text();
                })
                .then(data => {
                    targetElement.innerHTML = data;
                    // Se o header foi carregado, inicializa suas funções
                    if (targetElementId === 'header-placeholder') {
                        setupHeader();
                    }
                })
                .catch(error => console.error('Error loading component:', error));
        }
    };

    // Carrega o header e o footer
    loadComponent('_includes/header.html', 'header-placeholder');
    loadComponent('_includes/footer.html', 'footer-placeholder');

    // Carrega seções apenas se os placeholders existirem na página
    if (document.getElementById('services-placeholder')) {
        loadComponent('_includes/services-section.html', 'services-placeholder');
    }
    if (document.getElementById('portfolio-placeholder')) {
        loadComponent('_includes/portfolio-section.html', 'portfolio-placeholder');
    }

    // Agrupa todas as funções que dependem do header
    function setupHeader() {
        const header = document.querySelector('header');
        const mobileNavToggle = document.querySelector('.mobile-nav-toggle');
        const primaryNav = document.querySelector('.primary-navigation');

        // Menu mobile
        if (mobileNavToggle && primaryNav) {
            mobileNavToggle.addEventListener('click', () => {
                const isVisible = primaryNav.getAttribute('data-visible') === 'true';
                primaryNav.setAttribute('data-visible', !isVisible);
                mobileNavToggle.setAttribute('aria-expanded', !isVisible);
            });
        }

        // Efeito de scroll no header
        if (header) {
            window.addEventListener('scroll', () => {
                if (window.scrollY > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            });
        }

        // Ativa o link da página atual no menu
        const navLinks = document.querySelectorAll('header nav a');
        const currentPage = window.location.pathname.split('/').pop() || 'index.html'; // Padrão para index.html se vazio
        navLinks.forEach(link => {
            const linkPage = link.getAttribute('href').split('/').pop();
            if (linkPage === currentPage) {
                link.classList.add('active');
            }
        });
    }

    // Lógica para o contador de caracteres do formulário de contato
    const messageTextarea = document.getElementById('message');
    const currentCharCount = document.getElementById('current-chars');
    if (messageTextarea && currentCharCount) {
        const maxLength = messageTextarea.getAttribute('maxlength');

        messageTextarea.addEventListener('input', () => {
            const currentLength = messageTextarea.value.length;
            currentCharCount.textContent = currentLength;
        });
    }
});