(function () {
    const scriptUrl = document.currentScript?.src || new URL('assets/js/api.js', document.baseURI).href;
    const backendBase = new URL('../../../backend/', scriptUrl).href.replace(/\/$/, '');

    function resolveApiUrl(path) {
        const value = String(path || '');
        if (/^(?:[a-z][a-z\d+.-]*:)?\/\//i.test(value) || value.startsWith('data:')) {
            return value;
        }

        if (value.startsWith('./') || value.startsWith('../')) {
            return new URL(value, document.baseURI).href;
        }

        const marker = 'backend/';
        const markerIndex = value.indexOf(marker);
        if (markerIndex >= 0 && (value.startsWith('.') || value.startsWith('/'))) {
            return new URL(value.slice(markerIndex + marker.length), `${backendBase}/`).href;
        }

        return new URL(value.replace(/^\/+/, ''), `${backendBase}/`).href;
    }

    function loginUrl() {
        return new URL('../login/', document.baseURI).href;
    }

    window.apiUrl = resolveApiUrl;

    window.apiFetch = async function (path, options = {}) {
        const headers = new Headers(options.headers || {});
        if (typeof options.body === 'string' && !headers.has('Content-Type')) {
            headers.set('Content-Type', 'application/json');
        }

        const response = await fetch(resolveApiUrl(path), {
            ...options,
            headers,
            credentials: options.credentials || 'same-origin'
        });

        if (response.status === 401 && !String(path).includes('auth/login.php')) {
            if (window.location.href !== loginUrl()) {
                window.location.assign(loginUrl());
            }
        }

        return response;
    };

    window.apiJson = async function (response) {
        const text = await response.text();
        let data = {};

        if (text) {
            try {
                data = JSON.parse(text);
            } catch (error) {
                data = {
                    sucesso: false,
                    mensagem: response.status === 404
                        ? 'Recurso não encontrado (404). Verifique o endereço da tela ou do endpoint.'
                        : 'O servidor retornou uma resposta inválida.'
                };
            }
        }

        if (!data || typeof data !== 'object') {
            data = {};
        }

        if (typeof data.sucesso !== 'boolean') {
            data.sucesso = response.ok;
        }

        if (!response.ok) {
            data.sucesso = false;
            data.mensagem = data.mensagem || `Erro ${response.status} ao acessar o servidor.`;
        }

        return data;
    };
}());
