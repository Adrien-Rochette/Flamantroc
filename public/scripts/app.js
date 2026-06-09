document.addEventListener('DOMContentLoaded', () => {
    const forms = document.querySelectorAll('form');
    forms.forEach((form) => {
        form.setAttribute('autocomplete', 'on');
    });

    const navbar = document.querySelector('[data-navbar]');
    const burgerButton = document.querySelector('[data-navbar-toggle]');
    const menu = document.querySelector('[data-navbar-menu]');
    const fishAlertToggle = document.querySelector('[data-fish-alert-toggle]');
    const fishAlertLabel = document.querySelector('[data-fish-alert-label]');
    const priceOfferToggle = document.querySelector('[data-price-offer-toggle]');
    const priceOfferForm = document.querySelector('[data-price-offer-form]');
    const helpWidget = document.querySelector('[data-help-widget]');

    if (helpWidget) {
        const endpoint = helpWidget.getAttribute('data-help-endpoint') ?? '';
        const toggleButton = helpWidget.querySelector('[data-help-toggle]');
        const panel = helpWidget.querySelector('[data-help-panel]');
        const closeButton = helpWidget.querySelector('[data-help-close]');
        const messagesContainer = helpWidget.querySelector('[data-help-messages]');
        const form = helpWidget.querySelector('[data-help-form]');
        const input = helpWidget.querySelector('#help-widget-input');
        const submitButton = helpWidget.querySelector('[data-help-submit]');

        if (
            endpoint !== ''
            && toggleButton instanceof HTMLButtonElement
            && panel instanceof HTMLElement
            && closeButton instanceof HTMLButtonElement
            && messagesContainer instanceof HTMLElement
            && form instanceof HTMLFormElement
            && input instanceof HTMLInputElement
            && submitButton instanceof HTMLButtonElement
        ) {
            let isBusy = false;
            const history = [];

            const getPageContext = () => {
                const headingTexts = Array.from(document.querySelectorAll('h1, h2, h3'))
                    .map((element) => element.textContent?.trim() ?? '')
                    .filter((text) => text.length > 0)
                    .slice(0, 10)
                    .join(' | ');

                const links = Array.from(document.querySelectorAll('a[href]'))
                    .map((link) => {
                        const text = link.textContent?.trim() ?? '';
                        const href = (link.getAttribute('href') ?? '').trim();
                        if (text === '' || href === '') {
                            return '';
                        }
                        return `${text} -> ${href}`;
                    })
                    .filter((item) => item !== '')
                    .slice(0, 20)
                    .join(' | ');

                const contentRoot = document.querySelector('.page-content');
                const contentText = ((contentRoot?.textContent ?? '').replace(/\s+/g, ' ').trim()).slice(0, 2000);

                return [
                    `Titre page: ${document.title}`,
                    `URL: ${window.location.pathname}`,
                    `Titres: ${headingTexts || 'Aucun'}`,
                    `Liens visibles: ${links || 'Aucun'}`,
                    `Contenu principal: ${contentText || 'Aucun'}`,
                ].join('\n');
            };

            const setPanelState = (isOpen) => {
                panel.toggleAttribute('hidden', !isOpen);
                toggleButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                if (isOpen) {
                    input.focus();
                }
            };

            const setBusyState = (busy) => {
                isBusy = busy;
                input.disabled = busy;
                submitButton.disabled = busy;
                submitButton.textContent = busy ? 'Envoi...' : 'Envoyer';
            };

            const appendMessage = (role, content) => {
                const message = document.createElement('p');
                message.className = role === 'assistant'
                    ? 'help-message help-message-assistant'
                    : 'help-message help-message-user';
                const escapedContent = content
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');

                const withBold = escapedContent.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
                message.innerHTML = withBold.replace(/\n/g, '<br>');
                messagesContainer.appendChild(message);
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            };

            toggleButton.addEventListener('click', () => {
                const isOpen = !panel.hasAttribute('hidden');
                setPanelState(!isOpen);
            });

            closeButton.addEventListener('click', () => {
                setPanelState(false);
            });

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                if (isBusy) {
                    return;
                }

                const question = input.value.trim();
                if (question === '') {
                    return;
                }

                const requestHistory = history.slice(-8);
                appendMessage('user', question);
                history.push({ role: 'user', content: question });
                input.value = '';
                setBusyState(true);

                try {
                    const response = await fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            question,
                            pageContext: getPageContext(),
                            history: requestHistory,
                        }),
                    });

                    const data = await response.json();
                    if (!response.ok || typeof data.answer !== 'string') {
                        const errorMessage = typeof data.error === 'string'
                            ? data.error
                            : 'Service indisponible.';
                        throw new Error(errorMessage);
                    }

                    appendMessage('assistant', data.answer);
                    history.push({ role: 'assistant', content: data.answer });
                } catch (error) {
                    const errorMessage = error instanceof Error ? error.message : 'Erreur reseau.';
                    appendMessage('assistant', `Desole, ${errorMessage}`);
                } finally {
                    setBusyState(false);
                }
            });
        }
    }

    if (priceOfferToggle && priceOfferForm) {
        priceOfferToggle.addEventListener('click', () => {
            const isHidden = priceOfferForm.hasAttribute('hidden');
            priceOfferForm.toggleAttribute('hidden', !isHidden);

            if (isHidden) {
                const priceInput = priceOfferForm.querySelector('input[name="prix_propose"]');
                if (priceInput instanceof HTMLInputElement) {
                    priceInput.focus();
                }
            }
        });
    }

    if (fishAlertToggle && fishAlertLabel) {
        let fishAlertTimeout = null;

        const setFishAlertState = (isVisible) => {
            fishAlertLabel.classList.toggle('visible', isVisible);
            fishAlertLabel.setAttribute('aria-hidden', isVisible ? 'false' : 'true');
            fishAlertToggle.setAttribute('aria-expanded', isVisible ? 'true' : 'false');
        };

        fishAlertToggle.addEventListener('click', (event) => {
            event.stopPropagation();
            setFishAlertState(true);

            window.clearTimeout(fishAlertTimeout);
            fishAlertTimeout = window.setTimeout(() => {
                setFishAlertState(false);
            }, 6000);
        });

        fishAlertLabel.addEventListener('click', (event) => {
            event.stopPropagation();
        });

        document.addEventListener('click', () => {
            setFishAlertState(false);
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                setFishAlertState(false);
            }
        });
    }

    if (!navbar || !burgerButton || !menu) {
        return;
    }

    const setMenuState = (isOpen) => {
        navbar.classList.toggle('menu-open', isOpen);
        burgerButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        burgerButton.setAttribute('aria-label', isOpen ? 'Fermer le menu' : 'Ouvrir le menu');
    };

    setMenuState(false);

    burgerButton.addEventListener('click', () => {
        const isOpen = navbar.classList.contains('menu-open');
        setMenuState(!isOpen);
    });

    const menuLinks = menu.querySelectorAll('a');
    menuLinks.forEach((link) => {
        link.addEventListener('click', () => {
            if (window.matchMedia('(max-width: 1024px)').matches) {
                setMenuState(false);
            }
        });
    });

    window.addEventListener('resize', () => {
        if (!window.matchMedia('(max-width: 1024px)').matches) {
            setMenuState(false);
        }
    });
});
