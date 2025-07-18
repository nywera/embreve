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
                    // Atualiza os ícones da Lucide após carregar novo conteúdo
                    if (window.lucide) {
                        window.lucide.createIcons();
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
    if (document.getElementById('product-showcase-placeholder')) {
        loadComponent('_includes/product-showcase-section.html', 'product-showcase-placeholder');
    }
    if (document.getElementById('cta-placeholder')) {
        loadComponent('_includes/cta-section.html', 'cta-placeholder');
    }
    if (document.getElementById('info-placeholder')) {
        loadComponent('_includes/info-section.html', 'info-placeholder');
    }

    // Agrupa todas as funções que dependem do header
    function setupHeader() {
        const header = document.querySelector('header');
        const mobileNavToggle = document.querySelector('.mobile-nav-toggle');
        const primaryNav = document.querySelector('.primary-navigation');

        // Aplica o tema correto do header baseado na página
        const currentPage = window.location.pathname.split('/').pop() || 'index.html';
        const currentPath = window.location.pathname;
        const lightThemePages = ['index.html', 'contato.html', '404.html'];
        
        // Verifica se é uma página 404 (pode ser detectada de várias formas)
        const is404Page = currentPage === '404.html' || 
                         currentPath.includes('404') || 
                         document.title.includes('não encontrada') ||
                         document.querySelector('.error-section') !== null;
        
        if (lightThemePages.includes(currentPage) || is404Page) {
            // Páginas com tema light (menu light, texto preto)
            header.classList.add('header-light');
        } else {
            // Demais páginas com tema dark (menu dark, texto light)
            header.classList.add('header-dark');
        }

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