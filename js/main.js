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
        if (mobileNavToggle) {
            mobileNavToggle.addEventListener('click', () => {
                const headerWrapper = document.querySelector('.header-wrapper');
                const isOpen = headerWrapper.classList.contains('nav-open');
                
                if (isOpen) {
                    headerWrapper.classList.remove('nav-open');
                    mobileNavToggle.setAttribute('aria-expanded', 'false');
                } else {
                    headerWrapper.classList.add('nav-open');
                    mobileNavToggle.setAttribute('aria-expanded', 'true');
                }
            });
        }

        // Dropdown mobile
        const dropdownItems = document.querySelectorAll('.dropdown');
        dropdownItems.forEach(dropdown => {
            const dropdownLink = dropdown.querySelector('a');
            dropdownLink.addEventListener('click', (e) => {
                if (window.innerWidth <= 768) {
                    e.preventDefault();
                    dropdown.classList.toggle('submenu-open');
                }
            });
        });

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

    // Newsletter form handler
    const newsletterForm = document.getElementById('newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const emailInput = this.querySelector('input[type="email"]');
            const email = emailInput.value.trim();
            const messageDiv = document.getElementById('newsletter-message');
            
            // Validar email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!email || !emailRegex.test(email)) {
                showNewsletterMessage('Por favor, insira um email válido.', 'error');
                return;
            }
            
            // Mostrar loading
            const submitBtn = this.querySelector('button');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            submitBtn.disabled = true;
            
            // Enviar dados
            fetch('process-newsletter.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email: email })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNewsletterMessage(data.message, 'success');
                    emailInput.value = '';
                } else {
                    showNewsletterMessage(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showNewsletterMessage('Erro ao processar inscrição. Tente novamente.', 'error');
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }
    
    function showNewsletterMessage(message, type) {
        const messageDiv = document.getElementById('newsletter-message');
        if (messageDiv) {
            messageDiv.textContent = message;
            messageDiv.className = `newsletter-message ${type}`;
            messageDiv.style.display = 'block';
            
            // Auto-hide após 5 segundos
            setTimeout(() => {
                messageDiv.style.display = 'none';
            }, 5000);
        }
    }
});

// Formulário de contato
document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contactForm');
    const messageTextarea = document.getElementById('message');
    const charCount = document.getElementById('current-chars');
    const submitBtn = document.getElementById('submitBtn');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoading = submitBtn.querySelector('.btn-loading');
    const formMessage = document.getElementById('formMessage');

    // Contador de caracteres
    if (messageTextarea && charCount) {
        messageTextarea.addEventListener('input', function() {
            const currentLength = this.value.length;
            charCount.textContent = currentLength;
            
            if (currentLength > 250) {
                charCount.style.color = '#ff6b35';
            } else {
                charCount.style.color = '';
            }
        });
    }

    // Envio do formulário
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validar campos obrigatórios
            const fullname = document.getElementById('fullname').value.trim();
            const email = document.getElementById('email').value.trim();
            const message = document.getElementById('message').value.trim();
            
            if (!fullname || !email || !message) {
                showFormMessage('Por favor, preencha todos os campos obrigatórios.', 'error');
                return;
            }
            
            // Validar email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showFormMessage('Por favor, insira um email válido.', 'error');
                return;
            }
            
            // Preparar dados
            const formData = {
                fullname: fullname,
                email: email,
                phone: document.getElementById('phone').value.trim(),
                position: document.getElementById('position').value,
                message: message
            };
            
            // Mostrar loading
            setFormLoading(true);
            hideFormMessage();
            
            // Enviar dados
            fetch('process-contact.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showFormMessage(data.message, 'success');
                    contactForm.reset();
                    if (charCount) charCount.textContent = '0';
                } else {
                    showFormMessage(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showFormMessage('Erro ao enviar mensagem. Tente novamente ou entre em contato pelo WhatsApp.', 'error');
            })
            .finally(() => {
                setFormLoading(false);
            });
        });
    }
    
    function setFormLoading(loading) {
        if (loading) {
            btnText.style.display = 'none';
            btnLoading.style.display = 'inline';
            submitBtn.disabled = true;
        } else {
            btnText.style.display = 'inline';
            btnLoading.style.display = 'none';
            submitBtn.disabled = false;
        }
    }
    
    function showFormMessage(message, type) {
        formMessage.textContent = message;
        formMessage.className = `form-message form-message-${type}`;
        formMessage.style.display = 'block';
        
        // Scroll para a mensagem
        formMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    
    function hideFormMessage() {
        formMessage.style.display = 'none';
    }
});