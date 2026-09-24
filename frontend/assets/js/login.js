const mostrarSenha = document.getElementById('mostrarSenha');
const senha = document.getElementById('senha');
const eyeIcon = document.getElementById('eyeIcon');

if (mostrarSenha && senha && eyeIcon) {
    mostrarSenha.addEventListener('click', () => {
        const senhaVisivel = senha.type === 'password';
        senha.type = senhaVisivel ? 'text' : 'password';
        eyeIcon.classList.toggle('bi-eye', !senhaVisivel);
        eyeIcon.classList.toggle('bi-eye-slash', senhaVisivel);
        mostrarSenha.setAttribute('aria-label', senhaVisivel ? 'Ocultar senha' : 'Mostrar senha');
    });
}

const mensagemErro = document.getElementById('mensagemErro');
const loginForm = document.getElementById('loginForm');
const btnLogin = document.getElementById('btnLogin');
const textoBotao = document.getElementById('textoBotao');
const loading = document.getElementById('loading');

if (mensagemErro && new URLSearchParams(window.location.search).get('erro')) {
    mensagemErro.textContent = 'Usuário ou senha inválidos.';
    mensagemErro.classList.remove('d-none');
}

if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const usuario = document.getElementById('usuario').value.trim();
        const senha = document.getElementById('senha').value;

        if (!usuario || !senha) {
            mensagemErro.textContent = 'Preencha todos os campos.';
            mensagemErro.classList.remove('d-none');
            return;
        }

        btnLogin.disabled = true;
        textoBotao.textContent = 'Entrando...';
        loading.classList.remove('d-none');
        mensagemErro.classList.add('d-none');

        try {
            const response = await apiFetch('auth/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ usuario, senha })
            });

            const data = await apiJson(response);

            if (data.sucesso) {
                sessionStorage.setItem('usuario_id', String(data.dados?.id || ''));
                sessionStorage.setItem('usuario_nome', data.dados?.nome || data.dados?.usuario || 'Usuário');
                sessionStorage.setItem('nivel_acesso', data.dados?.nivel_acesso || 'operador');
                window.location.href = '../dashboard/';
            } else {
                mensagemErro.textContent = data.mensagem || 'Erro ao fazer login.';
                mensagemErro.classList.remove('d-none');
            }
        } catch (error) {
            mensagemErro.textContent = 'Erro de conexão. Tente novamente.';
            mensagemErro.classList.remove('d-none');
        } finally {
            btnLogin.disabled = false;
            textoBotao.textContent = 'Entrar';
            loading.classList.add('d-none');
        }
    });
}