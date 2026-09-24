// Criar Usuário JavaScript

const form = document.getElementById('criarUsuarioForm');
const btnCriar = document.getElementById('btnCriar');
const textoBotao = document.getElementById('textoBotao');
const loading = document.getElementById('loading');
const mensagemErro = document.getElementById('mensagemErro');
const mensagemSucesso = document.getElementById('mensagemSucesso');

if (form) {
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const nome = document.getElementById('nome').value.trim();
        const usuario = document.getElementById('usuario').value.trim();
        const senha = document.getElementById('senha').value;
        const nivel_acesso = document.getElementById('nivel_acesso').value;
        
        if (!nome || !usuario || !senha) {
            mostrarErro('Preencha todos os campos obrigatórios.');
            return;
        }
        
        if (senha.length < 6) {
            mostrarErro('A senha deve ter pelo menos 6 caracteres.');
            return;
        }
        
        btnCriar.disabled = true;
        textoBotao.textContent = 'Criando...';
        loading.classList.remove('d-none');
        mensagemErro.classList.add('d-none');
        mensagemSucesso.classList.add('d-none');
        
        try {
            const response = await apiFetch('./criar_usuario.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ nome, usuario, senha, nivel_acesso })
            });
            
            const data = await apiJson(response);
            
            if (data.sucesso) {
                mostrarSucesso(data.mensagem);
                form.reset();
                setTimeout(() => {
                    window.location.href = '../login/';
                }, 2000);
            } else {
                mostrarErro(data.mensagem);
            }
        } catch (error) {
            mostrarErro('Erro de conexão. Tente novamente.');
        } finally {
            btnCriar.disabled = false;
            textoBotao.textContent = 'Criar usuário';
            loading.classList.add('d-none');
        }
    });
}

function mostrarErro(msg) {
    mensagemErro.textContent = msg;
    mensagemErro.classList.remove('d-none');
    mensagemSucesso.classList.add('d-none');
}

function mostrarSucesso(msg) {
    mensagemSucesso.textContent = msg;
    mensagemSucesso.classList.remove('d-none');
    mensagemErro.classList.add('d-none');
}